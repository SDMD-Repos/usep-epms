import apiClient from '@/services/axios'

export function getVpOpcrDetailsByOffice(payload) {
  const { officeId, year, formId } = payload
  return apiClient
    .get('/forms/ocpcr/get-vp-opcr-details/' + officeId + '/' + year + '/' + formId)
    .then(response => {
      return response.data
    })
    .catch(err => console.log(err))
}
