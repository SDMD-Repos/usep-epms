import apiClient from '@/services/axios'

export async function getFunctions(year) {
  return apiClient
    .get('/settings/get-all-functions/' + year)
    .then(response => {
      return response.data
    })
    .catch(err => console.log(err))
}

export function createNewFunction(data) {
  return apiClient
    .post('/settings/create-function', data)
    .then(response => {
      return response.data
    })
    .catch(err => console.log(err))
}

export function deleteFunction(id) {
  return apiClient
    .post('/settings/delete-category/' + id)
    .then(response => {
      return response.data
    })
    .catch(err => console.log(err))
}

export async function getPrograms(year) {
  return apiClient
    .get('/settings/get-all-programs/' + year)
    .then(response => {
      return response.data
    })
    .catch(err => console.log(err))
}

export async function addNewProgram(data) {
  return apiClient
    .post('/settings/create-program', data)
    .then(response => {
      return response.data
    })
    .catch(err => console.log(err))
}

export async function deleteProgram(id) {
  return apiClient
    .post('/settings/delete-program/' + id)
    .then(response => {
      return response.data
    })
    .catch(err => console.log(err))
}

export async function getOtherPrograms(year,form_id) {
  return apiClient
    .get('/settings/get-other-programs/' + year + '/' + form_id)
    .then(response => {
      return response.data
    })
    .catch(err => console.log(err))
}

export async function addNewOtherProgram(data) {
  return apiClient
    .post('/settings/create-other-program', data)
    .then(response => {
      return response.data
    })
    .catch(err => console.log(err))
}

export async function deleteOtherProgram(id) {
  return apiClient
    .post('/settings/delete-other-program/' + id)
    .then(response => {
      return response.data
    })
    .catch(err => console.log(err))
}

export async function getSubCategories(year, isPrevious) {
  return apiClient
    .get('/settings/get-sub-categories/' + year + '/'+ isPrevious)
    .then(response => {
      return response.data
    })
    .catch(err => console.log(err))
}

export async function createSubCategory(data) {
  return apiClient
    .post('/settings/create-sub-category', data)
    .then(response => {
      return response.data
    })
    .catch(err => console.log(err))
}


export async function deleteSubCategory(id) {
  return apiClient
    .post('/settings/delete-sub-category/' + id)
    .then(response => {
      return response.data
    })
    .catch(err => console.log(err))
}

export async function getMeasures(year) {
  return apiClient
    .get('/settings/get-all-measures/' + year)
    .then(response => {
      return response.data
    })
    .catch(err => console.log(err))
}

export async function createMeasure(data) {
  return apiClient
    .post('/settings/create-measure', data)
    .then(response => {
      return response.data
    })
    .catch(err => console.log(err))
}

export async function updateMeasure(data, id) {
  return apiClient
    .post('/settings/update-measure/' + id, data)
    .then(response => {
      return response.data
    })
    .catch(err => console.log(err))
}

export async function deleteMeasure(id) {
  return apiClient
    .post('/settings/delete-measure/' + id)
    .then(response => {
      return response.data
    })
    .catch(err => console.log(err))
}

export async function getAllForms() {
  return apiClient
    .get('/settings/get-all-spms-forms')
    .then(response => {
      return response.data
    })
    .catch(err => console.log(err))
}

export async function getAllSignatoryTypes() {
  return apiClient
    .get('/settings/get-all-signatory-types')
    .then(response => {
      return response.data
    })
    .catch(err => console.log(err))
}

export async function getYearSignatories(year, formId, officeId) {
  return apiClient
    .get('/settings/get-year-signatories/' + year + '/' + formId + '/' + officeId)
    .then(response => {
      return response.data
    })
    .catch(err => console.log(err))
}

export async function saveSignatories(data) {
  return apiClient
    .post('/settings/save-signatories', data)
    .then(response => {
      return response.data
    })
    .catch(err => console.log(err))
}

export async function updateSignatory(data) {
  return apiClient
    .post('/settings/update-signatories', data)
    .then(response => {
      return response.data
    })
    .catch(err => console.log(err))
}

export async function deleteSignatory(id) {
  return apiClient
    .post('/settings/delete-signatory/' + id)
    .then(response => {
      return response.data
    })
    .catch(err => console.log(err))
}

export async function fetchAllGroups() {
  return apiClient
    .get('/settings/get-all-groups')
    .then(response => {
      return response.data
    })
    .catch(err => console.log(err))
}

export async function createGroup(data) {
  return apiClient
    .post('/settings/create-group', data)
    .then(response => {
      return response.data
    })
    .catch(err => console.log(err))
}

export async function updateGroup(data, id) {
  return apiClient
    .post('/settings/update-group/' + id, data)
    .then(response => {
      return response.data
    })
    .catch(err => console.log(err))
}

export async function deleteGroup(id) {
  return apiClient
    .post('/settings/delete-group/' + id)
    .then(response => {
      return response.data
    })
    .catch(err => console.log(err))
}

export async function getCascadingLevels() {
  return apiClient
    .get('/settings/get-all-cascading-levels')
    .then(response => {
      return response.data
    })
    .catch(err => console.log(err))
}

export async function getAllFormFields(year) {
  return apiClient
    .get('/settings/get-all-form-fields/' + year)
    .then(response => {
      return response.data
    })
    .catch(err => console.log(err))
}

export async function saveFormFieldSettings(data) {
  return apiClient
    .post('/settings/save-form-field-settings', data)
    .then(response => {
      return response.data
    })
    .catch(err => console.log(err))
}

export async function updateFormFieldSettings(data, id) {
  return apiClient
    .post('/settings/update-form-field-settings/' + id, data)
    .then(response => {
      return response.data
    })
    .catch(err => console.log(err))
}
