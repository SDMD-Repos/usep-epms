import Vue from 'vue'
import Vuex from 'vuex'

import * as hris from '@/services/hris'

const mapApiProviders = {
  getMainOffices: hris.getMainOffices,
  getPersonnelList: hris.getPersonnelByOffice,
}

Vue.use(Vuex)

export default {
  namespaced: true,
  state: {
    mainOffices: [],
    personnel: [],
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
    FETCH_MAIN_OFFICES({ commit }) {
      commit('SET_STATE', {
        loading: true,
      })

      const getMainOffices = mapApiProviders.getMainOffices
      getMainOffices().then(response => {
        if (response) {
          const { mainOffices } = response
          commit('SET_STATE', {
            mainOffices: mainOffices,
          })
        }
        commit('SET_STATE', {
          loading: false,
        })
      })
    },
    FETCH_PERSONNEL_BY_OFFICE({ commit }, { payload }) {
      const id = payload
      commit('SET_STATE', {
        loading: true,
      })

      const getPersonnelList = mapApiProviders.getPersonnelList
      getPersonnelList(id).then(response => {
        if (response) {
          const { personnel } = response
          commit('SET_STATE', {
            personnel: personnel,
          })
        }
        commit('SET_STATE', {
          loading: false,
        })
      })
    },
  },
  getters: {
    officeList: state => state.mainOffices,
  },
}
