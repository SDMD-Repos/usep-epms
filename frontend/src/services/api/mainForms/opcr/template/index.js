import apiClient from '@/services/axios'

export function checkSavedForm(year) {
  return apiClient
    .get('/forms/opcr/check-saved-template/' + year)
    .then(response => {
      return response.data
    })
    .catch(err => console.log(err))
}

export function fetchOpcrTemplates() {
  return apiClient
    .get('/forms/opcr/template-list')
    .then(response => {
      return response.data
    })
    .catch(err => console.log(err))
}

export function save(data) {
  return apiClient
    .post('/forms/opcr/save-template', data)
    .then(response => {
      return response.data
    })
    .catch(err => console.log(err))
}

export function fetchFormDetails(id) {
  return apiClient
    .get('/forms/opcr/view-template/' + id)
    .then(response => {
      return response.data
    })
    .catch(err => console.log(err))
}

export function update(id, data) {
  return apiClient
    .post('/forms/opcr/update-template/' + id, data)
    .then(response => {
      return response.data
    })
    .catch(err => console.log(err))
}

export function deactivate(data) {
  return apiClient
    .post('/forms/opcr/deactivate-template', data)
    .then(response => {
      return response.data
    })
}

export function publish(data) {
  return apiClient
    .post('/forms/opcr/publish-template', data)
    .then(response => {
      return response.data
    })
    .catch(err => console.log(err))
}

