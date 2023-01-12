import { notification } from 'ant-design-vue'

import * as opcrTemplateForm from '@/services/api/mainForms/ocpcr/template'
import { getRequest, postRequest } from '@/services/api/mainForms/ocpcr'

const mapApiProviders = {
  save: opcrTemplateForm.save,
  publish: opcrTemplateForm.publish,
  unpublish: opcrTemplateForm.unpublish,
  deactivate: opcrTemplateForm.deactivate,
  update: opcrTemplateForm.update,
}

const baseUrl = '/forms/ocpcr/template'

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
    ADD_STATE_ITEM(state, payload) {
      const { type, details } = payload
      state[type].push(details)

      state[type] = [...state[type]]
    },
    UPDATE_STATE_ITEM(state, payload) {
      const { type, details, index } = payload

      Object.assign(state[type][index], details)

      state[type] = [...state[type]]
    },
    UPDATE_STATE_SUB_ITEM(state, payload) {
      const { type, details, index, parent } = payload

      const parentIndex = state[type].findIndex(i => i.key === parent)
      const { children } = state[type][parentIndex]
      Object.assign(children[index], details)
    },
    DELETE_STATE_ITEM(state, payload) {
      const { type, key } = payload
      state[type].splice(key, 1)
    },
  },
  actions: {
    FETCH_LIST({ commit }) {
      commit('SET_STATE', {
        loading: true,
      })
      getRequest(baseUrl + '/list').then(response => {
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

      postRequest(baseUrl + '/save', payload).then(response => {
        if (response) {
          // dispatch('FETCH_LIST')
          notification.success({
            message: 'Success',
            description: 'OPCR Template was created successfully',
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
      const id = payload.opcrTemplateId
      postRequest(baseUrl + '/update/' + id, payload).then(response => {
        if (response) {
          dispatch('FETCH_LIST')
          notification.success({
            message: 'Success',
            description: 'OPCR Template was updated successfully',
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

      postRequest(baseUrl + '/publish', payload).then(response => {
        if (response) {
          dispatch('FETCH_LIST')
          notification.success({
            message: 'Success',
            description: 'OPCR Template was published successfully',
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

      postRequest(baseUrl + "/unpublish", payload).then(response => {
        if (response) {
          dispatch('FETCH_LIST')
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
            description: 'OPCR Template was deactivated successfully',
          })
        }
        commit('SET_STATE', {
          loading: false,
        })
      })
    },

    UPDATE_DATA_SOURCE({ commit }, { payload }) {
      const { isNew, data } = payload
      if(isNew) {
        commit('ADD_STATE_ITEM', {
          type: 'dataSource',
          details: data,
        })
      }else {
        commit('SET_STATE', {
          dataSource: data,
        })
      }
    },
    UPDATE_SOURCE_ITEM({ commit }, { payload }) {
      commit('UPDATE_STATE_ITEM', {
        type: 'dataSource',
        details: payload.updateData.value,
        index: payload.updateId,
      })
    },
    UPDATE_SOURCE_SUB_ITEM({ commit }, { payload }) {
      commit('UPDATE_STATE_SUB_ITEM', {
        type: 'dataSource',
        details: payload.updateData.value,
        index: payload.updateId,
        parent: payload.parentId,
      })
    },
    DELETE_SOURCE_ITEM({ commit }, { payload }) {
      commit('DELETE_STATE_ITEM', {
        type: 'dataSource',
        key: payload.key,
      })
    },
  },
  getters: {
    form: state => state,
  },
}
