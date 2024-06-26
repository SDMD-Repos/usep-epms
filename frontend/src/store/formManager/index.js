import * as manager from '@/services/api/manager'
import { postRequest, getRequest, deleteRequest } from '@/services/api/mainForms/ocpcr'
import { notification } from 'ant-design-vue'

const mapApiProviders = {
  getFunctions: manager.getFunctions,
  createFunction: manager.createNewFunction,
  deleteFunction: manager.deleteFunction,
  updateFunctionProgram: manager.updateFunctionProgram,
  getPrograms: manager.getPrograms,
  createProgram: manager.addNewProgram,
  getOtherPrograms: manager.getOtherPrograms,
  createOtherProgram: manager.addNewOtherProgram,
  deleteProgram: manager.deleteProgram,
  deleteOtherProgram: manager.deleteOtherProgram,
  getSubCategories: manager.getSubCategories,
  createSubCategory: manager.createSubCategory,
  updateSubCategory: manager.updateSubCategory,
  deleteSubCategory: manager.deleteSubCategory,
  getMeasures: manager.getMeasures,
  getAllForms: manager.getAllForms,
  getUserFormAccess: manager.getUserFormAccess,
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
  getAllFormFields: manager.getAllFormFields,
  saveFormFieldSettings: manager.saveFormFieldSettings,
  updateFormFieldSettings: manager.updateFormFieldSettings,
  getAllFormsByPermission: manager.getAllFormsByPermission,
}

const baseUrl = '/settings'

