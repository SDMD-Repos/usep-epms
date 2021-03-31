<template>
  <div>
    <div
      :class="{[$style.air__sidebar]: true, [$style.air__sidebar__toggled]: settings.isSidebarOpen}"
    >
      <vue-custom-scrollbar :style="{ height: '100%' }">
        <div :class="$style.air__sidebar__inner">
          <a
            href="javascript: void(0);"
            :class="$style.air__sidebar__close"
            class="fe fe-x-circle"
            @click="toggleSidebar()"
          />
          <h5>
            <strong>Theme Settings</strong>
          </h5>
          <div class="air__utils__line" :style="{ marginTop: '25px', marginBottom: '30px' }" />
          <div :class="$style.air__sidebar__type" class="mb-4">
            <div :class="$style.air__sidebar__type__title">
              <span>Application Name</span>
            </div>
            <div :class="$style.air__sidebar__type__items" class="mb-3">
              <a-input :value="settings.logo" @change="changeLogo" />
            </div>
            <div :class="$style.air__sidebar__type__items">
              <a-input :value="settings.description" @change="changeDescription" />
            </div>
          </div>
          <div :class="$style.air__sidebar__type">
            <div :class="$style.air__sidebar__type__title">
              <span>Menu Layout</span>
            </div>
            <div :class="$style.air__sidebar__type__items">
              <a-radio-group :defaultValue="settings.menuLayoutType" @change="selectMenuLayoutType">
                <div class="row">
                  <div class="col-6">
                    <div class="mb-2">
                      <a-radio :value="'left'">Left Menu</a-radio>
                    </div>
                    <div class="mb-2">
                      <a-radio :value="'top-dark'">Top Dark</a-radio>
                    </div>
                  </div>
                  <div class="col-6">
                    <div class="mb-2">
                      <a-radio :value="'top'">Top Menu</a-radio>
                    </div>
                    <div class="mb-2">
                      <a-radio :value="'nomenu'">No menu</a-radio>
                    </div>
                  </div>
                </div>
              </a-radio-group>
            </div>
          </div>
          <div :class="$style.air__sidebar__type">
            <div :class="$style.air__sidebar__type__title">
              <span>Left Menu Type</span>
            </div>
            <div :class="$style.air__sidebar__type__items">
              <a-radio-group :defaultValue="settings.menuType" @change="selectMenuType">
                <div class="row">
                  <div class="col-6">
                    <div class="mb-2">
                      <a-radio
                        :value="'default'"
                        :disabled="settings.menuLayoutType !== 'left'"
                      >Default</a-radio>
                    </div>
                    <div class="mb-2">
                      <a-radio
                        :value="'flyout'"
                        :disabled="settings.menuLayoutType !== 'left'"
                      >Flyout</a-radio>
                    </div>
                  </div>
                  <div class="col-6">
                    <div class="mb-2">
                      <a-radio
                        :value="'compact'"
                        :disabled="settings.menuLayoutType !== 'left'"
                      >Compact</a-radio>
                    </div>
                  </div>
                </div>
              </a-radio-group>
            </div>
          </div>
          <div :class="$style.air__sidebar__type" class="mb-4">
            <div :class="$style.air__sidebar__type__title">
              <span>Router Animation</span>
            </div>
            <div :class="$style.air__sidebar__type__items">
              <a-select
                :defaultValue="settings.routerAnimation"
                style="width: 100%;"
                @change="selectRouterAnimation"
              >
                <a-select-option value="none">None</a-select-option>
                <a-select-option value="slide-fadein-up">Slide Up</a-select-option>
                <a-select-option value="slide-fadein-right">Slide Right</a-select-option>
                <a-select-option value="fadein">Fade In</a-select-option>
                <a-select-option value="zoom-fadein">Zoom</a-select-option>
              </a-select>
            </div>
          </div>
          <div :class="$style.air__sidebar__type" class="mb-4">
            <div :class="$style.air__sidebar__type__title">
              <span>Internationalization</span>
            </div>
            <div :class="$style.air__sidebar__type__items">
              <a-select :defaultValue="settings.locale" style="width: 100%;" @change="selectLocale">
                <a-select-option value="en-US">English (en-US)</a-select-option>
                <a-select-option value="fr-FR">French (fr-FR)</a-select-option>
                <a-select-option value="ru-RU">Русский (ru-RU)</a-select-option>
                <a-select-option value="zh-CN">简体中文 (zh-CN))</a-select-option>
              </a-select>
            </div>
          </div>
          <div :class="$style.air__sidebar__item">
            <div :class="$style.air__sidebar__label">Left Menu: Collapsed</div>
            <div :class="$style.air__sidebar__container">
              <a-switch
                :checked="settings.isMenuCollapsed"
                @click="settingChange($event, 'isMenuCollapsed')"
              />
            </div>
          </div>
          <div :class="$style.air__sidebar__item">
            <div :class="$style.air__sidebar__label">Left Menu: Unfixed</div>
            <div :class="$style.air__sidebar__container">
              <a-switch
                :checked="settings.isMenuUnfixed"
                @click="settingChange($event, 'isMenuUnfixed')"
              />
            </div>
          </div>
          <div :class="$style.air__sidebar__item">
            <div :class="$style.air__sidebar__label">Left Menu: Shadow</div>
            <div :class="$style.air__sidebar__container">
              <a-switch
                :checked="settings.isMenuShadow"
                @click="settingChange($event, 'isMenuShadow')"
              />
            </div>
          </div>
          <div :class="$style.air__sidebar__item">
            <div :class="$style.air__sidebar__label">Footer: Dark</div>
            <div :class="$style.air__sidebar__container">
              <a-switch
                :checked="settings.isFooterDark"
                @click="settingChange($event, 'isFooterDark')"
              />
            </div>
          </div>
          <div :class="$style.air__sidebar__item">
            <div :class="$style.air__sidebar__label">Menu: Color</div>
            <div :class="$style.air__sidebar__container">
              <air-color-picker
                :value="settings.menuColor"
                :setting="'menuColor'"
                :colors="['white', 'gray', 'blue', 'dark']"
              />
            </div>
          </div>
          <div :class="$style.air__sidebar__item">
            <div :class="$style.air__sidebar__label">Flyout: Color</div>
            <div :class="$style.air__sidebar__container">
              <air-color-picker
                :value="settings.flyoutMenuColor"
                :setting="'flyoutMenuColor'"
                :colors="['white', 'gray', 'blue', 'dark']"
              />
            </div>
          </div>
          <div :class="$style.air__sidebar__item">
            <div :class="$style.air__sidebar__label">Auth: Background</div>
            <div :class="$style.air__sidebar__container">
              <air-color-picker
                :value="settings.systemLayoutColor"
                :setting="'systemLayoutColor'"
                :colors="['white', 'gray', 'image']"
              />
            </div>
          </div>
          <div :class="$style.air__sidebar__item">
            <div :class="$style.air__sidebar__label">Topbar: Fixed</div>
            <div :class="$style.air__sidebar__container">
              <a-switch
                :checked="settings.isTopbarFixed"
                @click="settingChange($event, 'isTopbarFixed')"
              />
            </div>
          </div>
          <div :class="$style.air__sidebar__item">
            <div :class="$style.air__sidebar__label">Topbar: Gray Background</div>
            <div :class="$style.air__sidebar__container">
              <a-switch
                :checked="settings.isGrayTopbar"
                @click="settingChange($event, 'isGrayTopbar')"
              />
            </div>
          </div>
          <div :class="$style.air__sidebar__item">
            <div :class="$style.air__sidebar__label">App: Content Max-Width</div>
            <div :class="$style.air__sidebar__container">
              <a-switch
                :checked="settings.isContentMaxWidth"
                @click="settingChange($event, 'isContentMaxWidth')"
              />
            </div>
          </div>
          <div :class="$style.air__sidebar__item">
            <div :class="$style.air__sidebar__label">App: Max-Width</div>
            <div :class="$style.air__sidebar__container">
              <a-switch
                :checked="settings.isAppMaxWidth"
                @click="settingChange($event, 'isAppMaxWidth')"
              />
            </div>
          </div>
          <div :class="$style.air__sidebar__item">
            <div :class="$style.air__sidebar__label">App: Gray Background</div>
            <div :class="$style.air__sidebar__container">
              <a-switch
                :checked="settings.isGrayBackground"
                @click="settingChange($event, 'isGrayBackground')"
              />
            </div>
          </div>

          <div :class="$style.air__sidebar__item">
            <div :class="$style.air__sidebar__label">Cards: Squared Borders</div>
            <div :class="$style.air__sidebar__container">
              <a-switch
                :checked="settings.isSquaredBorders"
                @click="settingChange($event, 'isSquaredBorders')"
              />
            </div>
          </div>
          <div :class="$style.air__sidebar__item">
            <div :class="$style.air__sidebar__label">Cards: Shadow</div>
            <div :class="$style.air__sidebar__container">
              <a-switch
                :checked="settings.isCardShadow"
                @click="settingChange($event, 'isCardShadow')"
              />
            </div>
          </div>
          <div :class="$style.air__sidebar__item">
            <div :class="$style.air__sidebar__label">Cards: Borderless</div>
            <div :class="$style.air__sidebar__container">
              <a-switch
                :checked="settings.isBorderless"
                @click="settingChange($event, 'isBorderless')"
              />
            </div>
          </div>
        </div>
      </vue-custom-scrollbar>
    </div>
    <!-- <a-tooltip placement="left">
      <template slot="title">
        <span>Settings</span>
      </template>
      <a
        href="javascript: void(0);"
        @click="toggleSidebar()"
        style="bottom: calc(50% + 120px)"
        :class="$style.air__sidebar__toggleButton"
      >
        <i class="fe fe-settings" />
      </a>
    </a-tooltip> -->
    <a-tooltip placement="left">
      <template slot="title">
        <span>Switch Dark / Light Theme</span>
      </template>
      <a
        href="javascript: void(0);"
        @click="setTheme(settings.theme === 'default' ? 'dark' : 'default')"
        style="bottom: calc(50% + 60px)"
        :class="$style.air__sidebar__toggleButton"
      >
        <i v-if="settings.theme === 'default'" class="fe fe-moon" />
        <i v-if="settings.theme !== 'default'" class="fe fe-sun" />
      </a>
    </a-tooltip>
    <!-- <a-tooltip placement="left">
      <template slot="title">
        <span>Set Primary Color</span>
      </template>
      <a
        href="javascript: void(0);"
        style="bottom: calc(50%)"
        :class="{
        [$style.air__sidebar__toggleButton]: true,
        [$style.color]: true,
        [$style.reset]: settings.primaryColor === defaultColor,
      }"
      >
        <button type="button" tabindex="0" @click="resetColor()">
          <i class="fe fe-x-circle" />
        </button>
        <input
          type="color"
          id="colorPicker"
          @change="e => selectColor(e.target.value)"
          v-model="settings.primaryColor"
        />
        <i class="fe fe-package" />
      </a>
    </a-tooltip>
    <a-tooltip placement="left">
      <template slot="title">
        <span>Documentation</span>
      </template>
      <a
        href="https://docs.sellpixels.com"
        target="_blank"
        rel="noopener noreferrer"
        style="bottom: calc(50% - 60px)"
        :class="$style.air__sidebar__toggleButton"
      >
        <i class="fe fe-book-open" />
      </a>
    </a-tooltip> -->
  </div>
</template>

<script>
import vueCustomScrollbar from 'vue-custom-scrollbar'
import { mapState } from 'vuex'
import throttle from 'lodash/throttle'
import AirColorPicker from '@/@airui/layout/Sidebar/partials/colorPicker'

export default {
  components: { vueCustomScrollbar, AirColorPicker },
  computed: {
    ...mapState(['settings']),
    primaryColor() {
      return this.settings.primaryColor
    },
  },
  data() {
    return {
      defaultColor: '#1b55e3',
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
        value: nextTheme === 'dark' ? 'dark' : 'light',
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
