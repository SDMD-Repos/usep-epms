import router from '@/router'
import { notification } from 'ant-design-vue'

import * as jwt from '@/services/api/jwt'

const mapAuthProviders = {
  jwt: {
    login: jwt.login,
    register: jwt.register,
    currentAccount: jwt.currentAccount,
    logout: jwt.logout,
    isLoggedIn: jwt.isLoggedIn,
  },
}

export default {
  namespaced: true,
  state: {
    id: '',
    lastName: '',
    firstName: '',
    pmapsId: '',
    role: '',
    avatar: '',
    authorized: process.env.VUE_APP_AUTHENTICATED || false,
    loading: false,
    accountFetchIsTouched: jwt.isLoggedIn().then(response => {
      return response
    }),
    accessRights:[],
    formAccess: [],
  },
  mutations: {
    SET_STATE(state, payload) {
      Object.assign(state, {
        ...payload,
      })
    },
  },
  actions: {
    LOGIN({ commit, dispatch, rootState }, { payload }) {
      const { pmapsId, password } = payload
      commit('SET_STATE', {
        loading: true,
      })

      const login = mapAuthProviders[rootState.settings.authProvider].login
      login(pmapsId, password).then(success => {
        if (success) {
          dispatch('LOAD_CURRENT_ACCOUNT')
          notification.success({
            message: 'Logged In',
            description: 'You have successfully logged in!',
          })
        }
        if (!success) {
          commit('SET_STATE', {
            loading: false,
          })
        }
      })
    },
    LOAD_CURRENT_ACCOUNT({ commit, rootState }) {
      commit('SET_STATE', {
        loading: true,
      })

      const currentAccount = mapAuthProviders[rootState.settings.authProvider].currentAccount
      currentAccount().then(response => {
        if (response) {
          const { id, lastName, firstName, pmapsId, avatar, role , accessRights, formAccess } = response
          commit('SET_STATE', {
            id,
            lastName,
            firstName,
            pmapsId,
            avatar,
            role,
            authorized: true,
            accessRights,
            formAccess,
          })

        }
        commit('SET_STATE', {
          loading: false,
        })
      })


    },
    LOGOUT({ commit, rootState }) {
      const logout = mapAuthProviders[rootState.settings.authProvider].logout
      logout().then(() => {
        commit('SET_STATE', {
          id: '',
          lastName: '',
          firstName: '',
          pmapsId: '',
          avatar: '',
          role: '',
          authorized: false,
          loading: false,
          accessRights : [],
          formAccess: [],
        })
        router.push('/auth/login')
      })
    },
  },
  getters: {
    user: state => state,
    access: state => state.accessRights,
  },
}
