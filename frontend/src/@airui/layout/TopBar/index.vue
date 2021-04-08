<template>
  <div :class="$style.topbar">
    <div class="mr-md-4 mr-auto">
      <air-search />
    </div>
    <div class="mr-auto d-none d-md-block">
      <!-- <air-issues-history /> -->
    </div>
    <div class="mb-0 mr-4 d-xl-block d-none">
      <!-- <air-status /> -->
    </div>
    <div class="mr-4 d-none d-sm-block">
      <!-- <air-language-switcher /> -->
    </div>
    <div class="mr-4 d-none d-sm-block">
      <!-- <air-actions /> -->
    </div>
    <div class="d-none d-md-block">
      <air-user-menu />
    </div>
  </div>
</template>

<script>
import AirSearch from '@/@airui/layout/TopBar/Search'
import AirUserMenu from '@/@airui/layout/TopBar/UserMenu'

export default {
  components: {
    AirSearch,
    AirUserMenu,
  },
}
</script>

<style lang="scss" module>
@import "./style.module.scss";
</style>
