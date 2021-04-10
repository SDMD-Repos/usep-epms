import Vue from 'vue'
import Vuex from 'vuex'
// import store from 'store'

import * as appSettings from '@/services/appSettings'

const mapApiProviders = {
  getFunctions: appSettings.getFunctions,
}

Vue.use(Vuex)

export default {
  namespaced: true,
  state: {
    functions: [],
    loading: false,
  },
  mutations: {
    SET_STATE(state, payload) {
      Object.assign(state, {
        ...payload,
      })
    },
  },
  actions: {
    FETCH_FUNCTIONS({ commit, rootState }) {
      commit('SET_STATE', {
        loading: true,
      })

      const getFunctions = mapApiProviders.getFunctions
      getFunctions().then(response => {
        if (response) {
          console.log(response)
        }
        commit('SET_STATE', {
          loading: false,
        })
      })
    },
  },
  getters: {
    functions: state => state.functions,
  },
}
