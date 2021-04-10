import apiClient from '@/services/axios'

export async function getFunctions() {
  return apiClient
    .get()
}
