<template>
  <div :class="$style.subbar">
    <ul :class="$style.breadcrumbs" class="mr-4">
    </ul>
    <div :class="$style.amount" class="mr-3 ml-auto">
      <a href="javascript: void(0);" @click="logout">
        <i :class="$style.menuIcon" class="fe fe-log-out"></i>
        {{ $t('topBar.profileMenu.logout') }}
      </a>
    </div>
  </div>
</template>

<script>
import { mapState } from 'vuex'

export default {
  computed: {
    ...mapState(['user']),
  },
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
