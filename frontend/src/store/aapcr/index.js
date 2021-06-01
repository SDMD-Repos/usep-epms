import Vue from 'vue'
import Vuex from 'vuex'

import * as aapcrForm from '@/services/mainForms/aapcr'

const mapApiProviders = {
  save: aapcrForm.save,
}

Vue.use(Vuex)

export default {
  namespaced: true,
  state: {
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
    SAVE_AAPCR({ commit, dispatch }, { payload }) {
      commit('SET_STATE', {
        loading: true,
      })

      const save = mapApiProviders.save
      save(payload).then(response => {
        if (response) {
          // dispatch('FETCH_FUNCTIONS')
          Vue.prototype.$notification.success({
            message: 'Success',
            description: 'AAPCR created successfully',
          })
        }
        commit('SET_STATE', {
          loading: false,
        })
      })
    },
  },
}
