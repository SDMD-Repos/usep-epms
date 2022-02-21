import * as manager from '@/services/api/manager'
import { notification } from 'ant-design-vue'

const mapApiProviders = {
  getFunctions: manager.getFunctions,
  createFunction: manager.createNewFunction,
  deleteFunction: manager.deleteFunction,
  getPrograms: manager.getPrograms,
  createProgram: manager.addNewProgram,
  deleteProgram: manager.deleteProgram,
  getSubCategories: manager.getSubCategories,
  createSubCategory: manager.createSubCategory,
  deleteSubCategory: manager.deleteSubCategory,
  getMeasures: manager.getMeasures,
  createMeasure: manager.createMeasure,
  updateMeasure: manager.updateMeasure,
  deleteMeasure: manager.deleteMeasure,
  getAllForms: manager.getAllForms,
  getAllSignatoryTypes: manager.getAllSignatoryTypes,
  getYearSignatories: manager.getYearSignatories,
  saveSignatories: manager.saveSignatories,
  updateSignatories: manager.updateSignatory,
  deleteSignatory: manager.deleteSignatory,
  fetchGroups: manager.fetchAllGroups,
  createGroup: manager.createGroup,
  updateGroup: manager.updateGroup,
  deleteGroup: manager.deleteGroup,
  getCascadingLevels: manager.getCascadingLevels,
}

export default {
  namespaced: true,
  state: {
    functions: [],
    programs: [],
    subCategories: [],
    measures: [],
    previousMeasures: [],
    forms: [],
    signatoryTypes: [],
    signatories: [],
    cascadingLevels: [],
    groups: [],
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
          notification.success({
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
          notification.success({
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
          notification.success({
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
          notification.success({
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
          notification.success({
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
          notification.success({
            message: 'Success',
            description: 'Sub category deleted successfully',
          })
        }
        commit('SET_STATE', {
          loading: false,
        })
      })
    },
    FETCH_MEASURES({ commit }, { payload }) {
      const { year } = payload

      commit('SET_STATE', {
        loading: true,
      })

      const getMeasures = mapApiProviders.getMeasures
      getMeasures(year).then(response => {
        if (response) {
          const { measures } = response
          if(typeof payload.isPrevious !== 'undefined' && payload.isPrevious) {
            commit('SET_STATE', { previousMeasures: measures })
          }else {
            commit('SET_STATE', { measures: measures })
          }
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
        year: payload.year,
        items: payload.items,
      }

      const createMeasure = mapApiProviders.createMeasure
      createMeasure(data).then(response => {
        if (response) {
          dispatch('FETCH_MEASURES', { payload : { year: payload.year }})
          notification.success({
            message: 'Success',
            description: 'Measure created successfully',
          })
        }
        /*commit('SET_STATE', {
          loading: false,
        })*/
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
          dispatch('FETCH_MEASURES', { payload : { year: payload.year }})
          notification.success({
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
      const { id, year } = payload
      commit('SET_STATE', {
        loading: true,
      })

      const deleteMeasure = mapApiProviders.deleteMeasure
      deleteMeasure(id).then(response => {
        if (response) {
          dispatch('FETCH_MEASURES', { payload : { year: year }})
          notification.success({
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
          const { spmsForms } = response
          commit('SET_STATE', {
            forms: spmsForms,
          })
        }
        commit('SET_STATE', {
          loading: false,
        })
      })
    },
    FETCH_ALL_SIGNATORY_TYPES({ commit }) {
      commit('SET_STATE', {
        loading: true,
      })

      const getAllSignatoryTypes = mapApiProviders.getAllSignatoryTypes
      getAllSignatoryTypes().then(response => {
        if (response) {
          const { signatoryTypes } = response
          commit('SET_STATE', {
            signatoryTypes: signatoryTypes,
          })
        }
        commit('SET_STATE', {
          loading: false,
        })
      })
    },
    FETCH_YEAR_SIGNATORIES({ commit }, { payload }) {
      const { year, formId, officeId } = payload
      commit('SET_STATE', {
        loading: true,
      })

      const getYearSignatories = mapApiProviders.getYearSignatories
      getYearSignatories(year, formId, officeId).then(response => {
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
          notification.success({
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
          notification.success({
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
          notification.success({
            message: 'Success',
            description: 'Personnel deleted successfully',
          })
        }
        commit('SET_STATE', {
          loading: false,
        })
      })
    },
    FETCH_GROUPS({ commit }) {
      commit('SET_STATE', {
        loading: true,
      })

      const fetchGroups = mapApiProviders.fetchGroups
      fetchGroups().then(response => {
        if (response) {
          const { groups } = response
          commit('SET_STATE', {
            groups: groups,
          })
        }
        commit('SET_STATE', {
          loading: false,
        })
      })
    },
    CREATE_GROUP({ commit, dispatch }, { payload }) {
      commit('SET_STATE', {
        loading: true,
      })

      const createGroup = mapApiProviders.createGroup
      createGroup(payload).then(response => {
        if (response) {
          dispatch('FETCH_GROUPS')
          notification.success({
            message: 'Success',
            description: 'Group created successfully',
          })
        }
        commit('SET_STATE', {
          loading: false,
        })
      })
    },
    UPDATE_GROUP({ commit, dispatch }, { payload }) {
      commit('SET_STATE', {
        loading: true,
      })
      const id = payload.id

      const updateGroup = mapApiProviders.updateGroup
      updateGroup(payload, id).then(response => {
        if (response) {
          dispatch('FETCH_GROUPS')
          notification.success({
            message: 'Success',
            description: 'Group updated successfully',
          })
        }
        commit('SET_STATE', {
          loading: false,
        })
      })
    },
    DELETE_GROUP({ commit, dispatch }, { payload }) {
      const id = payload
      commit('SET_STATE', {
        loading: true,
      })

      const deleteGroup = mapApiProviders.deleteGroup
      deleteGroup(id).then(response => {
        if (response) {
          dispatch('FETCH_GROUPS')
          notification.success({
            message: 'Success',
            description: 'Group deleted successfully',
          })
        }
        commit('SET_STATE', {
          loading: false,
        })
      })
    },
    FETCH_CASCADING_LEVELS({ commit }) {
      commit('SET_STATE', {
        loading: true,
      })

      const getCascadingLevels = mapApiProviders.getCascadingLevels
      getCascadingLevels().then(response => {
        if (response) {
          const { cascadingLevels } = response
          commit('SET_STATE', {
            cascadingLevels: cascadingLevels,
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
    subCategories: state => state.subCategories,
    manager: state => state,
  },
}
