import apiClient from '@/services/axios'

export function save(data) {
  return apiClient
    .post('/forms/aapcr/save', data)
    .then(response => {
      return response.data
    })
    .catch(err => console.log(err))
}
