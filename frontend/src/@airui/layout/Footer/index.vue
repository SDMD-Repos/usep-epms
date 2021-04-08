<template>
  <div
    :class="{
      [$style.footer]: true,
      [$style.footerFullWidth]: !settings.isContentMaxWidth
    }"
  >
    <div :class="$style.inner">
      <div class="row">
        <div class="col-md-8">
          <p>&copy; 2021 University of Southeastern Philippines</p>
        </div>
        <div class="col-md-4 d-none d-sm-block">
          <div :class="$style.logo">
            <div :class="$style.logo__letter">A</div>
            <div :class="$style.logo__name">{{ settings.logo }}</div>
            <div :class="$style.logo__descr">{{ settings.description }}</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { mapState } from 'vuex'

export default {
  computed: mapState(['settings']),
}
</script>

<style lang="scss" module>
@import "./style.module.scss";
</style>
