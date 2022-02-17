<template>
  <div>
    <indicator-table :year="year" :form-id="formId" :function-id="functionId" :form-table-columns="modifiedTableColumns"
                     :item-source="dataSource" :allow-edit="allowEdit"
                     @open-drawer="openDrawer" @edit-item="editItem" @delete-item="deleteItem" @add-sub-item="handleAddSub"/>

    <opcr-vp-form :drawer-config="drawerConfig" :form-object="formData" :drawer-id="functionId" :current-year="year"
                  :targets-basis-list="targetsBasisList" :categories="categories"  :validate="validate"
                  :validate-infos="validateInfos"
                  @toggle-is-header="resetFormAsHeader" @add-table-item="addTableItem" @update-table-item="updateTableItem"
                  @close-drawer="closeDrawer"/>
  </div>
</template>
<script>
import { defineComponent, computed, ref, reactive, watch, onMounted, createVNode } from "vue"
import { Form, Modal } from "ant-design-vue"
import { ExclamationCircleOutlined } from "@ant-design/icons-vue"
import { formTableColumns } from "@/services/columns"
import { useDrawerSettings, useDefaultFormData } from '@/services/functions/indicator'
import IndicatorTable from '@/components/Tables/Forms/Main'
import OpcrVpForm from '@/components/Drawer/Forms/OpcrVp'

const useForm = Form.useForm

export default defineComponent({
  name: 'OpcrVpItems',
  components: { IndicatorTable, OpcrVpForm },
  props: {
    year: { type: Number, default: new Date().getFullYear() },
    formId: { type: String, default: "" },
    functionId: { type: String, default: "" },
    categories: { type: Array, default: () => { return [] }},
    itemSource: { type: Array, default: () => { return [] }},
    targetsBasisList: { type: Array, default: () => { return [] }},
    allowEdit: { type: Boolean, default: false },
    counter: { type: Number, default: 0 },
  },
  emits: ['add-targets-basis-item', 'update-data-source', 'update-source-item', 'delete-source-item', 'add-deleted-item'],
  setup(props, { emit }) {
    // DATA
    const modifiedTableColumns = ref()
    const count = ref(0)
    const dataSource = computed(()=> { return props.itemSource })

    const {
      drawerConfig,
      openDrawer, resetDrawerSettings } = useDrawerSettings()

    const computedConfig = computed(() => { return drawerConfig })

    const parameters = reactive({...props, config: computedConfig })

    const { formData, rules, resetFormAsHeader, assignFormData } = useDefaultFormData(parameters)

    // EVENTS
    onMounted(() => {
      modifyColumns()
    })

    watch(() => props.counter, counter => {
      count.value = counter
    })

    const { resetFields, validate, validateInfos } = useForm(formData, rules)

    // METHODS
    const modifyColumns = () => {
      let columns = JSON.parse(JSON.stringify(formTableColumns))
      const remarksIndex = columns.findIndex(i => i.key === 'remarks')
      columns[remarksIndex].title = "Remarks"
      const deleteKeys = ['subCategory', 'cascadingLevel']
      columns = [...columns.filter(i => deleteKeys.indexOf(i.key) === -1)]
      const addendum = {
        title: '#',
        key: 'count',
        dataIndex: 'count',
        className: 'column-count',
        slots: { customRender: 'count' },
        width: 60,
      }
      columns.splice(0, 0, addendum)
      modifiedTableColumns.value = columns
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
        category: props.functionId,
        subCategory: data.value.subCategory,
        program: data.value.program,
        name: data.value.name,
        isHeader: data.value.isHeader,
        target: data.value.target,
        measures: data.value.measures,
        budget: data.value.budget,
        targetsBasis: data.value.targetsBasis,
        implementing: data.value.implementing,
        supporting: data.value.supporting,
        remarks: data.value.remarks,
        deleted: 0,
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
      formData.program = newData.program
      if (!newData.isHeader) {
        formData.measures = newData.measures
        formData.targetsBasis = newData.targetsBasis
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
          const { targetsBasis } = data.updateData.value
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

    return {
      modifiedTableColumns,

      closeDrawer,
      addTableItem,
      handleAddSub,
      editItem,
      updateTableItem,
      deleteItem,

      // useDrawerSettings
      drawerConfig,
      openDrawer,

      // useDefaultFormData
      formData,
      resetFormAsHeader,

      validateInfos,
      validate,
      dataSource,
    }
  },
})
</script>
