<template>
  <a-table :columns="columns" :data-source="formFields" row-key="id" :pagination="false" bordered>
    <template #settings="{ record }">
      <div v-if="record.code === 'implementing' || record.code === 'supporting'">
        <span v-if="editableData[record.id] || typeof record.setting === 'undefined'">
          <a-select v-model:value="editableData[record.id]['setting']" style="width: 400px">
            <a-select-option v-for="data in functions"
                             :value="data.id"
                             :key="data.id"
                             :label="data.name">
              {{ data.name }}
            </a-select-option>
          </a-select>
        </span>
        <template v-else>{{ typeof record.setting !== 'undefined' ? record.setting : 'Not set' }}</template>
      </div>
    </template>

    <template #operation="{ record }">
      <span v-if="editableData[record.id] || typeof record.setting === 'undefined'">
        <a-button type="link">Save</a-button>
        <a-popconfirm v-if="typeof record.setting !== 'undefined'" title="Sure to cancel?" @confirm="cancel(record.if)">
          <a type="primary">Cancel</a>
        </a-popconfirm>
      </span>
      <span v-else>
        <a-button type="link" @click="edit(record.id)">Edit</a-button>
      </span>
    </template>
  </a-table>
</template>
<script>
import { defineComponent, reactive, computed, onMounted } from "vue"
import { useStore } from "vuex"
import { cloneDeep } from 'lodash-es'

const columns = [
  { title: 'Field', dataIndex: 'field_name', key: 'field_name', width: 500 },
  { title: 'Settings', dataIndex: 'settings', slots: { customRender: 'settings' } },
  { title: 'Action', dataIndex: 'operation', slots: { customRender: 'operation' } },
]

export default defineComponent({
  name: "FieldsFormTable",
  props: {
    year: { type: Number, default: new Date().getFullYear() },
  },
  setup(props) {
    const store = useStore()

    // DATA
    const editableData = reactive({})

    // COMPUTED
    const formFields = computed(() => store.getters['formManager/manager'].formFields)
    const functions = computed(() => store.getters['formManager/functions'])


    // EVENTS
    onMounted(() => {
      store.dispatch('formManager/FETCH_FORM_FIELDS')
      store.dispatch('formManager/FETCH_FUNCTIONS', { payload: { year: props.year }})
    })

    // METHODS
    const edit = key => {
      editableData[key] = cloneDeep(formFields.value.filter(item => key === item.id)[0])
    }

    const save = key => {
      Object.assign(formFields.value.filter(item => key === item.id)[0], editableData[key])
      delete editableData[key]
    }

    const cancel = key => {
      delete editableData[key]
    }

    return {
      columns,

      editableData,

      formFields,
      functions,

      edit,
      save,
      cancel,
    }
  },
})
</script>
