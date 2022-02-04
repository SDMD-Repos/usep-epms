import { ref, reactive } from 'vue'

export const useDrawerSettings = () => {
  const initialData = () => ({
    open: false,
    okText: '',
    modalTitle: '',
    updateId: null,
    type: 'pi',
    parentDetails: undefined,
  })

  const data = ref(initialData())

  const openDrawer = params => {
    const { action } = params
    switch(action) {
      case "Add":
        data.value = {
          open: true,
          okText: action,
          modalTitle: 'Add New Performance Indicator',
          updateId: null,
          type: 'pi',
          parentDetails: undefined,
        }
        return false
      case 'newsub':
        data.value = {
          open: true,
          okText: 'Add Sub PI',
          modalTitle: 'New Sub Performance Indicator',
          updateId: null,
          type: 'sub',
          parentDetails: params.parentDetails,
        }
        return false
      case 'Update':
        data.value = {
          open: true,
          okText: action,
          modalTitle: 'Update Performance Indicator',
          updateId: params.updateId,
          type: params.type,
          parentDetails: params.parentDetails,
        }
        return false
    }
  }

  const resetDrawerSettings = isNew => {
    data.value = initialData()
    if (isNew) {
      openDrawer({ action: 'Add' })
    }
  }

  return {
    drawerConfig: data,

    openDrawer,
    resetDrawerSettings,
  }
}

/* ------------------------------------------ */

const defaultAapcrFormData = {
  subCategory: null,
  name: '',
  isHeader: false,
  target: '',
  measures: [],
  budget: null,
  targetsBasis: '',
  cascadingLevel: null,
  implementing: [],
  supporting: [],
  options: {
    implementing: [],
    supporting: [],
  },
  remarks: '',
}

export const useDefaultFormData = props => {
  const defaultData = ref({})
  let rules

  switch (props.formId) {
    case 'aapcr':
      defaultData.value = defaultAapcrFormData
  }

  const formData = reactive(defaultData.value)

  // VALIDATORS
  let validateNonHeader = async (rule, value) => {
    if (!formData.isHeader) {
      if (value === '' || value === null || (Array.isArray(value) && !value.length) || typeof value === 'undefined') {
        if((rule.field === 'implementing' && formData.options.implementing.length) || (rule.field === 'supporting' && formData.options.supporting.length)) {
          return Promise.reject('Please click the check icon to save the data')
        } else {
          if(rule.field === 'supporting') {
            return Promise.resolve()
          }

          return Promise.reject('This field is required')
        }
      } else {
        return Promise.resolve()
      }
    } else {
      return Promise.resolve()
    }
  }

  let subCategoryValidator = async (rule, value) => {
    if ((props.functionId !== 'support_functions') && value === null) {
      return Promise.reject('Please select at least one')
    } else {
      return Promise.resolve()
    }
  }

  switch (props.formId) {
    case 'aapcr':
      rules = reactive({
        subCategory: [
          { validator: subCategoryValidator, trigger: 'blur' },
          { type: 'object' },
        ],
        name: [{ required: true, message: 'This field is required', trigger: 'blur' }],
        target: [{ validator: validateNonHeader, trigger: 'blur'}],
        measures: [{ validator: validateNonHeader, trigger: 'blur'}],
        targetsBasis: [{ validator: validateNonHeader, trigger: 'blur' }],
        cascadingLevel: [{ validator: validateNonHeader, trigger: 'blur'}],
        implementing: [
          { validator: validateNonHeader, trigger: 'blur'},
          { type: 'array' },
        ],
        supporting: [
          { validator: validateNonHeader, trigger: 'blur'},
          { type: 'array' },
        ],
      })
  }

  const resetFormAsHeader = () => {
    switch (props.formId) {
      case 'aapcr':
        formData.cascadingLevel = null
    }

    formData.target = ''
    formData.measures = []
    formData.budget = null
    formData.targetsBasis = ''
    formData.implementing = []
    formData.supporting = []
    formData.options.implementing = []
    formData.options.supporting = []
    formData.remarks = ''
  }

  const assignFormData = newData => {
    switch (props.formId) {
      case 'aapcr':
        formData.cascadingLevel = newData.cascadingLevel
    }

    formData.subCategory = newData.subCategory
    formData.name = newData.name
    formData.isHeader = newData.isHeader
    formData.target = newData.target
    formData.measures = newData.measures
    formData.budget = newData.budget
    formData.targetsBasis = newData.targetsBasis
    formData.implementing = newData.implementing
    formData.supporting = newData.supporting
    formData.remarks = newData.remarks
  }

  return {
    formData,
    rules,

    resetFormAsHeader,
    assignFormData,
  }
}

/* ------------------------------------------ */

export const useFormOperations = () => {
  const targetsBasisList = ref([])
  const counter = ref(0)
  const dataSource = ref([])

  const updateDataSource = ({data, isNew}) => {
    if(isNew) {
      dataSource.value.push(data)
    }else {
      dataSource.value = data
    }
    updateSourceCount(counter.value + 1)
  }

  const deleteSourceItem = key => {
    dataSource.value.splice(key, 1)
  }
  const addTargetsBasisItem = data => {
    targetsBasisList.value.push({ value: data })
  }

  const updateSourceCount = data => {
    counter.value = data
  }

  const updateSourceItem = data => {
    const { updateData, updateId } = data
    if(data.type === 'pi') {
      Object.assign(dataSource.value[updateId], updateData.value)
      const { children } = dataSource.value[updateId]
      if (typeof children !== 'undefined' && children.length) {
        console.log('children', children)
        children.forEach(i => {
          i.subCategory = updateData.value.subCategory
          i.targetsBasis = updateData.value.targetsBasis
          i.cascadingLevel = updateData.value.cascadingLevel
        })
      }
    } else {
      const { parentId } = data
      const parentIndex = dataSource.value.findIndex(i => i.key === parentId)
      const { children } = dataSource.value[parentIndex]
      Object.assign(children[updateId], updateData.value)
    }
  }

  return {
    dataSource,
    targetsBasisList,
    counter,

    updateDataSource,
    addTargetsBasisItem,
    updateSourceCount,
    deleteSourceItem,
    updateSourceItem,
  }
}
