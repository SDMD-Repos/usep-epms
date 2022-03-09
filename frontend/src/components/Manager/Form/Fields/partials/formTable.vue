<template>
  <a-table :columns="columns" :data-source="formFields" row-key="id" :pagination="false" :loading="loading" bordered>
    <template #settings="{ record }">
      <div v-if="record.code === 'implementing' || record.code === 'supporting'">
        <span v-if="record.id in editableData">
          <a-select v-model:value="editableData[record.id]['settings']" placeholder="Select"
                    label-in-value style="width: 400px">
            <a-select-option v-for="data in functions" :value="data.id" :key="data.id" :label="data.name">
              {{ data.name }}
            </a-select-option>
          </a-select>
        </span>
        <template v-else>Cascade to: <b>{{ record.settingLabel }}</b></template>
      </div>
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
        <a type="primary" @click="edit(record.id)" v-if="isCheck || allAccess">Edit</a>
      </span>
    </template>
  </a-table>
</template>
<script>
import { defineComponent, reactive, computed } from "vue"
import { useStore } from "vuex"
import { cloneDeep } from 'lodash-es'

const columns = [
  { title: 'Field', dataIndex: 'field_name', key: 'field_name', width: 500 },
  { title: 'Settings', dataIndex: 'settings', width: 650, slots: { customRender: 'settings' } },
  { title: 'Action', dataIndex: 'operation', slots: { customRender: 'operation' } },
]

export default defineComponent({
  name: "FieldsFormTable",
  props: {
    year: { type: Number, default: new Date().getFullYear() },
    isCreate: Boolean,
    allAccess: Boolean,
  },
   
  emits: ['handle-save'],
  setup(props, { emit }) {
    const store = useStore()

    // DATA
    const editableData = reactive({})

    // COMPUTED
    const formFields = computed(() => {
      const fields = store.state.formManager.formFields

      return fields.map(v => {
        let name = "Not Set"
        if(v.settings) {
          const filtered = functions.value.filter(i => v.settings.setting === i.id)
          name = filtered.length > 0 ? filtered[0]['name'] : ''
        }
        return {...v, settingLabel: name}
      })
    })
    const functions = computed(() => store.getters['formManager/functions'])
    const loading = computed(() => store.getters['formManager/manager'].loading)

    // METHODS
    const edit = key => {
      const settings = cloneDeep(formFields.value.filter(item => key === item.id)[0]['settings'])
      if(settings) {
        const details = functions.value.filter(item => item.id === settings.setting)[0]
        editableData[key] = {
          settings: {
            key: details.key,
            id: details.id,
            label: details.name,
          },
          settingId: settings.id,
        }
      }else {
        editableData[key] = { settings: null }
      }
    }

    const save = async data => {
      data.isUpdate = !!data.settings
      data.settings = editableData[data.id]
      await emit('handle-save', data)
      await cancel(data.id)
      /*Object.assign(formFields.value.filter(item => id === item.id)[0], editableData[id])
      delete editableData[id]*/
    }

    const cancel = key => {
      delete editableData[key]
    }

    return {
      columns,

      editableData,

      formFields,
      functions,
      loading,

      edit,
      save,
      cancel,
    }
  },
})
</script>
