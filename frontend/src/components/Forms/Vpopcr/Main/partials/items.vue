<template>
  <div>
    <indicator-table :year="year" :form-id="formId" :function-id="functionId" :form-table-columns="modifiedTableColumns"
                     :item-source="dataSource" :allow-edit="allowEdit"
                     @open-drawer="openDrawer" @edit-item="editItem" @delete-item="deleteItem" @add-sub-item="handleAddSub"/>

    <opcr-vp-form :drawer-config="drawerConfig" :form-object="formData" :drawer-id="functionId" :rules="rules" :current-year="year"
                  :targets-basis-list="targetsBasisList"
                  @toggle-is-header="resetFormAsHeader" @add-table-item="addTableItem" @update-table-item="updateTableItem"
                  @close-drawer="closeDrawer" />
  </div>
</template>
<script>
import { defineComponent, ref, reactive, watch, onBeforeMount, createVNode, computed } from "vue"
import { useStore } from 'vuex'
import { cloneDeep } from 'lodash'
import { Modal } from "ant-design-vue"
import { ExclamationCircleOutlined } from "@ant-design/icons-vue"
import { formTableColumns } from "@/services/columns"
import { useDrawerSettings, useDefaultFormData } from '@/services/functions/indicator'
import IndicatorTable from '@/components/Tables/Forms/Main'
import OpcrVpForm from '@/components/Drawer/Forms/VpOpcr'

export default defineComponent({
  name: 'OpcrVpItems',
  components: { IndicatorTable, OpcrVpForm },
  props: {
    year: { type: Number, default: new Date().getFullYear() },
    formId: { type: String, default: "" },
    functionId: { type: Number, default: null },
    categories: { type: Array, default: () => { return [] }},
    itemSource: { type: Array, default: () => { return [] }},
    allowEdit: { type: Boolean, default: false },
    targetsBasisList: { type: Array, default: () => { return [] }},
    counter: { type: Number, default: 0 },
  },
  emits: ['add-targets-basis-item', 'update-data-source', 'update-source-item', 'delete-source-item', 'add-deleted-item'],
  setup(props, { emit }) {
    const store = useStore()

    // DATA
    const modifiedTableColumns = ref()
    const count = ref(0)
    const dataSource = computed(()=> { return props.itemSource })

    const {
      drawerConfig,
      openDrawer, resetDrawerSettings } = useDrawerSettings()

    const computedConfig = computed(() => { return drawerConfig })

    const parameters = reactive({...props, config: computedConfig })

    const { formData, rules, resetVpOpcrForm, resetFormAsHeader, assignFormData } = useDefaultFormData(parameters)

    // EVENTS
    onBeforeMount( () => {
      modifyColumns()
    })

    watch(() => props.counter, counter => {
      count.value = counter
    })

    // METHODS
    const modifyColumns = () => {
      let columns = JSON.parse(JSON.stringify(formTableColumns))
      const remarksIndex = columns.findIndex(i => i.key === 'remarks')
      columns[remarksIndex].title = "Remarks"
      const deleteKeys = ['subCategory']
      columns = [...columns.filter(i => deleteKeys.indexOf(i.key) === -1)]
      const addendum = {
        title: '#',
        key: 'count',
        dataIndex: 'count',
        className: 'column-count',
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

    const addTableItem = async params => {
      const { data } = params

      if (!data.isHeader) {
        const { targetsBasis } = data
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
        subCategory: data.subCategory,
        program: typeof data.program !== 'undefined' ? data.program : null,
        name: data.name,
        isHeader: data.isHeader,
        target: typeof data.target !== 'undefined' ? data.target : '',
        measures: typeof data.measures !== 'undefined' ? data.measures : [],
        budget: typeof data.budget !== 'undefined' ? data.budget : null,
        targetsBasis: typeof data.targetsBasis !== 'undefined' ? data.targetsBasis : '',
        cascadingLevel: typeof data.cascadingLevel !== 'undefined' ? data.cascadingLevel : null,
        implementing: typeof data.implementing !== 'undefined' ? data.implementing : [],
        supporting: typeof data.supporting != 'undefined' ? data.supporting : [],
        remarks: typeof data.remarks != 'undefined' ? data.remarks : '',
        deleted: 0,
      }

      if (drawerConfig.value.type === 'pi') {
        await emit('update-data-source', { data: newData, isNew: true })
        await params.resetFields
        await resetOfficesFields()
        if (newData.isHeader) {
          Modal.confirm({
            title: () => 'Do you want to add a sub PI?',
            icon: () => createVNode(ExclamationCircleOutlined),
            content: () => '',
            okText: 'Yes',
            cancelText: 'No',
            onOk() {
              handleAddSub(newData)
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
        await params.resetFields
        await resetOfficesFields()
        await handleAddSub(parentDetails)
      }
    }

    const handleAddSub = record => {
      const newData = dataSource.value.filter(item => { return record.key === item.key && record.category === item.category } )[0]
      formData.subCategory = newData.subCategory ? newData.subCategory : undefined
      formData.program = newData.program
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
        editData = cloneDeep(dataSource.value.filter(item => item.key === data.key)[0])
        updateId = dataSource.value.findIndex(record => record.key === data.key)
      } else {
        let shouldBreak = false

        dataSource.value.forEach(item => {
          if (typeof item.children !== 'undefined') {
            const child = item.children.filter(i => i.key === data.key)
            if (shouldBreak) {
              return
            }
            if (child.length) {
              editData = cloneDeep(child[0])
              shouldBreak = true
              updateId = item.children.findIndex(i => i.key === data.key)
              parentDetails = { ...item }
              return
            }
            console.log(child)
          }
        })
      }
      assignFormData(editData)

      openDrawer({ action: 'Update', updateId: updateId, type: data.type, isCascaded: data.isCascaded, parentDetails: parentDetails })
    }

    const updateTableItem = async data => {
      if (drawerConfig.value.type === 'pi') {
        if (!data.updateData.isHeader) {
          const { targetsBasis } = data.updateData
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
      await closeDrawer({ isNewIndicator: 0, callback: data.resetFields })
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

    const closeDrawer = async params => {
      const { isNewIndicator } = params
      await resetDrawerSettings(isNewIndicator)
      await params.callback
      await resetVpOpcrForm()
      await resetOfficesFields()
    }

    const resetOfficesFields = () => {
      formData.implementing = []
      formData.supporting = []
    }

    return {
      modifiedTableColumns,

      closeDrawer, addTableItem, handleAddSub, editItem, updateTableItem, deleteItem,

      // useDrawerSettings
      drawerConfig, openDrawer,

      // useDefaultFormData
      formData, resetFormAsHeader,

      rules, dataSource,
    }
  },
})
</script>
