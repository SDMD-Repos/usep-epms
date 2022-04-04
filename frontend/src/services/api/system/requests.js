import apiClient from '@/services/axios'

export async function getAllUnpublishRequests() {
  return apiClient
    .get('/system/requests/get-all-unpublish')
    .then(response => {
      return response.data
    })
    .catch(err => console.log(err))
}

export function updateRequestStatus(data) {
  return apiClient
    .post('/system/requests/update-request-status', data)
    .then(response => {
      return response.data
    })
    .catch(err => console.log(err))
}

export async function viewUnpublishedForm(id) {
  return apiClient
    .get('/system/requests/view-unpublished-form/' + id, { responseType: 'blob' })
    .then(response => {
      return response.data
    })
    .catch(err => console.log(err))
}
