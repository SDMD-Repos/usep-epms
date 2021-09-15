import Vue from 'vue'
import Vuex from 'vuex'

import * as hris from '@/services/hris'

const mapApiProviders = {
  getMainOfficesWithChildren: hris.getMainOfficesWithChildren,
  getMainOfficesOnly: hris.getMainOfficesOnly,
  getPersonnelList: hris.getPersonnelByOffice,
  getAllPositions: hris.getAllPositions,
  getPersonnelOffices: hris.getPersonnelOffices,
  getOfficesAccountable: hris.getOfficesAccountable,
}

Vue.use(Vuex)

export default {
  namespaced: true,
  state: {
    vpOffices: [],
    mainOffices: [],
    mainOfficesChildren: [],
    personnel: [],
    positionList: [],
    personnelOffices: [],
    officesAccountable: [],
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
    FETCH_VP_OFFICES({ commit }, { payload }) {
      commit('SET_STATE', {
        loading: true,
      })
      const { officesOnly } = payload
      const getMainOfficesOnly = mapApiProviders.getMainOfficesOnly
      getMainOfficesOnly(officesOnly).then(response => {
        if (response) {
          const { mainOffices } = response
          commit('SET_STATE', {
            vpOffices: mainOffices,
          })
        }
        commit('SET_STATE', {
          loading: false,
        })
      })
    },
    FETCH_MAIN_OFFICES_CHILDREN({ commit }, { payload }) {
      commit('SET_STATE', {
        loading: true,
      })
      const getMainOfficesWithChildren = mapApiProviders.getMainOfficesWithChildren
      getMainOfficesWithChildren(payload).then(response => {
        if (response) {
          const { mainOffices } = response
          commit('SET_STATE', {
            mainOfficesChildren: mainOffices,
          })
        }
        commit('SET_STATE', {
          loading: false,
        })
      })
    },
    FETCH_MAIN_OFFICES_ONLY({ commit }) {
      commit('SET_STATE', {
        loading: true,
      })
      const getMainOfficesOnly = mapApiProviders.getMainOfficesOnly
      getMainOfficesOnly(0).then(response => {
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
    FETCH_OFFICES_BY_PERSONNEL({ commit }, { payload }) {
      const { formId } = payload
      commit('SET_STATE', {
        loading: true,
      })

      const getPersonnelOffices = mapApiProviders.getPersonnelOffices
      getPersonnelOffices(formId).then(response => {
        if (response) {
          const { personnelOffices } = response
          commit('SET_STATE', {
            personnelOffices: personnelOffices,
          })
        }
        commit('SET_STATE', {
          loading: false,
        })
      })
    },
    FETCH_ALL_POSITIONS({ commit }) {
      commit('SET_STATE', {
        loading: true,
      })

      const getAllPositions = mapApiProviders.getAllPositions
      getAllPositions().then(response => {
        if (response) {
          const { positionList } = response
          commit('SET_STATE', {
            positionList: positionList,
          })
        }
        commit('SET_STATE', {
          loading: false,
        })
      })
    },
    FETCH_OFFICES_ACCOUNTABLE({ commit }, { payload }) {
      commit('SET_STATE', {
        loading: true,
      })

      const getOfficesAccountable = mapApiProviders.getOfficesAccountable
      getOfficesAccountable(payload).then(response => {
        if (response) {
          const { officesAccountable } = response
          commit('SET_STATE', {
            officesAccountable: officesAccountable,
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
