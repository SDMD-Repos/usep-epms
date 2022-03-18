import { notification } from 'ant-design-vue'

import * as system from '@/services/api/system/permission'

const mapApiProviders = {
    getAllPermissionList: system.getAllPermissionList,
    savePermissionList: system.savePermissionList,
    updatePermissionList: system.updatePermissionList,
    saveOfficeHead: system.saveOfficeHead,
    fetchOfficeHead: system.fetchOfficeHead,
    saveOfficeStaff: system.saveOfficeStaff,
  }

export default {
    namespaced: true,
    state: {
      loading: false,
      list: [],
      officeHeadDetails: [],
    },
    mutations: {
      SET_STATE(state, payload) {
        Object.assign(state, {
          ...payload,
        })
      },
    },
    actions: {
        FETCH_PERMISSION({ commit }) {
            commit('SET_STATE', {
              loading: true,
            })
            const getAccessPermission = mapApiProviders.getAllPermissionList
            getAccessPermission().then(response => {
              if (response) {
                const { accessPermission } = response
                commit('SET_STATE', {
                  list: accessPermission,
                })
              }
              commit('SET_STATE', {
                loading: false,
              })
            })
          },
          SAVE_PERMISSION({ commit }, { payload }) {
            commit('SET_STATE', {
              loading: true,
            })
            const savePermissionList = mapApiProviders.savePermissionList
            savePermissionList(payload).then(response => {
              if (response) {
                  notification.success({
                  message: 'Success',
                  description: 'Access Rights was saved successfully',
                })
              }
              commit('SET_STATE', {
                loading: false,
              })
            })
          },
          UPDATE_PERMISSION({ commit }, { payload }) {
            commit('SET_STATE', {
              loading: true,
            })
            const updatePermissionList = mapApiProviders.updatePermissionList
            updatePermissionList(payload).then(response => {
              if (response) {
                  notification.success({
                  message: 'Success',
                  description: 'Access Rights was saved successfully',
                })
              }
              commit('SET_STATE', {
                loading: false,
              })
            })
          },
         SAVE_AAPCR_HEAD({ commit }, { payload }) {
            commit('SET_STATE', {
              loading: true,
            })
            const saveOfficeHead = mapApiProviders.saveOfficeHead
            saveOfficeHead(payload).then(response => {
              if (response) {
                  notification.success({
                  message: 'Success',
                  description: 'AAPCR Head has been assigned.',
                })
              }
              commit('SET_STATE', {
                loading: false,
              })
            })
        },
        FEATCH_AAPCR_HEAD({ commit }, { payload }) {
          commit('SET_STATE', {
            loading: true,
          })
          const fetchOfficeHead = mapApiProviders.fetchOfficeHead
          fetchOfficeHead(payload.form_id).then(response => {
            if (response) {
              commit('SET_STATE', {
                officeHeadDetails : {
                            pmaps_id: response.officeHeadDetails.pmaps_id,
                            pmaps_name: response.officeHeadDetails.pmaps_name,
                            office_id: response.officeHeadDetails.office_id,
                            office_name: response.officeHeadDetails.office_name,
                            staff_id: response.officeHeadDetails.staff_id,
                            staff_name: response.officeHeadDetails.staff_name,
                },
              })
            }
            commit('SET_STATE', {
              loading: false,
            })
          })
      },
      SAVE_AAPCR_STAFF({ commit }, { payload }) {
        commit('SET_STATE', {
          loading: true,
        })
        const saveOfficeStaff = mapApiProviders.saveOfficeStaff
        saveOfficeStaff(payload).then(response => {
          if (response) {
              notification.success({
              message: 'Success',
              description: 'AAPCR Head has been assigned.',
            })
          }
          commit('SET_STATE', {
            loading: false,
          })
        })
    },
    },
    getters: {
      permission: state => state,
    },

}