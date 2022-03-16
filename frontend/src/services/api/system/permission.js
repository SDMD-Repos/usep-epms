import apiClient from '@/services/axios'

export function getAllPermissionList(year) {
    return apiClient
      .get('/system/permission')
      .then(response => {
        return response.data
      })
      .catch(err => console.log(err))
  }

  export function savePermissionList(data) {
    return apiClient
      .post('/system/save-permission',data)
      .then(response => {
        return response.data
      })
      .catch(err => console.log(err))
  }

  export async function getAccessByUser(id) {
    return apiClient
      .get('/system/get-permission-by-user/' + id)
      .then(response => {
        return response.data
      })
      .catch(err => console.log(err))
  }

  export function updatePermissionList(data) {
    return apiClient
      .post('/system/update-permission',data)
      .then(response => {
        return response.data
      })
      .catch(err => console.log(err))
  }

  export function saveAapcrHead(data) {
    return apiClient
      .post('/system/save-aapcr-head',data)
      .then(response => {
        return response.data
      })
      .catch(err => console.log(err))
  }