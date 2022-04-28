import { ref, reactive, computed } from 'vue'
import { useStore } from "vuex"

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

const defaultOpcrVpFormData = () => ({
  program: null,
  subCategory: null,
  name: '',
  isHeader: false,
  target: '',
  measures: [],
  budget: null,
  targetsBasis: '',
  implementing: [],
  supporting: [],
  options: {
    implementing: [],
    supporting: [],
  },
  remarks: '',
})

const defaultOpcrFormData = {
  program: null,
  subCategory: null,
  name: '',
  isHeader: false,
  target: '',
  measures: [],
  budget: null,
  targetsBasis: '',
  implementing: [],
  supporting: [],
  options: {
    implementing: [],
    supporting: [],
  },
  remarks: '',
}

const defaultOpcrTemplateData = {
  subCategory: null,
  name: '',
  isHeader: false,
  target: '',
  measures: [],
}

export const useDefaultFormData = props => {
  const defaultData = ref({})
  let rules

  switch (props.formId) {
    case 'aapcr':
      defaultData.value = defaultAapcrFormData
      break
    case 'opcrvp':
      defaultData.value = defaultOpcrVpFormData()
      break
    case 'opcr':
      defaultData.value = defaultOpcrFormData
      break
    case 'opcrtemplate':
      defaultData.value = defaultOpcrTemplateData
      break
  }

  const formData = reactive(defaultData.value)

  // VALIDATORS
  let validateNonHeader = async (rule, value) => {
    if (!formData.isHeader) {
      if (value === '' || value === null || (Array.isArray(value) && !value.length) || typeof value === 'undefined') {
        if((rule.field === 'implementing' && formData.options.implementing.length)
          || (rule.field === 'supporting' && formData.options.supporting.length)) {
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

  let validateOfficesForVP = async (rule, value) => {
    if (!formData.isHeader) {
      if(rule.field === 'options.implementing' || rule.field === 'options.supporting') {
        if(!formData.implementing.length || !formData.supporting.length) {
          if(value.length) {
            return Promise.reject('Please click the check icon to save the data')
          }else {
            if(rule.field === 'options.supporting') {
              return Promise.resolve()
            }

            if(!formData.implementing.length) {
              return Promise.reject('This field is required')
            }
          }
        }
      }
    } else {
      return Promise.resolve()
    }
  }

  switch (props.formId) {
    case 'aapcr':
      rules = reactive({
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
      break;
    case 'opcrvp':
    case 'opcr':
      let programValidator = async (rule, value) => {
        const { parentDetails } = props.config.value
        const hasParent = (typeof parentDetails === 'undefined') || (typeof parentDetails !== 'undefined' && parentDetails.subCategory !== null)
        if (value === null && hasParent) {
          return Promise.reject('Please select at least one')
        } else {
          return Promise.resolve()
        }
      }
      rules = reactive({
        program: [{ validator: programValidator, trigger: 'blur' }],
        name: [{ required: true, message: 'This field is required', trigger: 'blur' }],
        target: [{ validator: validateNonHeader, trigger: 'blur'}],
        measures: [{ validator: validateNonHeader, trigger: 'blur'}],
        targetsBasis: [{ validator: validateNonHeader, trigger: 'blur' }],
        implementing: [
          { validator: validateOfficesForVP, trigger: 'blur'},
          { type: 'array' },
        ],
        cascadeTo: [{ validator: validateNonHeader, trigger: 'blur'}],
        supporting: [
          { validator: validateOfficesForVP, trigger: 'blur'},
          { type: 'array' },
        ],
      })
      break;
     case 'opcrtemplate':
       rules = reactive({
         name: [{ required: true, message: 'This field is required', trigger: 'blur' }],
         target: [{ validator: validateNonHeader, trigger: 'blur'}],
         measures: [{ validator: validateNonHeader, trigger: 'blur'}],
       })
      break;

  }

  const resetFormAsHeader = () => {
    formData.target = ''
    formData.measures = []
    switch (props.formId) {
      case 'aapcr':
        formData.budget = null
        formData.targetsBasis = ''
        formData.implementing = []
        formData.supporting = []
        formData.options.implementing = []
        formData.options.supporting = []
        formData.remarks = ''
        break
      case 'opcrtemplate':
    }
  }

  const assignFormData = newData => {
    formData.subCategory = newData.subCategory
    formData.name = newData.name
    formData.isHeader = newData.isHeader
    formData.target = newData.target
    formData.measures = newData.measures

    switch (props.formId) {
      case 'aapcr':
        formData.budget = newData.budget
        formData.targetsBasis = newData.targetsBasis
        formData.implementing = newData.implementing
        formData.supporting = newData.supporting
        formData.remarks = newData.remarks
        break
      case 'opcrtemplate':
        break
      case 'opcrvp':
      case 'opcr':
        formData.budget = newData.budget
        formData.targetsBasis = newData.targetsBasis
        formData.implementing = newData.implementing
        formData.supporting = newData.supporting
        formData.remarks = newData.remarks
        formData.program = newData.program
        break
    }
  }

  return {
    formData,
    rules,

    resetFormAsHeader,
    assignFormData,
  }
}

/* ------------------------------------------ */

export const useFormOperations = props => {
  const store = useStore()

  const targetsBasisList = ref([])
  const counter = ref(0)
  const deletedItems = ref([])

  const editMode = ref(false)
  const isFinalized = ref(false)
  const allowEdit = ref(false)

  const year = ref(new Date().getFullYear())
  const cachedYear = ref(null)

  const dataSource = computed(() => store.state[props.formId].dataSource )

  const years = computed(() => {
    const now = new Date().getFullYear() + 1
    const min = 10
    const lists = []
    for (let i = now; i >= (now - min); i--) {
      lists.push(i)
    }
    return lists
  })

  const updateDataSource = async ({data, isNew}) => {
    await store.dispatch(props.formId + '/UPDATE_DATA_SOURCE', { payload : { data, isNew }})
    await updateSourceCount(counter.value + 1)
  }

  const deleteSourceItem = key => {
    store.dispatch(props.formId + '/DELETE_SOURCE_ITEM', { payload : { key }})
  }

  const addTargetsBasisItem = data => {
    targetsBasisList.value.push({ value: data })
  }

  const updateSourceCount = data => {
    counter.value = data
  }

  const updateSourceItem = async data => {
    const { updateData, updateId } = data
    if(data.type === 'pi') {
      await store.dispatch(props.formId + '/UPDATE_SOURCE_ITEM', { payload: { updateData, updateId }} )
      await updateChildren(data)
    } else {
      const { parentId } = data
      await store.dispatch(props.formId + '/UPDATE_SOURCE_SUB_ITEM', { payload: { updateData, updateId, parentId }} )
    }
  }

  const updateChildren = data => {
    const { updateData, updateId } = data
    const { children } = dataSource.value[updateId]

    if (typeof children !== 'undefined' && children.length) {
      children.forEach(i => {
        if(props.formId === 'opcrvp') {
          i.program = updateData.value.program
        }

        i.subCategory = updateData.value.subCategory
        if(!updateData.value.isHeader) {
          i.targetsBasis = updateData.value.targetsBasis
          if(props.formId === 'aapcr') {
            i.cascadingLevel = updateData.value.cascadingLevel
          }
        }
      })
    }
  }

  const addDeletedItem = id => {
    deletedItems.value.push(id)
  }

  return {
    dataSource,
    targetsBasisList,
    counter,
    deletedItems,
    editMode,
    isFinalized,
    allowEdit,
    year,
    cachedYear,

    years,

    updateDataSource,
    addTargetsBasisItem,
    updateSourceCount,
    deleteSourceItem,
    updateSourceItem,
    addDeletedItem,
  }
}
