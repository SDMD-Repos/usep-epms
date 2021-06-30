import apiClient from '@/services/axios'

export function getAapcrDetailsByOffice(vpId, year) {
  return apiClient
    .get('/forms/opcrvp/get-aapcr-details/' + vpId + '/' + year)
    .then(response => {
      return response.data
    })
    .catch(err => console.log(err))
}

export function save(data) {
  return apiClient
    .post('/forms/opcrvp/save', data)
    .then(response => {
      return response.data
    })
    .catch(err => console.log(err))
}

export function fetchVpOpcrs() {
  return apiClient
    .get('/forms/opcrvp/list')
    .then(response => {
      return response.data
    })
    .catch(err => console.log(err))
}

export function publish(data) {
  return apiClient
    .post('/forms/opcrvp/publish', data)
    .then(response => {
      return response.data
    })
    .catch(err => console.log(err))
}
