import Vue from 'vue'
import Vuex from 'vuex'

// import * as ocprForm from '@/services/mainForms/opcr'

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
}
