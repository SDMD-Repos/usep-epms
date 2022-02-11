<template>
  <div>
    <a-select v-model:value="mainCategory" placeholder="Select" style="width: 350px" label-in-value @change="loadPIs">
      <a-select-option v-for="(y, i) in programsByFunction" :value="y.id" :key="i">
        {{ y.name }}
      </a-select-option>
    </a-select>

    <div class="mt-4">
      <indicator-table :year="year" :form-id="formId" :item-source="dataSource" :budget-list="budgetList"
                       :main-category="mainCategory" :form-table-columns="formTableColumns"
                       @open-drawer="openDrawer" @add-sub-item="handleAddSub" @edit-item="editItem"
                       @delete-item="deleteItem" @add-budget-list-item="addBudgetListItem"/>

      <aapcr-form-drawer :drawer-config="drawerConfig" :form-object="formData" :drawer-id="functionId"
                         :targets-basis-list="targetsBasisList" :categories="categories" :current-year="year"
                         :validate="validate" :validate-infos="validateInfos"
                         @toggle-is-header="resetFormAsHeader" @add-table-item="addTableItem" @update-table-item="updateTableItem"
                         @close-drawer="closeDrawer" />
    </div>
  </div>
</template>
<script>
import { defineComponent, ref, watch, reactive, onMounted, createVNode, computed } from "vue"
import { useStore } from 'vuex'
import { Form, Modal } from 'ant-design-vue'
import { ExclamationCircleOutlined } from '@ant-design/icons-vue'
import { formTableColumns } from "@/services/columns"
import IndicatorTable from '@/components/Tables/Forms/Main'
import AapcrFormDrawer from '@/components/Drawer/Forms/Aapcr'
import { useDrawerSettings, useDefaultFormData } from '@/services/functions/indicator'

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
    budgetList: { type: Array, default: () => { return [] }},
    counter: { type: Number, default: 0 },
  },
  emits: ['add-targets-basis-item', 'update-counter', 'update-data-source', 'delete-source-item', 'add-deleted-item',
    'update-source-item', 'add-budget-list-item'],
  setup(props, { emit }) {
    const store = useStore()

    // DATA
    const mainCategory = ref(undefined)
    const displayIndicatorList = ref(0)
    const count = ref(0)
    const dataSource = computed(()=> { return props.itemSource })

    // COMPUTED
    const programs = computed(() => store.getters['formManager/manager'].programs)

    const programsByFunction = computed( () => { return programs.value.filter(i => i.category_id === props.functionId) })

    const {
      drawerConfig,
      openDrawer, resetDrawerSettings } = useDrawerSettings()

    const { formData, rules, resetFormAsHeader, assignFormData } = useDefaultFormData(props)

    // EVENTS
    onMounted(() => {

    })

    watch(() => props.counter, counter => {
      count.value = counter
    })

    const { resetFields, validate, validateInfos } = useForm(formData, rules)

    // METHODS
    const loadPIs = e => {
      if (e !== '' && typeof e !== 'undefined') {
        displayIndicatorList.value = 1
      }
    }

    const isTargetsExists = find => {
      let isExists = false
      props.targetsBasisList.forEach(data => {
        if(isExists) {
          return
        }
        isExists = data.value === find
      })

      return isExists
    }

    const addTableItem = async data => {
      if (!data.value.isHeader) {
        const { targetsBasis } = data.value
        if (targetsBasis !== '' && typeof targetsBasis !== 'undefined' && !isTargetsExists(targetsBasis)) {
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
        remarks: data.value.remarks,
      }

      if (drawerConfig.value.type === 'pi') {
        await emit('update-data-source', { data: newData, isNew: true })
        await resetFields()
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
        await resetFields()
        await handleAddSub(parentDetails.key)
      }
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
      openDrawer({ action: 'newsub', parentDetails: { ...newData }})
    }

    const editItem = data => {
      let editData = null, updateId = null, parentDetails = undefined
      if (data.type === 'pi') {
        editData = dataSource.value.filter(item => item.key === data.key)[0]
        updateId = dataSource.value.findIndex(record => record.key === data.key)
      } else {
        let shouldBreak = false

        dataSource.value.forEach(item => {
          if (typeof item.children !== 'undefined') {
            const temp = item.children.filter(i => i.key === data.key)
            if (shouldBreak) {
              return
            }
            if (temp.length) {
              editData = temp[0]
              shouldBreak = true
              updateId = item.children.findIndex(i => i.key === data.key)
              parentDetails = { ...item }
              return
            }
            console.log(temp)
          }
        })
      }
      assignFormData(editData)

      openDrawer({ action: 'Update', updateId: updateId, type: data.type, parentDetails: parentDetails })
    }

    const updateTableItem = async data => {
      if (drawerConfig.value.type === 'pi') {
        if (!data.updateData.isHeader) {
          const {targetsBasis} = data.updateData
          if (targetsBasis !== '' && typeof targetsBasis !== 'undefined' && !isTargetsExists(targetsBasis)) {
            await emit('add-targets-basis-item', targetsBasis)
          }
        }
      }

      const { parentDetails } = drawerConfig.value
      await emit('update-source-item', {
        updateData: data.updateData,
        updateId: data.updateId,
        type: drawerConfig.value.type,
        parentId: ((typeof parentDetails !== 'undefined') ? parentDetails.key : undefined),
      })
      await closeDrawer(0)
    }

    const deleteItem = data => {
      let deletedData = null
      if(data.type === 'pi') {
        const recordKey = dataSource.value.findIndex((record, i) => record.key === data.key)
        deletedData = dataSource.value[recordKey]
        emit('delete-source-item', recordKey)
      }else {
        dataSource.value.forEach((item) => {
          if (typeof item.children !== 'undefined') {
            const recordKey = item.children.findIndex(i => i.key === data.key)
            if (recordKey !== -1) {
              deletedData = item.children[recordKey]
              item.children.splice(recordKey, 1)
              if (!item.children.length) {
                delete item.children
              }
              return
            }
            console.log(recordKey)
          }
        })
        emit('update-data-source', { data: dataSource.value, isNew: false })
      }

      if (deletedData) {
        const { id } = deletedData
        if (id.toString().indexOf('new') === -1) {
          emit('add-deleted-item', id)
        }
      }
    }

    const closeDrawer = async isNewIndicator => {
      await resetDrawerSettings(isNewIndicator)
      await resetFields()
    }

    const addBudgetListItem = data => {
      emit('add-budget-list-item', data)
    }

    return {
      formTableColumns,
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
      handleAddSub,
      closeDrawer,
      editItem,
      updateTableItem,
      deleteItem,
      addBudgetListItem,
    }
  },
})
</script>
