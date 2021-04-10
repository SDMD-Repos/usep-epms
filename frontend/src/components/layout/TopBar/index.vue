<template>
  <div :class="$style.topbar">
    <div class="mr-md-4 mr-auto">
      <epms-logo />
    </div>
    <div class="mr-auto d-none d-md-block">
    </div>
    <div class="mb-0 mr-4 d-xl-block d-none">
    </div>
    <div class="mr-4 d-none d-sm-block">
    </div>
    <div class="mr-4 d-none d-sm-block">
    </div>
    <div class="d-none d-md-block">
      <usep-logo />
    </div>
  </div>
</template>

<script>
import EpmsLogo from '@/components/layout/TopBar/EpmsLogo'
import UsepLogo from '@/components/layout/TopBar/UsepLogo'

export default {
  components: {
    EpmsLogo,
    UsepLogo,
  },
}
</script>

<style lang="scss" module>
@import "./style.module.scss";
</style>
