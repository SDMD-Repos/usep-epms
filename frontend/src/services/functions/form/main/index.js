import { Modal } from "ant-design-vue";
import { ref, createVNode, computed } from "vue";
import { ExclamationCircleOutlined } from "@ant-design/icons-vue";
import { useStore } from "vuex"

export const useFormFields = form => {
  const store = useStore()

  const typeOptions = [{ label: 'PI', value: 'pi'}, { label: 'Sub PI', value: 'sub' }]
  const formItemLayout = { labelCol: { span: 6 }, wrapperCol: { span: 14 }}
  const tooltipHeaderText = 'Check to disable the editing of Target to Other Remarks'

  const storedOffices = ref({ implementing: [], supporting: [] })
  const cachedOffice = ref({ implementing: [], supporting: [] })

  // COMPUTED
  const formFields = computed(() => store.getters['formManager/manager'].formFields)

  const changeNullValue = (value, label) => {
    if (typeof value === 'undefined' || value === 0) {
      form.value[label] = null
    }
  }

  const filterBasisOption = (input, option) => {
    return option.value.toUpperCase().indexOf(input.toUpperCase()) >= 0;
  }

  const onOfficeChange = (value, label, extra, field) => {
    storedOffices.value[field] = []
    const { allCheckedNodes } = extra
    if (typeof allCheckedNodes !== 'undefined' && allCheckedNodes.length > 0) {
      allCheckedNodes.forEach(item => {
        const { dataRef } = (typeof item.node !== 'undefined') ? item.node.props : item.props
        storedOffices.value[field].push(dataRef)
      })
    }
  }

  const saveOfficeList = field => {
    const list = storedOffices.value[field]

    if(list.length) {
      const filtered = formFields.value.filter(i => i.code === field)

      if(filtered.length) {
        if(filtered[0].settings === null) {
          Modal.error({
            title: () => 'Unable to save data',
            content: () => 'No cascading option set in Form Manager for this field',
          })
        }else {
          form.value[field] = mappedOfficeList(list, field, parseInt(filtered[0].settings.setting))
          form.value.options[field] = []
          storedOffices.value[field] = []
          if (cachedOffice.value[field].length) {
            cachedOffice.value[field] = []
          }
        }
      }
    }
  }

  const saveOfficeListVp = field => {
    const list = storedOffices.value[field]
    if(list.length) {
      form.value[field] = mappedOfficeList(list, field, parseInt(form.program ? form.program : {}))
      form.value.options[field] = []
      storedOffices.value[field] = []
      if (cachedOffice.value[field].length) {
        cachedOffice.value[field] = []
      }
    }
  }

  const updateOfficeList = field => {
    form.value.options[field] = form.value[field]
    cachedOffice.value[field] = form.value[field]
    storedOffices.value[field] = form.value[field]
    form.value[field] = []
  }

  const mappedOfficeList = (list, field, cascadeTo) => {
    return list.map(item => {
      const container = {}
      let tempCascadeTo = ''
      container.value = item.value
      container.label = typeof item.title !== 'undefined' ? item.title : item.label
      if (typeof item.children !== 'undefined') {
        container.children = true
      } else {
        if (typeof item.isGroup === 'undefined') {
          container.acronym = item.acronym
        }
        container.pId = item.pId
      }
      const hasCached = cachedOffice.value[field].filter(i => i.value === item.value)
      if (hasCached.length) {
        tempCascadeTo = hasCached[0].cascadeTo
      } else if (typeof (item.cascadeTo) !== 'undefined' && item.cascadeTo) {
        tempCascadeTo = item.cascadeTo
      } else {
        tempCascadeTo = cascadeTo
      }
      container.cascadeTo = tempCascadeTo
      if (typeof item.isGroup !== 'undefined') {
        container.isGroup = item.isGroup
      }
      return container
    })
  }

  const deleteOfficeItem = (field, index) => {
    Modal.confirm({
      title: () => 'Are you sure you want to delete this?',
      icon: () => createVNode(ExclamationCircleOutlined),
      content: () => '',
      okText: 'Yes',
      cancelText: 'No',
      onOk() {
        form.value[field].splice(index, 1)
      },
      onCancel() {},
    })
  }

  return {
    typeOptions,
    formItemLayout,
    tooltipHeaderText,
    storedOffices,

    changeNullValue,
    filterBasisOption,
    onOfficeChange,
    saveOfficeList,
    saveOfficeListVp,
    updateOfficeList,
    deleteOfficeItem,
  }
}

export const useProgramBudget = () => {
  const budgetList = ref([])

  const addBudgetListItem = data => {
    budgetList.value.push(data)
  }

  const deleteBudgetItem = index => {
    budgetList.value.splice(index, 1)
  }

  return {
    budgetList,

    addBudgetListItem,
    deleteBudgetItem,
  }
}
