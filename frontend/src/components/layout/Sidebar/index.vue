<template>
  <div>
<!--    <a-tooltip placement="left">-->
<!--      <template slot="title">-->
<!--        <span>Switch Dark / Light Theme</span>-->
<!--      </template>-->
<!--      <a-->
<!--        href="javascript: void(0);"-->
<!--        @click="setTheme(settings.theme === 'default' ? 'dark' : 'default')"-->
<!--        style="bottom: calc(50% + 60px)"-->
<!--        :class="$style.air__sidebar__toggleButton"-->
<!--      >-->
<!--        <i v-if="settings.theme === 'default'" class="fe fe-moon" />-->
<!--        <i v-if="settings.theme !== 'default'" class="fe fe-sun" />-->
<!--      </a>-->
<!--    </a-tooltip>-->
  </div>
</template>

<script>
import { mapState } from 'vuex'
import throttle from 'lodash/throttle'

export default {
  computed: {
    ...mapState(['settings']),
    primaryColor() {
      return this.settings.primaryColor
    },
  },
  data() {
    return {
      defaultColor: '#6C1B1F',
    }
  },
  methods: {
    toggleSidebar: function () {
      const setting = 'isSidebarOpen'
      const value = !this.settings[setting]
      this.$store.commit('CHANGE_SETTING', { setting, value })
    },
    settingChange(e, setting) {
      const value = !this.settings[setting]
      this.$store.commit('CHANGE_SETTING', { setting, value })
    },
    selectMenuType(e) {
      const setting = 'menuType'
      const { value } = e.target
      this.$store.commit('CHANGE_SETTING', { setting, value })
    },
    selectMenuLayoutType(e) {
      const setting = 'menuLayoutType'
      const { value } = e.target
      this.$store.commit('CHANGE_SETTING', { setting, value })
    },
    selectRouterAnimation(value) {
      const setting = 'routerAnimation'
      this.$store.commit('CHANGE_SETTING', { setting, value })
    },
    selectLocale(value) {
      const setting = 'locale'
      this.$store.commit('CHANGE_SETTING', { setting, value })
    },
    setTheme(nextTheme) {
      this.$store.commit('SET_THEME', { theme: nextTheme })
      this.$store.commit('CHANGE_SETTING', {
        setting: 'menuColor',
        value: nextTheme === 'dark' ? 'dark' : 'gray',
      })
    },
    selectColor: throttle(function (color) {
      this.$store.commit('SET_PRIMARY_COLOR', { color })
    }, 200),
    resetColor() {
      this.$store.commit('SET_PRIMARY_COLOR', { color: this.defaultColor })
    },
    changeLogo(e) {
      const setting = 'logo'
      const { value } = e.target
      this.$store.commit('CHANGE_SETTING', { setting, value })
    },
    changeDescription(e) {
      const setting = 'description'
      const { value } = e.target
      this.$store.commit('CHANGE_SETTING', { setting, value })
    },
  },
}
</script>

<style lang="scss" module>
@import "./style.module.scss";
</style>
