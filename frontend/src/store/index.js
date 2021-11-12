import { createStore } from 'vuex'
import user from './user'
import settings from './settings'
import formManager from './formManager'
import external from './external'

export default createStore({
  modules: {
    user,
    settings,
    formManager,
    external,
  },
  state: {
    imagesPath: '/resources/images/',
  },
  mutations: {},
  actions: {},
  getters: {
    mainStore: state => state,
  },
})
