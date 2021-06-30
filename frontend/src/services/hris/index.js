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

export async function getPersonnelByOffice(id) {
  return apiClient
    .get('/hris/get-personnel-by-office/' + id)
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
