<template>
  <div :class="$style.topbarDark">
    <a href="javascript: void(0);" :class="$style.logo" class="d-none d-md-block">
      <div :class="$style.logo__letter">A</div>
      <div :class="$style.logo__name">{{ settings.logo }}</div>
      <div :class="$style.logo__descr">{{ settings.description }}</div>
    </a>
    <div class="mr-3 d-block">
      <air-menu-dashboards />
    </div>
    <div class="mr-auto d-block">
      <air-menu-pages />
    </div>
    <div class="mb-0 mr-4 d-xl-block d-none">
      <air-status />
    </div>
    <div class="mr-4 d-none d-md-block">
      <air-language-switcher />
    </div>
    <div class="mr-4 d-none d-md-block">
      <air-actions />
    </div>
    <div class="d-none d-md-block">
      <air-user-menu />
    </div>
  </div>
</template>

<script>
import { mapState } from 'vuex'
import AirMenuDashboards from '@/@airui/layout/TopBarDark/MenuDashboards'
import AirMenuPages from '@/@airui/layout/TopBarDark/MenuPages'
import AirStatus from '@/@airui/layout/TopBarDark/Status'
import AirLanguageSwitcher from '@/@airui/layout/TopBarDark/LanguageSwitcher'
import AirActions from '@/@airui/layout/TopBarDark/Actions'
import AirUserMenu from '@/@airui/layout/TopBarDark/UserMenu'

export default {
  components: {
    AirMenuDashboards,
    AirMenuPages,
    AirStatus,
    AirLanguageSwitcher,
    AirActions,
    AirUserMenu,
  },
  computed: mapState(['settings']),
}
</script>

<style lang="scss" module>
@import "./style.module.scss";
</style>
