import Vue from 'vue'
import Vuex from 'vuex'

import * as aapcrForm from '@/services/mainForms/aapcr'

const mapApiProviders = {
  save: aapcrForm.save,
  getAapcrList: aapcrForm.fetchAapcrs,
  publish: aapcrForm.publish,
  deactivate: aapcrForm.deactivate,
  update: aapcrForm.update,
}

Vue.use(Vuex)

export default {
  namespaced: true,
  state: {
    loading: false,
    aapcrList: [],
  },
  mutations: {
    SET_STATE(state, payload) {
      Object.assign(state, {
        ...payload,
      })
    },
  },
  actions: {
    FETCH_AAPCRS({ commit }) {
      commit('SET_STATE', {
        loading: true,
      })
      const getAapcrList = mapApiProviders.getAapcrList
      getAapcrList().then(response => {
        if (response) {
          const { aapcrList } = response
          commit('SET_STATE', {
            aapcrList: aapcrList,
          })
        }
        commit('SET_STATE', {
          loading: false,
        })
      })
    },
    SAVE_AAPCR({ commit, dispatch }, { payload }) {
      commit('SET_STATE', {
        loading: true,
      })

      const save = mapApiProviders.save
      save(payload).then(response => {
        if (response) {
          dispatch('FETCH_AAPCRS')
          Vue.prototype.$notification.success({
            message: 'Success',
            description: 'AAPCR was created successfully',
          })
        }
        commit('SET_STATE', {
          loading: false,
        })
      })
    },
    PUBLISH_AAPCR({ commit, dispatch }, { payload }) {
      commit('SET_STATE', {
        loading: true,
      })
      const publish = mapApiProviders.publish
      publish(payload).then(response => {
        if (response) {
          dispatch('FETCH_AAPCRS')
          Vue.prototype.$notification.success({
            message: 'Success',
            description: 'AAPCR was published successfully',
          })
        }
        commit('SET_STATE', {
          loading: false,
        })
      })
    },
    DEACTIVATE_AAPCR({ commit, dispatch }, { payload }) {
      commit('SET_STATE', {
        loading: true,
      })
      const deactivate = mapApiProviders.deactivate
      deactivate(payload).then(response => {
        if (response) {
          dispatch('FETCH_AAPCRS')
          Vue.prototype.$notification.success({
            message: 'Success',
            description: 'AAPCR was deactivated successfully',
          })
        }
        commit('SET_STATE', {
          loading: false,
        })
      })
    },
    UPDATE_AAPCR({ commit, dispatch }, { payload }) {
      commit('SET_STATE', {
        loading: true,
      })
      const id = payload.aapcrId
      const update = mapApiProviders.update
      update(id, payload).then(response => {
        if (response) {
          dispatch('FETCH_AAPCRS')
          Vue.prototype.$notification.success({
            message: 'Success',
            description: 'AAPCR was updated successfully',
          })
        }
        commit('SET_STATE', {
          loading: false,
        })
      })
    },
  },
}
