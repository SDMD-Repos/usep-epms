import { notification } from 'ant-design-vue'

import * as system from '@/services/api/system/requests'

const mapApiProviders = {
  getAllUnpublishRequests: system.getAllUnpublishRequests,
  updateRequestStatus: system.updateRequestStatus,
}

export default {
  namespaced: true,
  state: {
    loading: false,
    unpublishList: [],
  },
  mutations: {
    SET_STATE(state, payload) {
      Object.assign(state, {
        ...payload,
      })
    },
  },
  actions: {
    FETCH_UNPUBLISH_LIST({ commit }, { payload }) {
      const { status } = payload
      commit('SET_STATE', {
        loading: true,
      })
      const allUnpublishRequests = mapApiProviders.getAllUnpublishRequests
      allUnpublishRequests(status).then(response => {
        if (response) {
          const { unpublishList } = response
          commit('SET_STATE', {
            unpublishList: unpublishList,
          })
        }
        commit('SET_STATE', {
          loading: false,
        })
      })
    },
    UPDATE_REQUEST_STATUS({ commit, dispatch }, { payload }) {
      commit('SET_STATE', {
        loading: true,
      })
      const updateRequestStatus = mapApiProviders.updateRequestStatus
      updateRequestStatus(payload).then(response => {
        if (response) {
          dispatch('FETCH_UNPUBLISH_LIST', { payload: { status: 'pending' }})
          notification.success({
            message: 'Success',
            description: response,
          })
        }
        commit('SET_STATE', {
          loading: false,
        })
      })
    },
  },
  getters: {
    unpublish: state => state.unpublishList,
    stateRequests: state => state,
  },
}
