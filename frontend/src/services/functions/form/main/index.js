import { ref, createVNode, computed } from "vue";
import { useStore } from "vuex"
import { Modal } from "ant-design-vue";
import { ExclamationCircleOutlined } from "@ant-design/icons-vue";

export const useFormFields = form => {
  const store = useStore()

  const typeOptions = [{ label: 'PI', value: 'pi'}, { label: 'Sub PI', value: 'sub' }]
  const formItemLayout = { labelCol: { span: 6 }, wrapperCol: { span: 14 }}
  const tooltipHeaderText = 'Check to disable the editing of Target to Other Remarks'

  const storedOffices = ref({ implementing: [], supporting: [] })
  const cachedOffice = ref({ implementing: [], supporting: [] })

  // COMPUTED
  const formFields = computed(() => store.getters['formManager/manager'].formFields)
  const savedIndicators = computed(() => store.getters['vpopcr/form'].savedIndicators)

  const changeNullValue = (value, label) => {
    if (typeof value === 'undefined' || value === 0) {
      // form.value[label] = null
    }
  }

  const filterBasisOption = (input, option) => {
    return option.value.toUpperCase().indexOf(input.toUpperCase()) >= 0;
  }

  const onOfficeChange = (value, label, extra, field) => {
    console.log(extra)
    storedOffices.value[field] = []
    const { allCheckedNodes } = extra
    if (typeof allCheckedNodes !== 'undefined' && allCheckedNodes.length > 0) {
      allCheckedNodes.forEach(item => {
        const { props } = (typeof item.node !== 'undefined') ? item.node : item
        storedOffices.value[field].push(props)
      })
    }
  }

  const saveOfficeList = async (field, opposite) => {
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
          let duplicates = ""
          const findList = form.value[opposite]

          for await (const item of list) {
            if(findDuplicates(findList, item) === true) {
              if(duplicates !== "") {
                duplicates += ", "
              }

              let label = typeof item.acronym !== 'undefined' ? item.acronym : item.title

              duplicates = duplicates + label
            }
          }

          if(duplicates !== '') {
            Modal.error({
              title: () => 'Error',
              content: () => "Unable to set " + duplicates + " as Implementing and Supporting",
            })
          } else {
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
  }

  const findDuplicates = (arr, item) => {
    return arr.some(function(e) {
      if(e.value === item.value || (typeof e.pId !== 'undefined' && e.pId === item.value)
        || (typeof item.pId !== 'undefined' && e.value === item.pId)) {
        return true
      }
    })
  }

  const checkDefaultCascadeTo = async params => {
    const { field, opposite } = params
    const list = storedOffices.value[field]

    if(list.length) {
      const filtered = formFields.value.filter(i => i.code === field)

      if(filtered.length) {
        const cascadeTo = filtered[0].settings !== null ? filtered[0].settings.setting : null

        let duplicates = "", assigned = null
        const findList = form.value[opposite]

        const searchList = savedIndicators.value.filter(i => { return i.pi_name === form.value.name })

        for await (const item of list) {
          if(findDuplicates(findList, item) === true) {
            if(duplicates !== "") {
              duplicates += ", "
            }

            let label = typeof item.acronym !== 'undefined' ? item.acronym : item.title

            duplicates = duplicates + label
          }

          if(searchList.length > 0) {
            searchList.forEach(slist => {
              const filter = slist.offices.filter(i => { return item.value === parseInt(i.office_id)})
              if(filter.length > 0) {
                console.log(filter)
                assigned = {
                  office: slist.vpopcr.office_name,
                  selected: item.acronym || item.title,
                  assignedField: filter[0].field.field_name,
                }
              }
            })
          }
        }

        if(duplicates !== '') {
          Modal.error({
            title: () => 'Error',
            content: () => "Unable to set " + duplicates + " as Implementing and Supporting",
          })
        } else if(assigned !== null) {
          const { office, selected, assignedField } = assigned
          let splitField = assignedField.split(' ')
          Modal.error({
            title: () => 'Error saving data',
            content: () => office + " already set " + selected + " as " + splitField[0],
          })
        } else {
          saveOfficeListVP({ list: list, field: field, defaultCascade: cascadeTo})
        }
      }
    }
  }

  const onOfficeChangeVP = (value, label, extra, field) => {
    storedOffices.value[field] = []
    const { allCheckedNodes } = extra
    if (typeof allCheckedNodes !== 'undefined' && allCheckedNodes.length > 0) {
      allCheckedNodes.forEach(item => {
        if(item.children.length > 0) {
          item.children.forEach(i => {
            const { props } = (typeof i.node !== 'undefined') ? i.node : i
            storedOffices.value[field].push(props)
          })
        }else {
          const { props } = (typeof item.node !== 'undefined') ? item.node : item
          storedOffices.value[field].push(props)
        }
      })
    }
  }

  const saveOfficeListVP = params => {
    const { list, field, defaultCascade } = params

    form.value[field] = mappedOfficeList(list, field, defaultCascade)
    form.value.options[field] = []
    storedOffices.value[field] = []

    if (cachedOffice.value[field].length) {
      cachedOffice.value[field] = []
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
      container.title = typeof item.title !== 'undefined' ? item.title : item.label
      if (typeof item.pId === 'undefined') {
        container.children = true
      } else {
        if (typeof item.isGroup === 'undefined') {
          container.acronym = item.acronym
        }
        container.pId = item.pId
      }

      if (typeof item.is_subunit !== 'undefined' && item.is_subunit) {
        container.vp_id = item.vp_id
        container.is_subunit = item.is_subunit
      }

      const hasCached = cachedOffice.value[field].filter(i => {
        return i.value === item.value || i.value === item.value.toString()
      })

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
      onOk() { form.value[field].splice(index, 1) },
      onCancel() {},
    })
  }

  const syncCascadeOption = (field, index, value) => {
    const offices = form.value[field]

    if(index < 1) { offices.forEach((data, i) => { if(i) { data.cascadeTo = value } }) }
  }

  return {
    typeOptions, formItemLayout, tooltipHeaderText, storedOffices, cachedOffice,

    changeNullValue, filterBasisOption, onOfficeChange, saveOfficeList, checkDefaultCascadeTo,
    onOfficeChangeVP, updateOfficeList, deleteOfficeItem, syncCascadeOption,
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
