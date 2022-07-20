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

      const params = {
        id: payload.id,
        status: payload.status,
        fileName: payload.fileName,
      }

      const updateRequestStatus = mapApiProviders.updateRequestStatus
      updateRequestStatus(params).then(response => {
        if (response) {
          const { callback } = payload

          dispatch(callback.dispatch, callback.payload, { root: true })

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
