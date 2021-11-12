<template>
  <a-layout class="vb__layout">
    <a-layout-content>
      <!-- <vb-sidebar /> -->
      <!-- <vb-support-chat /> -->
      <div
        :class="{
          [$style.container]: true,
          vb__layout__squaredBorders: settings.isSquaredBorders,
          vb__layout__cardsShadow: settings.isCardShadow,
          vb__layout__borderless: settings.isBorderless,
          [$style.white]: settings.authPagesColor === 'white',
          [$style.gray]: settings.authPagesColor === 'gray',
        }"
        :style="{
          backgroundImage:
            settings.authPagesColor === 'image'
              ? `url(resources/images/content/photos/8.jpeg)`
              : 'none',
        }"
      >
        <div
          :class="{
            [$style.topbar]: true,
            [$style.topbarGray]: settings.isGrayTopbar,
          }"
        >
          <div :class="$style.logoContainer">
            <div :class="$style.logo">
              <router-link to="/dashboard" class="font-size-16 vb__utils__link">
                <img :src="settings.theme === 'default' ? `${imagesPath}ePMS.png` : `${imagesPath}ePMS-dark-2.png`" alt="ePMS Branding" height="40">
              </router-link>
            </div>
          </div>
          <div class="d-none d-sm-block">
            <img :src="settings.theme === 'default' ? `${imagesPath}SDMD.png` : `${imagesPath}SDMD-dark-2.png`" alt="SDMD Branding" height="40">
          </div>
        </div>
        <div class="mb-5">
          <router-view v-slot="{ Component }">
            <transition :name="settings.routerAnimation" mode="out-in">
              <component :is="Component" />
            </transition>
          </router-view>
        </div>
        <div class="mt-auto pb-5 pt-5">
          <div class="text-center">
            Copyright © {{ new Date().getFullYear() }}
            <a href="https://www.usep.edu.ph" target="_blank" rel="noopener noreferrer">
              USeP
            </a>
            |
            <a href="https://www.usep.edu.ph/usep-data-privacy-statement/" target="_blank" rel="noopener noreferrer">
              Privacy Policy
            </a>
          </div>
        </div>
      </div>
    </a-layout-content>
  </a-layout>
</template>

<script>
import { computed } from 'vue'
import { useStore } from 'vuex'
// import VbSidebar from '@/@vb/components/Sidebar'
// import VbSupportChat from '@/@vb/components/SupportChat'

export default {
  name: 'AuthLayout',
  components: {},
  setup() {
    const store = useStore()
    const imagesPath = '/resources/images/'
    const settings = computed(() => store.getters.settings)

    return {
      imagesPath,
      settings,
    }
  },
}
</script>

<style lang="scss" module>
@import './style.module.scss';
</style>
