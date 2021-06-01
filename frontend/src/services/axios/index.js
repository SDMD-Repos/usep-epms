import axios from 'axios'
import store from 'store'
import { notification } from 'ant-design-vue'

const apiClient = axios.create({
  baseURL: 'http://127.0.0.1:8000/api',
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
    if (typeof data.message !== 'undefined') {
      data = data.message
    }
    notification.warning({
      message: data,
    })
  }
})

export default apiClient
