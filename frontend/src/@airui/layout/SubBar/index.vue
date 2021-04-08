<template>
  <div :class="$style.subbar">
    <ul :class="$style.breadcrumbs" class="mr-4">
      <!-- <li :class="$style.breadcrumb">
        <a href="#" class="style.breadcrumbLink">Main</a>
      </li>
      <li :class="$style.breadcrumb">
        <a href="#" :class="[$style.breadcrumbLink, $style.breadcrumbLink__current]">Dashboard</a>
      </li> -->
    </ul>
    <!-- <div :class="$style.divider" class="mr-4 d-none d-xl-block" /> -->
    <div :class="$style.amount" class="mr-3 ml-auto">
      <a href="javascript: void(0);" @click="logout">
        <i :class="$style.menuIcon" class="fe fe-log-out"></i>
        {{ $t('topBar.profileMenu.logout') }}
      </a>
    </div>
  </div>
</template>

<script>

export default {
  methods: {
    logout() {
      this.$store.dispatch('user/LOGOUT')
    },
  },
}
</script>

<style lang="scss" module>
@import "./style.module.scss";
</style>
