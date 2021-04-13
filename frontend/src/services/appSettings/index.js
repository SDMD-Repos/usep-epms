import apiClient from '@/services/axios'

export async function getFunctions() {
  return apiClient
    .get('/settings/get-all-functions')
    .then(response => {
      return response.data
    })
    .catch(err => console.log(err))
}

export async function getPrograms() {
  return apiClient
    .get('/settings/get-all-programs')
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

export async function getSubCategories() {
  return apiClient
    .get('/settings/get-sub-categories')
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

export async function getMeasures() {
  return apiClient
    .get('/settings/get-all-measures')
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
