import apiClient from '@/services/axios'

export function checkSavedForm(year) {
  return apiClient
    .get('/forms/ocpcr/check-saved-template/' + year)
    .then(response => {
      return response.data
    })
    .catch(err => console.log(err))
}

export function fetchOpcrTemplate() {
  return apiClient
    .get('/forms/ocpcr/template-list')
    .then(response => {
      return response.data
    })
    .catch(err => console.log(err))
}

export function save(data) {
  return apiClient
    .post('/forms/aapcr/save', data)
    .then(response => {
      return response.data
    })
    .catch(err => console.log(err))
}

export function fetchFormDetails(id) {
  return apiClient
    .get('/forms/aapcr/view/' + id)
    .then(response => {
      return response.data
    })
    .catch(err => console.log(err))
}

export function update(id, data) {
  return apiClient
    .post('/forms/aapcr/update/' + id, data)
    .then(response => {
      return response.data
    })
    .catch(err => console.log(err))
}
