<template>
  <div>
    <a-select v-model:value="mainCategory" placeholder="Select" style="width: 350px" label-in-value @change="loadPIs">
      <a-select-option v-for="(y, i) in programsByFunction" :value="y.id" :key="i">
        {{ y.name }}
      </a-select-option>
    </a-select>

    <div class="mt-4">
      <indicator-table :year="year" :form-id="formId" :item-source="itemSource" :main-category="mainCategory"
                       @open-drawer="openDrawer" @delete-item="deleteItem"/>

      <aapcr-form-drawer :drawer-config="drawerConfig" :form-object="formData" :drawer-id="functionId"
                         :targets-basis-list="targetsBasisList" :categories="categories" :current-year="year"
                         :validate="validate" :validate-infos="validateInfos"
                         @toggle-is-header="resetFormAsHeader" @add-table-item="addTableItem" @close-drawer="closeDrawer"/>
    </div>
  </div>
</template>
<script>
import { computed, defineComponent, ref, watch, reactive, onMounted, createVNode } from "vue"
import { useStore } from 'vuex'
import IndicatorTable from '@/components/Tables/Forms/Main'
import AapcrFormDrawer from '@/components/Drawer/Forms/Aapcr'
import { useDrawerSettings, useDefaultFormData } from '@/services/functions/indicator'
import { Form, Modal } from 'ant-design-vue'
import { ExclamationCircleOutlined } from "@ant-design/icons-vue";

const useForm = Form.useForm

export default defineComponent({
  name: "AapcrItems",
  components: { IndicatorTable, AapcrFormDrawer },
  props: {
    year: { type: Number, default: new Date().getFullYear() },
    functionId: { type: String, default: "" },
    categories: { type: Array, default: () => { return [] }},
    itemSource: { type: Array, default: () => { return [] }},
    formId: { type: String, default: "" },
    targetsBasisList: { type: Array, default: () => { return [] }},
    counter: { type: Number, default: 0 },
  },
  emits: ['add-targets-basis-item', 'update-counter', 'update-data-source', 'delete-source-item', 'add-deleted-item'],
  setup(props, { emit }) {
    const store = useStore()

    // DATA
    const mainCategory = ref(undefined)
    const displayIndicatorList = ref(0)
    const count = ref(0)
    const dataSource = ref([])

    // COMPUTED
    const programs = computed(() => store.getters['formManager/manager'].programs)

    const programsByFunction = computed( () => { return programs.value.filter(i => i.category_id === props.functionId) })

    const {
      drawerConfig,
      openDrawer, resetDrawerSettings } = useDrawerSettings()

    const { formData, rules, resetFormAsHeader } = useDefaultFormData(props)

    // EVENTS
    onMounted(() => {

    })

    watch(() => [props.itemSource, props.counter], ([itemSource, counter]) => {
      dataSource.value = itemSource
      count.value = counter
    })

    const { resetFields, validate, validateInfos } = useForm(formData, rules)

    // METHODS
    const loadPIs = e => {
      if (e !== '' && typeof e !== 'undefined') {
        displayIndicatorList.value = 1
      }
    }

    const addTableItem = async data => {
      if (!data.value.isHeader) {
        const { targetsBasis } = data.value
        if (targetsBasis !== '' && typeof targetsBasis !== 'undefined' && props.targetsBasisList.indexOf(targetsBasis) === -1) {
          await emit('add-targets-basis-item', targetsBasis)
        }
      }

      const key = 'new_' + count.value
      const newData = {
        key: key,
        id: key,
        type: drawerConfig.value.type,
        subCategory: data.value.subCategory,
        program: mainCategory.value.key,
        name: data.value.name,
        isHeader: data.value.isHeader,
        target: data.value.target,
        measures: data.value.measures,
        budget: data.value.budget,
        targetsBasis: data.value.targetsBasis,
        cascadingLevel: data.value.cascadingLevel,
        implementing: data.value.implementing,
        supporting: data.value.supporting,
        otherRemarks: data.value.otherRemarks,
      }

      if (drawerConfig.value.type === 'pi') {
        await emit('update-data-source', { data: newData, isNew: true })
        if (newData.isHeader) {
          Modal.confirm({
            title: () => 'Do you want to add a sub PI?',
            icon: () => createVNode(ExclamationCircleOutlined),
            content: () => '',
            okText: 'Yes',
            cancelText: 'No',
            onOk() {
              handleAddSub(key)
            },
            onCancel() {},
          })
        }
      } else {
        const { parentDetails } = drawerConfig.value
        const source = [ ...dataSource.value ]
        const target = reactive(dataSource.value.filter(item => parentDetails.key === item.key)[0])
        const index = dataSource.value.findIndex(item => parentDetails.key === item.key)
        if (typeof source[index].children === 'undefined') {
          source[index]['children'] = new Proxy([], {})
        }
        target['children'].push(newData)
        await emit('update-data-source', { data: source, isNew: false })
      }

      await resetFields()
    }

    const handleAddSub = key => {
      const newData = dataSource.value.filter(item => key === item.key)[0]
      formData.subCategory = newData.subCategory
      if (!newData.isHeader) {
        formData.measures = newData.measures
        formData.targetsBasis = newData.targetsBasis
        formData.cascadingLevel = newData.cascadingLevel
        formData.implementing = newData.implementing
        formData.supporting = newData.supporting
      }
      openDrawer('newsub', { ...newData })
    }

    const deleteItem = data => {
      let deletedData = null
      if(data.type === 'pi') {
        const recordKey = dataSource.value.findIndex((record, i) => record.key === data.key)
        deletedData = dataSource.value[recordKey]
        emit('delete-source-item', recordKey)
      }

      if (deletedData) {
        const { id } = deletedData
        if (id.toString().indexOf('new') === -1) {
          emit('add-deleted-item', id)
        }
      }
    }

    const closeDrawer = async () => {
      await resetDrawerSettings()
      await resetFields()
    }

    return {
      mainCategory,

      programsByFunction,

      // useDrawerSettings
      drawerConfig,
      openDrawer,

      // useDefaultFormData
      formData,
      resetFormAsHeader,

      validateInfos,
      validate,
      dataSource,

      loadPIs,
      addTableItem,
      closeDrawer,
      deleteItem,
    }
  },
})
</script>
