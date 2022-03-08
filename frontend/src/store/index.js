import { createStore } from 'vuex'
import user from './user'
import settings from './settings'
import formManager from './formManager'
import external from './external'
import aapcr from './aapcr'
import opcrvp from './opcrvp'
import opcrtemplate from './opcr/template'
import opcrtemplate from './opcr/template'

export default createStore({
  modules: {
    user,
    settings,
    formManager,
    external,
    aapcr,
    opcrvp,
    opcrtemplate,
    system,
  },
  state: {
    dateFormat: 'YYYY-MM-DD hh:mm A',
    imagesPath: '/resources/images/',
  },
  mutations: {},
  actions: {},
  getters: {
    mainStore: state => state,
  },
})
