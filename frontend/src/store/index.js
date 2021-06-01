import Vue from 'vue'
import Vuex from 'vuex'
import user from './user'
import settings from './settings'
import formSettings from './formSettings'
import external from './external'
import aapcr from './aapcr'

Vue.use(Vuex)

export default new Vuex.Store({
  modules: {
    user,
    settings,
    formSettings,
    external,
    aapcr,
  },
  state: {},
  mutations: {},
  actions: {},
})
