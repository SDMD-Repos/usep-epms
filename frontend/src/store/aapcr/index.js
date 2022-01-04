import { notification } from 'ant-design-vue'

import * as aapcrForm from '@/services/api/mainForms/aapcr'

const mapApiProviders = {
  save: aapcrForm.save,
  getAapcrList: aapcrForm.fetchAapcrs,
  publish: aapcrForm.publish,
  unpublish: aapcrForm.unpublish,
  deactivate: aapcrForm.deactivate,
  update: aapcrForm.update,
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
    FETCH_LIST({ commit }) {
      commit('SET_STATE', {
        loading: true,
      })
      const getAapcrList = mapApiProviders.getAapcrList
      getAapcrList().then(response => {
        if (response) {
          const { aapcrList } = response
          commit('SET_STATE', {
            list: aapcrList,
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
          dispatch('FETCH_LIST')
          notification.success({
            message: 'Success',
            description: 'AAPCR was created successfully',
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
          notification.success({
            message: 'Success',
            description: 'AAPCR was published successfully',
          })
        }
        commit('SET_STATE', {
          loading: false,
        })
      })
    },
    UNPUBLISH({ commit, dispatch }, { payload }) {
      commit('SET_STATE', {
        loading: true,
      })
      const unpublish = mapApiProviders.unpublish
      unpublish(payload).then(response => {
        if (response) {
          dispatch('FETCH_LIST')
          notification.success({
            message: 'Success',
            description: 'AAPCR was unpublished successfully',
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
          notification.success({
            message: 'Success',
            description: 'AAPCR was deactivated successfully',
          })
        }
        commit('SET_STATE', {
          loading: false,
        })
      })
    },
    UPDATE({ commit, dispatch }, { payload }) {
      commit('SET_STATE', {
        loading: true,
      })
      const id = payload.aapcrId
      const update = mapApiProviders.update
      update(id, payload).then(response => {
        if (response) {
          dispatch('FETCH_LIST')
          notification.success({
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
  getters: {
    form: state => state,
  },
}
