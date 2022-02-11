import { notification } from 'ant-design-vue'

import * as opcrvpForm from '@/services/api/mainForms/opcrvp'

const mapApiProviders = {
  save: opcrvpForm.save,
  getList: opcrvpForm.fetchVpOpcrs,
  publish: opcrvpForm.publish,
  unpublish: opcrvpForm.unpublish,
  deactivate: opcrvpForm.deactivate,
  update: opcrvpForm.update,
}

export default {
  namespaced: true,
  state: {
    loading: false,
    list: [],
    dataSource: [],
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
          notification.success({
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
          notification.success({
            message: 'Success',
            description: 'VP\'s OPCR was published successfully',
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
            description: 'VP\'s OPCR was unpublished successfully',
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
            description: 'VP\'s OPCR was deactivated successfully',
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
      const id = payload.vpOpcrId
      const update = mapApiProviders.update
      update(id, payload).then(response => {
        if (response) {
          dispatch('FETCH_LIST')
          notification.success({
            message: 'Success',
            description: 'VP\'s OPCR was updated successfully',
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
