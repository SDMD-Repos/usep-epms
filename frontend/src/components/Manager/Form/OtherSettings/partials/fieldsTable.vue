<template>
  <div>
    <a-divider>Fields</a-divider>
    <div class="mt-4">
      <a-select v-model:value="formId" placeholder="Select a form"
                style="width: 200px" @change="getFormFieldsCascade">
        <a-select-option v-for="data in formList" :value="data.id" :key="data.id" :label="data.abbreviation">
          {{ data.abbreviation }}
        </a-select-option>
      </a-select>
    </div>
    <a-table class="mt-4" :columns="columns" :data-source="formFields" row-key="id" :pagination="false"
             :show-header="false" bordered>
      <template #settings="{ record }">
        <div v-if="record.code === 'implementing' || record.code === 'supporting'">
          <span v-if="record.id in editableData">
            <div v-if="formId === 'aapcr'">
              <a-select v-model:value="editableData[record.id]['settings']" placeholder="Select"
                        label-in-value style="width: 400px">
              <a-select-option v-for="data in functions" :value="data.id.toString()" :key="data.id" :label="data.name">
                {{ data.name }}
              </a-select-option>
            </a-select>
            </div>
            <div v-else-if="formId === 'vpopcr'">
              <a-tree-select
                v-model:value="editableData[record.id]['settings']"
                placeholder="Select a Program"
                style="width: 400px"
                :dropdown-style="{ maxHeight: '400px', overflow: 'auto' }"
                :tree-data="functionsWithProgram"
                tree-default-expand-all
                label-in-value
              />
            </div>
          </span>
          <template v-else-if="formId">Cascade to: <b>{{ record.settingLabel }}</b></template>
        </div>
      </template>

      <template #operation="{ record }" >
        <div v-if="formId">
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
        </div>
      </template>
    </a-table>
  </div>
</template>
<script>
import { defineComponent, reactive, ref, onMounted, computed } from "vue"
import { useStore } from "vuex"
import { cloneDeep } from 'lodash-es'
import { Modal } from "ant-design-vue"
import { useExtras } from "@/services/functions/extras";
import { useModifiedStates } from '@/services/functions/modifiedStates'

const columns = [
  { title: 'Field', dataIndex: 'field_name', key: 'field_name', width: 500 },
  { title: 'Settings', dataIndex: 'settings', width: 650, slots: { customRender: 'settings' } },
  { title: 'Action', dataIndex: 'operation', slots: { customRender: 'operation' } },
]

export default defineComponent({
  name: "FieldsTable",
  props: {
    year: { type: Number, default: new Date().getFullYear() },
    isCreate: Boolean,
  },
  emits: ['handle-save'],
  setup(props, { emit }) {
    const store = useStore()

    const { findInNested } = useExtras()

    // DATA
    const editableData = reactive({})
    const formId = ref(null)

    // COMPUTED
    const functions = computed(() => store.getters['formManager/functions'])
    const formList = computed(() => store.getters['formManager/manager'].forms)

    const formFields = computed(() => {
      const fields = store.state.formManager.formFields

      return fields.map(v => {
        let name = "Not Set", filtered = null
        if(v.settings) {
          switch (formId.value) {
            case 'vpopcr':
              filtered = findInNested(functionsWithProgram.value, v.settings.setting)
              name = typeof filtered !== 'undefined' ? filtered.name : ''
              break
            default:
              filtered = functions.value.filter(i => parseInt(v.settings.setting) === i.id)
              name = filtered.length > 0 ? filtered[0]['name'] : ''
              break
          }

        }
        return {...v, settingLabel: name}
      })
    })

    const { functionsWithProgram } = useModifiedStates()

    // EVENTS
    onMounted(() => {
      onLoad()
    })

    // METHODS
    const onLoad = async () => {
      await getFormFieldsCascade(null)
      await store.dispatch('formManager/FETCH_ALL_FORMS')
      await store.dispatch('formManager/FETCH_PROGRAMS', { payload : { year: props.year }})
      await store.dispatch('formManager/FETCH_OTHER_PROGRAMS', { payload : { formId: 'opcr', year: props.year }})
    }

    const getFormFieldsCascade = value => {
      const cloneEditableData = cloneDeep(editableData)
      for (const e in cloneEditableData) {
        delete editableData[e]
      }

      store.dispatch('formManager/FETCH_FORM_FIELDS', { payload: { year: props.year, formId: value }})
    }

    const edit = key => {
      if (!formId.value) {
        Modal.error({
          title: () => 'Unable to edit this option',
          content: () => 'Please select a form first',
        })
      } else {
        const settings = cloneDeep(formFields.value.filter(item => key === item.id)[0]['settings'])
        if(settings) {
          let details
          switch (formId.value) {
            case 'vpopcr':
              details = findInNested(functionsWithProgram.value, settings.setting)
              break
            default:
              details = functions.value.filter(item => item.id === parseInt(settings.setting))[0]
              break
          }

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
    }

    const save = async data => {
      data.isUpdate = !!data.settings
      data.settings = editableData[data.id]
      data.formId = formId.value
      await emit('handle-save', data)
      await cancel(data.id)
    }

    const cancel = key => {
      delete editableData[key]
    }

    return {
      columns,

      editableData, formId,

      functions, formList, functionsWithProgram, formFields,

      getFormFieldsCascade, edit, save, cancel,
    }
  },
})
</script>
