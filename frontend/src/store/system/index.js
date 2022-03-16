import { notification } from 'ant-design-vue'

import * as system from '@/services/api/system/permission'

const mapApiProviders = {
    getAllPermissionList: system.getAllPermissionList,
    savePermissionList: system.savePermissionList,
    updatePermissionList: system.updatePermissionList,
    saveAapcrHead: system.saveAapcrHead,
  }

export default {
    namespaced: true,
    state: {
      loading: false,
      list: [],
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
            const saveAapcrHead = mapApiProviders.saveAapcrHead
            console.log(payload)
            saveAapcrHead(payload).then(response => {
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