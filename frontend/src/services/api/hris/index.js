import apiClient from '@/services/axios'

export async function getMainOfficesOnly(data) {
  return apiClient
    .get('/hris/get-main-offices-only/' + data)
    .then(response => {
      return response.data
    })
    .catch(err => console.log(err))
}

export async function getMainOfficesWithChildren(data) {
  return apiClient
    .post('/hris/get-main-offices-children', data)
    .then(response => {
      return response.data
    })
    .catch(err => console.log(err))
}

export async function getPersonnelByOffice(id, permanentOnly=0, isSubunit=0) {
  return apiClient
    .get('/hris/get-personnel-by-office/' + id + '/' + permanentOnly + '/' + isSubunit)
    .then(response => {
      return response.data
    })
    .catch(err => console.log(err))
}

export async function getFormAccessByOffice(id) {
  return apiClient
    .get('/hris/get-form-access-by-office/' + id)
    .then(response => {
      return response.data
    })
    .catch(err => console.log(err))
}


export async function getPersonnelOffices(formId) {
  return apiClient
    .get('/hris/get-user-offices/' + formId)
    .then(response => {
      return response.data
    })
    .catch(err => console.log(err))
}

export async function getAllPositions() {
  return apiClient
    .get('/hris/get-all-positions')
    .then(response => {
      return response.data
    })
    .catch(err => console.log(err))
}

export async function getOfficesAccountable(data) {
  return apiClient
    .post('/hris/get-offices-accountable',  data)
    .then(response => {
      return response.data
    })
    .catch(err => console.log(err))
}
