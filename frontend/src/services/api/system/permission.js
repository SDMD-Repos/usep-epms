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

  export function saveOfficeHead(data) {
    return apiClient
      .post('/system/save-office-head',data)
      .then(response => {
        return response.data
      })
      .catch(err => console.log(err))
  }

  export function fetchOfficeHead(form_id,pmaps_id) {
    return apiClient
      .get('/system/fetch-office-head/'+form_id+"/"+pmaps_id)
      .then(response => {
        return response.data
      })
      .catch(err => console.log(err))
  }

  export function saveOfficeStaff(data) {
    return apiClient
      .post('/system/save-office-staff',data)
      .then(response => {
        return response.data
      })
      .catch(err => console.log(err))
  }

  export function checkAccessPermission(data) {
    return apiClient
      .post('/system/check-access',data)
      .then(response => {
        return response.data
      })
      .catch(err => console.log(err))
  }

  export function checkFormHeadPermission(pmaps_id,form_id) {
    return apiClient
      .get('/system/check-form-head/'+pmaps_id+'/'+form_id)
      .then(response => {
        return response.data
      })
      .catch(err => console.log(err))
  }

  export function checkAllowAapcrForm(pmaps_id,form_id) {
    return apiClient
      .get('/system/allow-aapcr-form/'+pmaps_id+'/'+form_id)
      .then(response => {
        return response.data
      })
      .catch(err => console.log(err))
  }



  