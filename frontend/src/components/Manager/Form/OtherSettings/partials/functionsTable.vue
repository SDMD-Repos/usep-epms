<template>
  <div>
    <a-divider>Functions</a-divider>

    <a-table class="mt-4" :columns="columns" :data-source="functions" row-key="id" :pagination="false"
             :show-header="false" bordered>
      <template #settings="{ record }">
        <span v-if="record.id in editableData">
          <a-select v-model:value="editableData[record.id]['defaultProgram']" placeholder="Select"
                    label-in-value style="width: 400px">
            <a-select-option v-for="data in filteredPrograms(record.id)" :value="data.id.toString()" :key="data.id" :label="data.name">
              {{ data.name }}
            </a-select-option>
          </a-select>
        </span>
        <template v-else>Default Program: <b>{{ record.settingLabel }}</b></template>
      </template>

      <template #operation="{ record }" >
        <span v-if="record.id in editableData">
          <a-popconfirm title="Are you sure you want to proceed?" @confirm="save(record)">
            <a-button type="primary" shape="round" size="small" >
              {{ record.settings ? "Update" : "Save" }}
            </a-button>
          </a-popconfirm>
          <a-button class="ml-2" shape="round" size="small" @click="cancel(record.id)">Cancel</a-button>
        </span>
        <span v-else>
          <a type="primary" @click="edit(record.id)" v-if="isCreate">Edit</a>
        </span>
      </template>
    </a-table>
  </div>
</template>
<script>
import { defineComponent, reactive, onMounted, computed } from "vue";
import { useStore } from "vuex"
import { cloneDeep } from "lodash-es";

const columns = [
  { title: 'Field', dataIndex: 'name', key: 'name', width: 500 },
  { title: 'Settings', dataIndex: 'settings', width: 650, slots: { customRender: 'settings' } },
  { title: 'Action', dataIndex: 'operation', slots: { customRender: 'operation' } },
]

export default defineComponent({
  name: "FunctionsTable",
  props: {
    year: { type: Number, default: new Date().getFullYear() },
    isCreate: Boolean,
  },
  emits: ['handle-save'],
  setup(props, { emit }) {
    const store = useStore()

    // DATA
    const editableData = reactive({})

    // COMPUTED
    const programs = computed(() => store.getters['formManager/manager'].programs)

    const functions = computed(() => {
      const list = store.state.formManager.functions

      return list.map(data => {
        let name = "Not Set"
        if(data.default_program_id != null) {
          const filtered = programs.value.filter(i => parseInt(data.default_program_id) === i.id)
          name = filtered.length > 0 ? filtered[0]['name'] : ''
        }
        return {...data, settingLabel: name}
      })
    })

    // EVENTS
    onMounted(() => {
      store.dispatch('formManager/FETCH_PROGRAMS', { payload : { year: props.year }})
    })

    // METHODS
    const filteredPrograms = functionId => {
      return programs.value.filter(i => i.category_id === functionId)
    }

    const save = async data => {
      data.isUpdate = !!data.defaultProgram
      data.defaultProgram = editableData[data.id]
      await emit('handle-save', data)
      await cancel(data.id)
    }

    const edit = key => {
      const defaultProgram = cloneDeep(functions.value.filter(item => key === item.id)[0]['default_program_id'])
      if(defaultProgram) {
        const details = programs.value.filter(item => item.id === parseInt(defaultProgram))[0]
        editableData[key] = {
          defaultProgram: {
            key: details.key,
            id: details.id,
            label: details.name,
          },
        }
      }else {
        editableData[key] = { defaultProgram: null }
      }
    }

    const cancel = key => {
      delete editableData[key]
    }

    return {
      columns,

      editableData,

      functions,
      programs,

      filteredPrograms,
      save,
      edit,
      cancel,
    }
  },
})

</script>
