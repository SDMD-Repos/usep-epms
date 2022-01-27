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