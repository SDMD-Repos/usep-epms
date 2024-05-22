import axios from 'axios'
import store from 'store'
import { notification } from 'ant-design-vue'

// Set the default base URL for Axios
const apiClient = axios.create({
  baseURL: process.env.VUE_APP_BACKEND_URL + '/api',
  // timeout: 1000,
  // headers: { 'X-Custom-Header': 'foobar' }
})

apiClient.interceptors.request.use(request => {
  const accessToken = store.get('accessToken')
  if (accessToken) {
    request.headers.Authorization = `Bearer ${accessToken}`
    request.headers.AccessToken = accessToken
  }
  return request
})

apiClient.interceptors.response.use(undefined, error => {
  // Errors handling
  const { response } = error
  let { data } = response

  if (data && response.status !== 401) {
    // Errors from backend form validation
    if (typeof data.errors !== 'undefined') {
      Object.keys(data.errors).forEach(key => {
        data = data.errors[key]
      })
    } else if (typeof data.message !== 'undefined') {
      data = data.message
    }
    notification.warning({
      message: data,
    })
  }
})

export default apiClient
