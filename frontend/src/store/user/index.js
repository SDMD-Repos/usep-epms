import router from '@/router'
import { notification } from 'ant-design-vue'

import * as firebase from '@/services/firebase'
import * as jwt from '@/services/jwt'

const mapAuthProviders = {
  firebase: {
    login: firebase.login,
    register: firebase.register,
    currentAccount: firebase.currentAccount,
    logout: firebase.logout,
  },
  jwt: {
    login: jwt.login,
    register: jwt.register,
    currentAccount: jwt.currentAccount,
    logout: jwt.logout,
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
    accountFetchIsTouched: false,
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
          const { id, lastName, firstName, pmapsId, avatar, role } = response
          commit('SET_STATE', {
            id,
            lastName,
            firstName,
            pmapsId,
            avatar,
            role,
            authorized: true,
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
        })
        router.push('/auth/login')
      })
    },
  },
  getters: {
    user: state => state,
  },
}
