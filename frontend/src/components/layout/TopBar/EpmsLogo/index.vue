<template>
  <div>
    <a class="navbar-brand" href="/" exact>
      <img :src="theme === 'default' ? root + 'ePMS.png' : root + 'ePMS-dark-2.png'" alt="ePMS Branding" height="40px">
    </a>
  </div>
</template>

<script>

export default {
  props: {
    root: String,
  },
  computed: {
    theme() {
      return this.$store.state.settings.theme
    },
  },
  data: function () {
    return {
    }
  },
}
</script>

<style lang="scss" module>
@import "./style.module.scss";
</style>
