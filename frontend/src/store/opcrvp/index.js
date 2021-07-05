import Vue from 'vue'
import Vuex from 'vuex'

import * as opcrvpForm from '@/services/mainForms/opcrvp'

const mapApiProviders = {
  save: opcrvpForm.save,
  getList: opcrvpForm.fetchVpOpcrs,
  publish: opcrvpForm.publish,
  deactivate: opcrvpForm.deactivate,
}

Vue.use(Vuex)

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
    FETCH_LIST({ commit }) {
      commit('SET_STATE', {
        loading: true,
      })
      const getList = mapApiProviders.getList
      getList().then(response => {
        if (response) {
          const { list } = response
          commit('SET_STATE', {
            list: list,
          })
        }
        commit('SET_STATE', {
          loading: false,
        })
      })
    },
    SAVE({ commit, dispatch }, { payload }) {
      commit('SET_STATE', {
        loading: true,
      })

      const save = mapApiProviders.save
      save(payload).then(response => {
        if (response) {
          // dispatch('FETCH_LIST')
          Vue.prototype.$notification.success({
            message: 'Success',
            description: 'VP\'s OPCR was created successfully',
          })
        }
        commit('SET_STATE', {
          loading: false,
        })
      })
    },
    PUBLISH({ commit, dispatch }, { payload }) {
      commit('SET_STATE', {
        loading: true,
      })
      const publish = mapApiProviders.publish
      publish(payload).then(response => {
        if (response) {
          dispatch('FETCH_LIST')
          Vue.prototype.$notification.success({
            message: 'Success',
            description: 'VP\'s OPCR was published successfully',
          })
        }
        commit('SET_STATE', {
          loading: false,
        })
      })
    },
    DEACTIVATE({ commit, dispatch }, { payload }) {
      commit('SET_STATE', {
        loading: true,
      })
      const deactivate = mapApiProviders.deactivate
      deactivate(payload).then(response => {
        if (response) {
          dispatch('FETCH_LIST')
          Vue.prototype.$notification.success({
            message: 'Success',
            description: 'VP\'s OPCR was deactivated successfully',
          })
        }
        commit('SET_STATE', {
          loading: false,
        })
      })
    },
  },
}