export default {
  namespaced: true,
  state: {
    functions: [],
    previousFunctions: [],
    programs: [],
    previousPrograms: [],
    allPrograms: [],
    otherPrograms: [],
    previousOtherPrograms: [],
    subCategories: [],
    prevSubCategories: [],
    measures: [],
    previousMeasures: [],
    measureRatings: [],
    previousMeasureRatings: [],
    forms: [],
    userFormAccess: [],
    signatoryTypes: [],
    signatories: [],
    cascadingLevels: [],
    groups: [],
    formFields: [],
    loading: false,
    formsByPermission: [],
  },
  mutations: {
    SET_STATE(state, payload) {
      Object.assign(state, {
        ...payload,
      })
    },
  },
  actions: {
    FETCH_FUNCTIONS({ commit }, { payload }) {
      const { year } = payload
      const formId = typeof payload.formId !== 'undefined' ? payload.formId : 0

      commit('SET_STATE', {
        loading: true,
      })

      const getFunctions = mapApiProviders.getFunctions
      getFunctions(year, formId).then(response => {
        if (response) {
          const { categories } = response
          if (typeof payload.isPrevious !== 'undefined' && payload.isPrevious) {
            commit('SET_STATE', { previousFunctions: categories })
          } else {
            commit('SET_STATE', { functions: categories })
          }
        }
        commit('SET_STATE', {
          loading: false,
        })
      })
    },
    CREATE_FUNCTION({ commit, dispatch }, { payload }) {
      const { year } = payload

      commit('SET_STATE', { loading: true })

      const createFunction = mapApiProviders.createFunction
      createFunction(payload).then(response => {
        if (response) {
          dispatch('FETCH_FUNCTIONS', { payload: { year: year } })
          notification.success({
            message: 'Success',
            description: 'Function created successfully',
          })
        } else {
          commit('SET_STATE', { loading: false })
        }
      })
    },
    DELETE_FUNCTION({ commit, dispatch }, { payload }) {
      const { id, year } = payload

      commit('SET_STATE', { loading: true })

      const deleteFunction = mapApiProviders.deleteFunction
      deleteFunction(id).then(response => {
        if (response) {
          dispatch('FETCH_FUNCTIONS', { payload: { year: year } })
          notification.success({
            message: 'Success',
            description: 'Function deleted successfully',
          })
        } else {
          commit('SET_STATE', { loading: false })
        }
      })
    },
    UPDATE_PROGRAM_FUNCTION({ commit, dispatch }, { payload }) {
      const { id, defaultProgram, year } = payload
      commit('SET_STATE', { loading: true })

      const updateFunctionProgram = mapApiProviders.updateFunctionProgram
      updateFunctionProgram(defaultProgram, id).then(response => {
        if (response) {
          dispatch('FETCH_FUNCTIONS', { payload: { year: year } })
          notification.success({
            message: 'Success',
            description: 'Settings was saved successfully',
          })
        } else {
          commit('SET_STATE', { loading: false })
        }
      })
    },
    FETCH_PROGRAMS({ commit }, { payload }) {
      const { year } = payload
      const formId = typeof payload.formId !== 'undefined' ? payload.formId : 0

      commit('SET_STATE', {
        loading: true,
      })

      const getPrograms = mapApiProviders.getPrograms
      getPrograms(year, formId).then(response => {
        if (response) {
          const { programs } = response
          if (typeof payload.isPrevious !== 'undefined' && payload.isPrevious) {
            commit('SET_STATE', { previousPrograms: programs })
          } else {
            commit('SET_STATE', {
              programs: programs,
            })
          }
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
        year: payload.year,
        category_id: payload.category_id,
        percentage: payload.percentage,
        form_id: payload.formId,
      }

      const createProgram = mapApiProviders.createProgram
      createProgram(data).then(response => {
        if (response) {
          dispatch('FETCH_PROGRAMS', { payload: { year: payload.year, formId: payload.formId } })
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
      const { id, year } = payload
      commit('SET_STATE', {
        loading: true,
      })

      const deleteProgram = mapApiProviders.deleteProgram
      deleteProgram(id).then(response => {
        if (response) {
          dispatch('FETCH_PROGRAMS', { payload: { year: year, formId: payload.formId }})
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
    CREATE_OTHER_PROGRAM({ commit, dispatch }, { payload }) {
      commit('SET_STATE', {
        loading: true,
      })
      const data = {
        name: payload.name,
        year: payload.year,
        category_id: payload.category_id,
        percentage: payload.percentage,
        formId: payload.formId,
      }

      const createOtherProgram = mapApiProviders.createOtherProgram
      createOtherProgram(data).then(response => {
        if (response) {
          dispatch('FETCH_OTHER_PROGRAMS', {
            payload: { year: payload.year, formId: payload.formId },
          })
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
    FETCH_OTHER_PROGRAMS({ commit }, { payload }) {
      const { year, formId } = payload

      commit('SET_STATE', {
        loading: true,
      })

      const getOtherPrograms = mapApiProviders.getOtherPrograms
      getOtherPrograms(year, formId).then(response => {
        if (response) {
          const { otherPrograms } = response
          if (typeof payload.isPrevious !== 'undefined' && payload.isPrevious) {
            commit('SET_STATE', { previousOtherPrograms: otherPrograms })
          } else {
            commit('SET_STATE', {
              otherPrograms: otherPrograms,
            })
          }
        }
        commit('SET_STATE', {
          loading: false,
        })
      })
    },
    DELETE_OTHER_PROGRAM({ commit, dispatch }, { payload }) {
      const { id, year, formId } = payload
      commit('SET_STATE', {
        loading: true,
      })

      const deleteOtherProgram = mapApiProviders.deleteOtherProgram
      deleteOtherProgram(id).then(response => {
        if (response) {
          dispatch('FETCH_OTHER_PROGRAMS', { payload: { year: year, formId: formId } })
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
    FETCH_SUB_CATEGORIES({ commit }, { payload }) {
      const { year } = payload

      const isPrevious = typeof payload.isPrevious !== 'undefined' && payload.isPrevious

      commit('SET_STATE', { loading: true })

      const getSubCategories = mapApiProviders.getSubCategories
      getSubCategories(year, !isPrevious).then(response => {
        if (response) {
          const { subCategories } = response
          if (isPrevious) {
            commit('SET_STATE', { prevSubCategories: subCategories })
          } else {
            commit('SET_STATE', {
              subCategories: subCategories,
            })
          }
        }
        commit('SET_STATE', { loading: false })
      })
    },
    CREATE_SUB_CATEGORY({ commit, dispatch }, { payload }) {
      const { year, category_id, parentId, ordering, name } = payload
      commit('SET_STATE', {
        loading: true,
      })
      const data = {
        name: name,
        category_id: category_id,
        parentId: parentId,
        ordering: ordering,
        year: year,
      }

      const createSubCategory = mapApiProviders.createSubCategory
      createSubCategory(data).then(response => {
        if (response) {
          dispatch('FETCH_SUB_CATEGORIES', { payload: { year: year } })
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
    UPDATE_SUB_CATEGORY({ commit, dispatch }, { payload }) {
      const { id, year, category_id, parent_id, ordering, name } = payload
      commit('SET_STATE', {
        loading: true,
      })
      const data = {
        id: id,
        name: name,
        category_id: category_id,
        parentId: parent_id,
        ordering: ordering,
        year: year,
      }

      const updateSubCategory = mapApiProviders.updateSubCategory
      updateSubCategory(data).then(response => {
        dispatch('FETCH_SUB_CATEGORIES', { payload: { year: year } })
        if (response) {
          notification.success({
            message: 'Success',
            description: 'Sub category updated successfully',
          })
        }
        commit('SET_STATE', {
          loading: false,
        })
      })
    },
    DELETE_SUB_CATEGORY({ commit, dispatch }, { payload }) {
      const { id, year } = payload
      commit('SET_STATE', {
        loading: true,
      })

      const deleteSubCategory = mapApiProviders.deleteSubCategory
      deleteSubCategory(id).then(response => {
        if (response) {
          dispatch('FETCH_SUB_CATEGORIES', { payload: { year: year } })
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
          if (typeof payload.isPrevious !== 'undefined' && payload.isPrevious) {
            commit('SET_STATE', { previousMeasures: measures })
          } else {
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

      postRequest(baseUrl + '/create-measure', payload).then(response => {
        if (response) {
          dispatch('FETCH_MEASURES', { payload: { year: payload.year } })
          notification.success({
            message: 'Success',
            description: response.message,
          })
        } else {
          commit('SET_STATE', { loading: false })
        }
      })
    },
    UPDATE_MEASURE({ commit, dispatch }, { payload }) {
      commit('SET_STATE', {
        loading: true,
      })

      postRequest(baseUrl + '/update-measure/' + payload.id, payload).then(response => {
        if (response) {
          dispatch('FETCH_MEASURES', { payload: { year: payload.year } })
          notification.success({
            message: 'Success',
            description: response.message,
          })
        } else {
          commit('SET_STATE', { loading: false })
        }
      })
    },
    DELETE_MEASURE({ commit, dispatch }, { payload }) {
      const { id, year } = payload
      commit('SET_STATE', {
        loading: true,
      })

      deleteRequest(baseUrl + '/delete-measure/' + id).then(response => {
        if (response) {
          dispatch('FETCH_MEASURES', { payload: { year: year } })
          notification.success({
            message: 'Success',
            description: 'Measure deleted successfully',
          })
        } else {
          commit('SET_STATE', { loading: false })
        }
      })
    },
    FETCH_MEASURE_RATINGS({ commit }, { payload }) {
      const { year } = payload

      commit('SET_STATE', {
        loading: true,
      })

      getRequest(baseUrl + '/get-all-measure-ratings/' + year).then(response => {
        if (response) {
          const { measureRatings } = response
          if (typeof payload.isPrevious !== 'undefined' && payload.isPrevious) {
            commit('SET_STATE', { previousMeasureRatings: measureRatings })
          } else {
            commit('SET_STATE', { measureRatings: measureRatings })
          }
        }
        commit('SET_STATE', {
          loading: false,
        })
      })
    },
    CREATE_MEASURE_RATING({ commit, dispatch }, { payload }) {
      commit('SET_STATE', {
        loading: true,
      })

      postRequest(baseUrl + '/create-measure-rating', payload).then(response => {
        if (response) {
          dispatch('FETCH_MEASURE_RATINGS', { payload: { year: payload.year } })
          notification.success({
            message: 'Success',
            description: response.message,
          })
        }
        commit('SET_STATE', {
          loading: false,
        })
      })
    },
    UPDATE_MEASURE_RATING({ commit, dispatch }, { payload }) {
      commit('SET_STATE', {
        loading: true,
      })
      const { id, numericalRating, averagePointScore, year, adjectivalRating, description } = payload
      const data = {
        id: id,
        year: year,
        numerical_rating: numericalRating,
        aps_from: averagePointScore.from,
        aps_to: averagePointScore.to,
        adjectival_rating: adjectivalRating,
        description: description,
      }

      postRequest(baseUrl + '/update-measure-rating/' + id, data).then(response => {
        if (response) {
          dispatch('FETCH_MEASURE_RATINGS', { payload: { year: year } })
          notification.success({
            message: 'Success',
            description: response.message,
          })
        } else {
          commit('SET_STATE', { loading: false })
        }
      })
    },
    DELETE_MEASURE_RATING({ commit, dispatch }, { payload }) {
      const { id, year } = payload
      commit('SET_STATE', {
        loading: true,
      })

      deleteRequest(baseUrl + '/delete-measure-rating/' + id).then(response => {
        let type = ''
        if (response) {
          const { message, description } = response
          type = response.type

          notification[type]({ message: message, description: description })

          if(type !== 'error') {
            dispatch('FETCH_MEASURE_RATINGS', { payload: { year: year } })
          }
        }

        if(!response || type === 'error') { commit('SET_STATE', { loading: false }) }
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
    FETCH_USER_FORM_ACCESS({ commit }) {
      commit('SET_STATE', {
        loading: true,
      })

      const getUserFormAccess = mapApiProviders.getUserFormAccess
      getUserFormAccess().then(response => {
        if (response) {
          const { userForms } = response
          commit('SET_STATE', {
            userFormAccess: userForms,
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
            description: 'Signatory deleted successfully',
          })
        }
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
    FETCH_FORM_FIELDS({ commit }, { payload }) {
      const { year, formId } = payload
      commit('SET_STATE', {
        loading: true,
      })

      const getAllFormFields = mapApiProviders.getAllFormFields
      getAllFormFields(year, formId).then(response => {
        if (response) {
          const { formFields } = response
          commit('SET_STATE', {
            formFields: formFields,
          })
        }
        commit('SET_STATE', {
          loading: false,
        })
      })
    },
    SAVE_FORM_FIELD_SETTINGS({ commit, dispatch }, { payload }) {
      const { year, formId } = payload
      commit('SET_STATE', {
        loading: true,
      })

      const saveFormFieldSettings = mapApiProviders.saveFormFieldSettings
      saveFormFieldSettings(payload).then(response => {
        if (response) {
          notification.success({
            message: 'Success',
            description: 'Settings was saved successfully',
          })
        }
        dispatch('FETCH_FORM_FIELDS', { payload: { year: year, formId: formId } })
        commit('SET_STATE', {
          loading: false,
        })
      })
    },
    UPDATE_FORM_FIELD_SETTINGS({ commit, dispatch }, { payload }) {
      commit('SET_STATE', {
        loading: true,
      })
      const { settingId, year, formId } = payload

      const updateFormFieldSettings = mapApiProviders.updateFormFieldSettings
      updateFormFieldSettings(payload, settingId).then(response => {
        if (response) {
          dispatch('FETCH_FORM_FIELDS', { payload: { year: year, formId: formId } })
          notification.success({
            message: 'Success',
            description: 'Settings was updated successfully',
          })
        }
        commit('SET_STATE', {
          loading: false,
        })
      })
    },
    FETCH_ALL_FORMS_BY_PERMISSION({ commit }, { payload }) {
      commit('SET_STATE', {
        loading: true,
      })

      const { allForms } = payload

      const getAllFormsByPermission = mapApiProviders.getAllFormsByPermission
      getAllFormsByPermission(allForms).then(response => {
        if (response) {
          const { forms } = response
          commit('SET_STATE', {
            formsByPermission: forms,
          })
        }
        commit('SET_STATE', {
          loading: false,
        })
      })
    },

    SAVE_FORM_CATEGORY({ commit, dispatch }, { payload }) {
      const { year, formId } = payload
      commit('SET_STATE', {
        loading: true,
      })

      postRequest(baseUrl + '/save-form-category', payload).then(response => {
        if (response) {
          dispatch('FETCH_FUNCTIONS', { payload: { year: year, formId: formId } })
          notification.success({
            message: 'Success',
            description: response.message,
          })
        }
        commit('SET_STATE', {
          loading: false,
        })
      })
    },
    DELETE_FORM_CATEGORY({ commit, dispatch }, { payload }) {
      const { year, formId, id } = payload
      commit('SET_STATE', {
        loading: true,
      })

      deleteRequest(baseUrl + '/delete-form-category/' + id).then(response => {
        if (response) {
          dispatch('FETCH_FUNCTIONS', { payload: { year: year, formId: formId } })
          notification.success({
            message: 'Success',
            description: 'Display Name was cleared successfully',
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
