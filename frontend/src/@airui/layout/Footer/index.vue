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
          <p>
            <strong>Air UI Admin Template - Best Solution for Your Next Big App!</strong>
          </p>
          <p>
            Air UI is a set of modern professional Html / React / Vue and Angular based
            templates. This is a powerful and super flexible tool, which suits best for any kind
            of web application: Web Sites, Web Applications, Hybrid Apps, CRM, CMS, Admin
            Panels, etc.
          </p>
          <p>&copy; 2019 Mediatec Software</p>
        </div>
        <div class="col-md-4">
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
