import Vue from 'vue'
import Vuex from 'vuex'

import * as appSettings from '@/services/appSettings'

const mapApiProviders = {
  getFunctions: appSettings.getFunctions,
  createFunction: appSettings.createNewFunction,
  deleteFunction: appSettings.deleteFunction,
  getPrograms: appSettings.getPrograms,
  createProgram: appSettings.addNewProgram,
  deleteProgram: appSettings.deleteProgram,
  getSubCategories: appSettings.getSubCategories,
  createSubCategory: appSettings.createSubCategory,
  deleteSubCategory: appSettings.deleteSubCategory,
  getMeasures: appSettings.getMeasures,
  createMeasure: appSettings.createMeasure,
  updateMeasure: appSettings.updateMeasure,
  deleteMeasure: appSettings.deleteMeasure,
  getAllForms: appSettings.getAllForms,
  getAllPositions: appSettings.getAllPositions,
  getYearSignatories: appSettings.getYearSignatories,
  saveSignatories: appSettings.saveSignatories,
  updateSignatories: appSettings.updateSignatory,
  deleteSignatory: appSettings.deleteSignatory,
}

Vue.use(Vuex)

export default {
  namespaced: true,
  state: {
    functions: [],
    programs: [],
    subCategories: [],
    measures: [],
    forms: [],
    positions: [],
    signatories: [],
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
    FETCH_FUNCTIONS({ commit }) {
      commit('SET_STATE', {
        loading: true,
      })

      const getFunctions = mapApiProviders.getFunctions
      getFunctions().then(response => {
        if (response) {
          const { categories } = response
          commit('SET_STATE', {
            functions: categories,
          })
        }
        commit('SET_STATE', {
          loading: false,
        })
      })
    },
    CREATE_FUNCTION({ commit, dispatch }, { payload }) {
      commit('SET_STATE', {
        loading: true,
      })

      const createFunction = mapApiProviders.createFunction
      createFunction(payload).then(response => {
        if (response) {
          dispatch('FETCH_FUNCTIONS')
          Vue.prototype.$notification.success({
            message: 'Success',
            description: 'Function created successfully',
          })
        }
        commit('SET_STATE', {
          loading: false,
        })
      })
    },
    DELETE_FUNCTION({ commit, dispatch }, { payload }) {
      const id = payload
      commit('SET_STATE', {
        loading: true,
      })

      const deleteFunction = mapApiProviders.deleteFunction
      deleteFunction(id).then(response => {
        if (response) {
          dispatch('FETCH_FUNCTIONS')
          Vue.prototype.$notification.success({
            message: 'Success',
            description: 'Function deleted successfully',
          })
        }
        commit('SET_STATE', {
          loading: false,
        })
      })
    },
    FETCH_PROGRAMS({ commit }) {
      commit('SET_STATE', {
        loading: true,
      })

      const getPrograms = mapApiProviders.getPrograms
      getPrograms().then(response => {
        if (response) {
          const { programs } = response
          commit('SET_STATE', {
            programs: programs,
          })
        }
        commit('SET_STATE', {
          loading: false,
        })
      })
    },
    CREATE_PROGRAM({ commit, dispatch }, { payload }) {
      commit('SET_STATE', {
        loading: true,
      })
      const data = {
        name: payload.name,
        category_id: payload.category_id,
        percentage: payload.percentage,
      }

      const createProgram = mapApiProviders.createProgram
      createProgram(data).then(response => {
        if (response) {
          dispatch('FETCH_PROGRAMS')
          Vue.prototype.$notification.success({
            message: 'Success',
            description: 'Program created successfully',
          })
        }
        commit('SET_STATE', {
          loading: false,
        })
      })
    },
    DELETE_PROGRAM({ commit, dispatch }, { payload }) {
      const id = payload
      commit('SET_STATE', {
        loading: true,
      })

      const deleteProgram = mapApiProviders.deleteProgram
      deleteProgram(id).then(response => {
        if (response) {
          dispatch('FETCH_PROGRAMS')
          Vue.prototype.$notification.success({
            message: 'Success',
            description: 'Program deleted successfully',
          })
        }
        commit('SET_STATE', {
          loading: false,
        })
      })
    },
    FETCH_SUB_CATEGORIES({ commit }) {
      commit('SET_STATE', {
        loading: true,
      })

      const getSubCategories = mapApiProviders.getSubCategories
      getSubCategories().then(response => {
        if (response) {
          const { subCategories } = response
          commit('SET_STATE', {
            subCategories: subCategories,
          })
        }
        commit('SET_STATE', {
          loading: false,
        })
      })
    },
    CREATE_SUB_CATEGORY({ commit, dispatch }, { payload }) {
      commit('SET_STATE', {
        loading: true,
      })
      const data = {
        name: payload.name,
        category_id: payload.category_id,
        parentId: payload.parentId,
      }

      const createSubCategory = mapApiProviders.createSubCategory
      createSubCategory(data).then(response => {
        if (response) {
          dispatch('FETCH_SUB_CATEGORIES')
          Vue.prototype.$notification.success({
            message: 'Success',
            description: 'Sub category created successfully',
          })
        }
        commit('SET_STATE', {
          loading: false,
        })
      })
    },
    DELETE_SUB_CATEGORY({ commit, dispatch }, { payload }) {
      const id = payload
      commit('SET_STATE', {
        loading: true,
      })

      const deleteSubCategory = mapApiProviders.deleteSubCategory
      deleteSubCategory(id).then(response => {
        if (response) {
          dispatch('FETCH_SUB_CATEGORIES')
          Vue.prototype.$notification.success({
            message: 'Success',
            description: 'Sub category deleted successfully',
          })
        }
        commit('SET_STATE', {
          loading: false,
        })
      })
    },
    FETCH_MEASURES({ commit }) {
      commit('SET_STATE', {
        loading: true,
      })

      const getMeasures = mapApiProviders.getMeasures
      getMeasures().then(response => {
        if (response) {
          const { measures } = response
          commit('SET_STATE', {
            measures: measures,
          })
        }
        commit('SET_STATE', {
          loading: false,
        })
      })
    },
    CREATE_MEASURE({ commit, dispatch }, { payload }) {
      commit('SET_STATE', {
        loading: true,
      })
      const data = {
        name: payload.name,
        items: payload.items,
      }

      const createMeasure = mapApiProviders.createMeasure
      createMeasure(data).then(response => {
        if (response) {
          dispatch('FETCH_MEASURES')
          Vue.prototype.$notification.success({
            message: 'Success',
            description: 'Measure created successfully',
          })
        }
        commit('SET_STATE', {
          loading: false,
        })
      })
    },
    UPDATE_MEASURE({ commit, dispatch }, { payload }) {
      commit('SET_STATE', {
        loading: true,
      })
      const data = {
        name: payload.name,
        items: payload.items,
        deleted: payload.deleted,
      }
      const id = payload.id

      const updateMeasure = mapApiProviders.updateMeasure
      updateMeasure(data, id).then(response => {
        if (response) {
          dispatch('FETCH_MEASURES')
          Vue.prototype.$notification.success({
            message: 'Success',
            description: 'Measure updated successfully',
          })
        }
        commit('SET_STATE', {
          loading: false,
        })
      })
    },
    DELETE_MEASURE({ commit, dispatch }, { payload }) {
      const id = payload
      commit('SET_STATE', {
        loading: true,
      })

      const deleteMeasure = mapApiProviders.deleteMeasure
      deleteMeasure(id).then(response => {
        if (response) {
          dispatch('FETCH_MEASURES')
          Vue.prototype.$notification.success({
            message: 'Success',
            description: 'Measure deleted successfully',
          })
        }
        commit('SET_STATE', {
          loading: false,
        })
      })
    },
    FETCH_ALL_FORMS({ commit }) {
      commit('SET_STATE', {
        loading: true,
      })

      const getAllForms = mapApiProviders.getAllForms
      getAllForms().then(response => {
        if (response) {
          const { forms } = response
          commit('SET_STATE', {
            forms: forms,
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
          const { positions } = response
          commit('SET_STATE', {
            positions: positions,
          })
        }
        commit('SET_STATE', {
          loading: false,
        })
      })
    },
    FETCH_YEAR_SIGNATORIES({ commit }, { payload }) {
      const { year, formId } = payload
      commit('SET_STATE', {
        loading: true,
      })

      const getYearSignatories = mapApiProviders.getYearSignatories
      getYearSignatories(year, formId).then(response => {
        if (response) {
          const { signatories } = response
          commit('SET_STATE', {
            signatories: signatories,
          })
        }
        commit('SET_STATE', {
          loading: false,
        })
      })
    },
    SAVE_POSITION_SIGNATORIES({ commit, dispatch }, { payload }) {
      commit('SET_STATE', {
        loading: true,
      })

      const saveSignatories = mapApiProviders.saveSignatories
      saveSignatories(payload).then(response => {
        if (response) {
          dispatch('FETCH_YEAR_SIGNATORIES', { payload: payload })
          Vue.prototype.$notification.success({
            message: 'Success',
            description: 'Your changes were saved successfully',
          })
        }
        commit('SET_STATE', {
          loading: false,
        })
      })
    },
    UPDATE_POSITION_SIGNATORIES({ commit, dispatch }, { payload }) {
      commit('SET_STATE', {
        loading: true,
      })

      const updateSignatories = mapApiProviders.updateSignatories
      updateSignatories(payload).then(response => {
        if (response) {
          dispatch('FETCH_YEAR_SIGNATORIES', { payload: payload })
          Vue.prototype.$notification.success({
            message: 'Success',
            description: 'Signatory updated successfully',
          })
        }
        commit('SET_STATE', {
          loading: false,
        })
      })
    },
    DELETE_POSITION_SIGNATORY({ commit, dispatch }, { payload }) {
      const { id } = payload
      commit('SET_STATE', {
        loading: true,
      })

      const deleteSignatory = mapApiProviders.deleteSignatory
      deleteSignatory(id).then(response => {
        if (response) {
          dispatch('FETCH_YEAR_SIGNATORIES', { payload: payload })
          Vue.prototype.$notification.success({
            message: 'Success',
            description: 'Personnel deleted successfully',
          })
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
