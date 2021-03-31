import Vue from 'vue'
import Vuex from 'vuex'
import store from 'store'

Vue.use(Vuex)

const STORED_SETTINGS = storedSettings => {
  const settings = {}
  Object.keys(storedSettings).forEach(key => {
    const item = store.get(`app.settings.${key}`)
    settings[key] = typeof item !== 'undefined' ? item : storedSettings[key]
  })
  return settings
}

export default {
  state: {
    ...STORED_SETTINGS({
      authProvider: 'jwt', // firebase, jwt
      logo: 'USeP',
      logoName: 'University of Southeastern Philippines',
      description: 'Electronic Performance Management System',
      acronym: 'e-PMS',
      locale: 'en-US',
      isSidebarOpen: false,
      isSupportChatOpen: false,
      isMobileView: false,
      isMobileMenuOpen: false,
      isMenuCollapsed: false,
      menuLayoutType: 'left', // left, top, nomenu
      menuType: 'default', // default, flyout, compact
      routerAnimation: 'slide-fadein-up', // none, slide-fadein-up, slide-fadein-right, fadein, zoom-fadein
      menuColor: 'gray', // dark, blue, gray, white
      flyoutMenuColor: 'blue', // dark, blue, gray, white
      authPagesColor: 'gray', // white, gray, image
      theme: 'default', // default, dark
      primaryColor: '#6C1B1F',
      isMenuUnfixed: false,
      isMenuShadow: false,
      isTopbarFixed: false,
      isGrayTopbar: false,
      isContentMaxWidth: true,
      isAppMaxWidth: false,
      isGrayBackground: true,
      isCardShadow: true,
      isSquaredBorders: false,
      isBorderless: false,
      isFooterDark: false,
    }),
  },
  mutations: {
    CHANGE_SETTING(state, payload) {
      window.localStorage.setItem(`app.settings.${payload.setting}`, payload.value)
      state[payload.setting] = payload.value
    },
    SETUP_URL_SETTINGS(state, payload) {
      let queryParams = payload
      let keys = false
      if (payload.redirect) {
        const str = payload.redirect
        const subs = str.substring(str.indexOf('?') + 1)
        if (str.indexOf('?') >= 0) {
          queryParams = JSON.parse('{"' + decodeURI(subs).replace(/"/g, '\\"').replace(/&/g, '","').replace(/=/g, '":"') + '"}')
        }
      }
      delete queryParams.redirect
      keys = Object.keys(queryParams)
      if (keys.length) {
        keys.forEach(key => {
          let value
          switch (queryParams[key]) {
            case 'false':
              value = false
              break
            case 'true':
              value = true
              break
            default:
              value = queryParams[key]
              break
          }
          if (key in state) { state[key] = value }
        })
      }
    },
    SET_PRIMARY_COLOR(state, payload) {
      const { color } = payload
      const addStyles = () => {
        const styleElement = document.querySelector('#primaryColor')
        if (styleElement) {
          styleElement.remove()
        }
        const body = document.querySelector('body')
        const styleEl = document.createElement('style')
        const css = document.createTextNode(`:root { --kit-color-primary: ${color};}`)
        styleEl.setAttribute('id', 'primaryColor')
        styleEl.appendChild(css)
        body.appendChild(styleEl)
      }
      addStyles()
      state.primaryColor = color
      store.set('app.settings.primaryColor', color)
    },
    SET_THEME(state, payload) {
      const { theme } = payload
      const nextTheme = theme === 'dark' ? 'dark' : 'default'
      document.querySelector('html').setAttribute('data-kit-theme', nextTheme)
      state.theme = nextTheme
      store.set('app.settings.theme', nextTheme)
    },
  },
  actions: {},
  getters: {
    state: state => state,
  },
}
