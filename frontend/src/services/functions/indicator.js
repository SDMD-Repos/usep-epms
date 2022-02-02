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

  const openDrawer = (action, parentDetails={}) => {
    switch(action) {
      case "Add":
        data.value = {
          open: true,
          okText: action,
          modalTitle: 'Add New',
          updateId: null,
          type: 'pi',
          parentDetails: undefined,
        }
        return false
      case 'newsub':
        data.value = {
          open: true,
          okText: 'Add Sub PI',
          modalTitle: 'New Sub PI',
          updateId: null,
          type: 'sub',
          parentDetails: parentDetails,
        }
    }
  }

  const resetDrawerSettings = () => {
    data.value = initialData()
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
  otherRemarks: '',
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
          { validator: subCategoryValidator },
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
    formData.otherRemarks = ''
  }

  return {
    formData,
    rules,

    resetFormAsHeader,
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
      updateSourceCount(dataSource.value.length)
    }else {
      dataSource.value = data
    }

  }
  const addTargetsBasisItem = data => {
    targetsBasisList.value.push(data)
  }

  const updateSourceCount = data => {
    counter.value = data
  }

  return {
    dataSource,
    targetsBasisList,
    counter,

    updateDataSource,
    addTargetsBasisItem,
    updateSourceCount,
  }
}
