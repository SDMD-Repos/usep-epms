import apiClient from '@/services/axios'

export function checkSavedForm(year) {
  return apiClient
    .get('/forms/aapcr/check-saved/' + year)
    .then(response => {
      return response.data
    })
    .catch(err => console.log(err))
}

export function fetchAapcrs() {
  return apiClient
    .get('/forms/aapcr/list')
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

export function publish(data) {
  return apiClient
    .post('/forms/aapcr/publish', data)
    .then(response => {
      return response.data
    })
    .catch(err => console.log(err))
}

export function deactivate(data) {
  return apiClient
    .post('/forms/aapcr/deactivate', data)
    .then(response => {
      return response.data
    })
    .catch(err => console.log(err))
}

export function fetchPdfData(id, documentName) {
  return apiClient
    .get('/forms/aapcr/viewPdf/' + id + '/' + documentName, { responseType: 'blob' })
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
