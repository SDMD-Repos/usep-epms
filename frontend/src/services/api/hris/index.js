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
    .get('/hris/get-main-offices-children/' + data)
    .then(response => {
      return response.data
    })
    .catch(err => console.log(err))
}

export async function getVpOfficeWithChildren() {
  return apiClient
    .get('/hris/get-vp-offices-children')
    .then(response => {
      return response.data
    })
    .catch(err => console.log(err))
}

export async function getPersonnelByOffice(id) {
  return apiClient
    .get('/hris/get-personnel-by-office/' + id)
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
    .get('/hris/get-offices-accountable/' + data)
    .then(response => {
      return response.data
    })
    .catch(err => console.log(err))
}
