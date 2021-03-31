<template>
  <a-layout>
    <a-layout-content>
      <air-sidebar />
      <!-- <air-support-chat /> -->
      <div
        :class="{
          [$style.container]: true,
          cui__layout__squaredBorders: settings.isSquaredBorders,
          cui__layout__cardsShadow: settings.isCardShadow,
          cui__layout__borderless: settings.isBorderless,
          [$style.white]: settings.authPagesColor === 'white',
          [$style.gray]: settings.authPagesColor === 'gray',
        }"
        :style="{backgroundImage: settings.authPagesColor === 'image' ? `url(resources/images/content/photos/1.jpg)` : 'none'}"
      >
        <div class="pt-5 pb-5 d-flex align-items-end mt-auto">
          <a href="#" :class="$style.logo">
            <div :class="$style.letter">U</div>
            <div :class="$style.name">{{settings.logoName}}</div>
            <div :class="$style.descr">{{settings.description}}</div>
          </a>
        </div>
        <div :class="$style.containerInner">
          <transition :name="settings.routerAnimation" mode="out-in">
            <router-view />
          </transition>
        </div>
        <div class="mt-auto pb-5 pt-5">
          <div class="text-center">
            Copyright © 2021 University of Southeastern Philippines |
            <a
              href="https://www.usep.edu.ph/usep-data-privacy-statement/"
              target="_blank"
              rel="noopener noreferrer"
            >Privacy Policy</a>
          </div>
        </div>
      </div>
    </a-layout-content>
  </a-layout>
</template>

<script>
import { mapState } from 'vuex'
import AirSidebar from '@/@airui/layout/Sidebar'

export default {
  name: 'AuthLayout',
  components: { AirSidebar },
  computed: mapState(['settings']),
}
</script>

<style lang="scss" module>
@import "./style.module.scss";
</style>
