import apiClient from '@/services/axios'

export function checkSavedForm(year) {
  return apiClient
    .get('/forms/ocpcr/check-saved/' + year)
    .then(response => {
      return response.data
    })
    .catch(err => console.log(err))
}

export function fetchOpcr() {
  return apiClient
    .get('/forms/ocpcr/list')
    .then(response => {
      return response.data
    })
    .catch(err => console.log(err))
}

export function save(data) {
  return apiClient
    .post('/forms/ocpcr/save-template', data)
    .then(response => {
      return response.data
    })
    .catch(err => console.log(err))
}

export function fetchFormDetails(id) {
  return apiClient
    .get('/forms/ocpcr/view-template/' + id)
    .then(response => {
      return response.data
    })
    .catch(err => console.log(err))
}

export function update(id, data) {
  return apiClient
    .post('/forms/ocpcr/update-template/' + id, data)
    .then(response => {
      return response.data
    })
    .catch(err => console.log(err))
}

export function deactivate(data) {
  return apiClient
    .post('/forms/ocpcr/deactivate-template', data)
    .then(response => {
      return response.data
    })
}

export function publish(data) {
  return apiClient
    .post('/forms/ocpcr/publish-template', data)
    .then(response => {
      return response.data
    })
    .catch(err => console.log(err))
}

export function unpublish(data) {
  return apiClient
    .post('/forms/ocpcr/unpublish-template', data)
    .then(response => {
      return response.data
    })
    .catch(err => console.log(err))
}

export function getRequest(url) {
  return apiClient
    .get(url)
    .then(response => {
      return response.data
    })
    .catch(err => console.log(err))
}

export function postRequest(url, data) {
  return apiClient
    .post(url, data)
    .then(response => {
      return response.data
    })
    .catch(err => console.log(err))
}

export function deleteRequest(url) {
  return apiClient
    .post(url)
    .then(response => {
      return response.data
    })
    .catch(err => console.log(err))
}

export function viewPdfRequest(url) {
  return apiClient
    .get(url, { responseType: 'blob' })
    .then(response => {
      return response.data
    })
    .catch(err => console.log(err))
}


