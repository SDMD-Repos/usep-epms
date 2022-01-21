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

  const openDrawer = action => {
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

export const useDefaultFormData = formId => {
  const defaultData = ref({})
  switch (formId) {
    case 'aapcr':
      defaultData.value = defaultAapcrFormData
  }
  const defaultFormData = () => (defaultData.value)

  const formData = reactive(defaultFormData())

  const resetFormData = () => {
    Object.assign(formData, defaultFormData())
  }

  const resetFormAsHeader = () => {
    switch (formId) {
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

    resetFormData,
    resetFormAsHeader,
  }
}
