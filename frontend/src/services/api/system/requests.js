import apiClient from '@/services/axios'

export async function getAllUnpublishRequests(status) {
  return apiClient
    .get('/system/requests/get-all-unpublish/' + status)
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

export async function getUnpublishedFormData(id) {
  return apiClient
    .get('/system/requests/view-unpublished-form/' + id, { responseType: 'blob' })
    .then(response => {
      return response.data
    })
    .catch(err => console.log(err))
}
