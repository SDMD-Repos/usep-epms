import apiClient from '@/services/axios'

export function getAapcrDetailsByOffice(vpId, year) {
  return apiClient
    .get('/forms/opcrvp/get-aapcr-details/' + vpId + '/' + year)
    .then(response => {
      return response.data
    })
    .catch(err => console.log(err))
}

export function checkSaved(officeId, year) {
  return apiClient
    .get('/forms/opcrvp/check-saved/' + officeId + '/' + year)
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

export function unpublish(data) {
  return apiClient
    .post('/forms/opcrvp/unpublish', data)
    .then(response => {
      return response.data
    })
    .catch(err => console.log(err))
}

export function deactivate(data) {
  return apiClient
    .post('/forms/opcrvp/deactivate', data)
    .then(response => {
      return response.data
    })
    .catch(err => console.log(err))
}

export function fetchFormDetails(id) {
  return apiClient
    .get('/forms/opcrvp/view/' + id)
    .then(response => {
      return response.data
    })
    .catch(err => console.log(err))
}

export function update(id, data) {
  return apiClient
    .post('/forms/opcrvp/update/' + id, data)
    .then(response => {
      return response.data
    })
    .catch(err => console.log(err))
}

export function renderPdf(id) {
  return apiClient
    .get('/forms/opcrvp/viewPdf/' + id, { responseType: 'blob' })
    .then(response => {
      return response.data
    })
    .catch(err => console.log(err))
}

/*export function viewUploadedFile(id) {
  return apiClient
    .get('/forms/opcrvp/viewUploadedFile/' + id, { responseType: 'blob' })
    .then(response => {
      return response.data
    })
    .catch(err => console.log(err))
}

export function updateFile(data) {
  return apiClient
    .post('/forms/opcrvp/update-file', data)
    .then(response => {
      return response.data
    })
    .catch(err => console.log(err))
}*/

export function checkSavedIndicators(data) {
  return apiClient
    .post('/forms/opcrvp/check-saved-indicators', data)
    .then(response => {
      return response.data
    })
    .catch(err => console.log(err))
}
