<template>
  <a-descriptions title="Display Name" bordered layout="vertical" size="small">
    <template v-for="details in functions" :key="details.id">
      <a-descriptions-item :span="3">
        <template #label>
          {{ details.name }} ( {{ details.percentage }}% )
        </template>
        <div class="mt-2 mb-2">
        <span v-if="details.id in editableData">
          <a-row>
            <a-col :xs="6" :sm="6" :md="10" :lg="10" :xl="12">
              <a-input v-model:value="editableData[details.id]['name']" placeholder="Display Name"
                       :maxlength="50" show-count allow-clear />
            </a-col>
            <a-col :xs="{ span: 10, offset: 2 }" :sm="{ span: 10, offset: 2 }" :md="{ span: 8, offset: 1 }"
                   :lg="{ span: 6, offset: 1 }" :xl="{ span: 4, offset: 1 }">
              <a-popconfirm title="Are you sure you want to proceed?" @confirm="save(details)">
                <a-button type="primary" shape="round" size="small" >
                  Save
                </a-button>
              </a-popconfirm>
              <a-button class="ml-2" shape="round" size="small" @click="cancel(details.id)">Cancel</a-button>
            </a-col>
          </a-row>
        </span>
        <span v-else>
          <b>{{ details.displayName }}</b>
          &nbsp;&nbsp;
          <a-popconfirm v-if="details.form_category"
                        title="Are you sure you want to clear the display name?"
                        @confirm="clear(details)">
            <clear-outlined style="cursor: pointer "/>
          </a-popconfirm>
          &nbsp;&nbsp;<edit-outlined @click="edit(details)"/>
        </span>
        </div>
      </a-descriptions-item>
    </template>
  </a-descriptions>
</template>
<script>
import { defineComponent, watch, onMounted, ref, reactive, computed } from 'vue';
import { useStore } from 'vuex'
import { EditOutlined, ClearOutlined } from "@ant-design/icons-vue"
import { Modal } from "ant-design-vue";

export default defineComponent({
  name: 'OpcrFunctionsManagerForm',
  components: { EditOutlined, ClearOutlined },
  props: {
    year: { type: Number, default: new Date().getFullYear() },
    formId: { type: String, default: '' },
  },
  emits: ['handle-save', 'handle-clear'],
  setup(props, { emit }) {
    const store = useStore()

    // DATA
    const formRef = ref()
    const formState = reactive({
      title: '',
      description: '',
      modifier: 'public',
    })

    const editableData = reactive({})

    // COMPUTED
    const functions = computed(() => {
      const list = store.state.formManager.functions

      return list.map(data => {
        let name = "Not Set"
        if(data.form_category != null) {
          name = data.form_category.display_name
        }
        return {...data, displayName: name}
      })
    })

    // EVENTS
    watch(() => [props.year], ([year]) => {
      fetchFunctions(year)
    })

    onMounted(() => {
      fetchFunctions(props.year)
    })

    // METHODS
    const fetchFunctions = year => {
      store.dispatch('formManager/FETCH_FUNCTIONS', { payload: { year: year, formId: props.formId, isPrevious: false }})
    }

    const edit = details => {
      const { id, displayName, form_category } = details

      const name = (!form_category || (form_category && !form_category.display_name)) ? null : displayName

      editableData[id] = {
        name: name ,
      }
    }

    const save = async data => {
      const name = editableData[data.id].name
      if(name === '' || name.trim().length === 0) {
        Modal.error({
          title: () => 'Unable to save data',
          content: () => 'Please input a valid Display Name!',
        })
      } else {
        data.isUpdate = !!data.form_category
        data.modifiedName = name
        await emit('handle-save', data)
        await cancel(data.id)
      }
    }

    const cancel = key => {
      delete editableData[key]
    }

    const clear = data => {
      emit('handle-clear', data)
    }

    return {
      formRef, formState, editableData,

      functions,

      edit, save, cancel, clear,
    }
  },
})
</script>
