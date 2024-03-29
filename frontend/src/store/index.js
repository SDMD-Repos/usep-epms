import { createStore } from 'vuex'

import user from './user'
import settings from './settings'
import formManager from './formManager'
import external from './external'
import aapcr from './aapcr'
import vpopcr from './vpopcr'
import ocpcr from './ocpcr'
import opcrtemplate from './ocpcr/template'
import system from './system'
import requests from './system/requests'

export default createStore({
  modules: {
    user,
    settings,
    formManager,
    external,
    aapcr,
    vpopcr,
    ocpcr,
    opcrtemplate,
    system,
    requests,
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
