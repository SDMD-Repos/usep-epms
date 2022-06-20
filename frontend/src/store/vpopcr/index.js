import { notification } from 'ant-design-vue'

import * as vpOpcrForm from '@/services/api/mainForms/vpopcr'
import * as system from '@/services/api/system/permission'

const mapApiProviders = {
  save: vpOpcrForm.save,
  getList: vpOpcrForm.fetchVpOpcrs,
  publish: vpOpcrForm.publish,
  unpublish: vpOpcrForm.unpublish,
  deactivate: vpOpcrForm.deactivate,
  update: vpOpcrForm.update,
  permission: system.checkAllowForm,
}

export default {
  namespaced: true,
  state: {
    loading: false,
    list: [],
    dataSource: [],
    hasVpopcrAccess: false,
    accessOfficeId : 0,
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
            description: 'The request to unpublish the form has been sent successfully',
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
        details: payload.updateData,
        index: payload.updateId,
      })
    },
    UPDATE_SOURCE_SUB_ITEM({ commit }, { payload }) {
      commit('UPDATE_STATE_SUB_ITEM', {
        type: 'dataSource',
        details: payload.updateData,
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
    CHECK_VPOPCR_PERMISSION({ commit }, { payload }) {
      const { pmaps_id,form_id } = payload
      commit('SET_STATE', {
        loading: true,
      })
      const permission = mapApiProviders.permission
      permission(pmaps_id,form_id).then(response => {
        if (response) {
          const { permission,office_id } = response

          commit('SET_STATE', {
            hasVpopcrAccess: permission,
            accessOfficeId : office_id,
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
