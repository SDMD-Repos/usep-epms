<template>
  <div>
    <img :src="theme === 'default' ? root + 'SDMD.png' : root + 'SDMD-dark-2.png'" alt="SDMD Branding" height="40px">
  </div>
</template>

<script>
import { mapState } from 'vuex'

export default {
  props: {
    root: String,
  },
  computed: {
    ...mapState(['user']),
    loading() {
      return this.$store.state.user.loading
    },
    theme() {
      return this.$store.state.settings.theme
    },
  },
  data: function () {
    return {
    }
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
