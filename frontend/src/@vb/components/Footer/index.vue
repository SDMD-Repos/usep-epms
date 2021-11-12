<template>
  <div :class="$style.footer">
    <div :class="$style.footerInner">
      <router-link to="/dashboard" :class="$style.logo">
        <img :src="`${mainStore.imagesPath}SDMD Logo.png`" alt="SDMD Logo" height="20">
        <strong class="mr-2 ml-2">{{ settings.officeName }}</strong>
      </router-link>
      <br />
      <p class="mb-0">
        Copyright © {{ new Date().getFullYear() }}
        <a href="https://www.usep.edu.ph" target="_blank" rel="noopener noreferrer">
          USeP
        </a>
        |
        <a href="https://www.usep.edu.ph/usep-data-privacy-statement/" target="_blank" rel="noopener noreferrer">
          Privacy Policy
        </a>
      </p>
    </div>
  </div>
</template>

<script>
import { computed } from 'vue'
import { useStore } from 'vuex'
export default {
  setup() {
    const store = useStore()
    const settings = computed(() => store.getters.settings)
    const mainStore = computed(() => store.getters.mainStore)

    return {
      settings,
      mainStore,
    }
  },
}
</script>

<style lang="scss" module>
@import './style.module.scss';
</style>
