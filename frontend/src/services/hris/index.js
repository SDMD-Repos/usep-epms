import apiClient from '@/services/axios'

export async function getMainOffices(data) {
  return apiClient
    .get('/hris/get-main-offices/' + data)
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
