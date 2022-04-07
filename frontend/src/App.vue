<template>
  <styleLoader />
  <localization />
</template>

<script>
import { computed, onMounted, watch } from 'vue'
import { useStore } from 'vuex'
import { useRoute, useRouter } from 'vue-router'
import qs from 'qs'
import Localization from '@/localization'
import StyleLoader from '@/styleLoader'

export default {
  name: 'App',
  components: { Localization, StyleLoader },
  setup() {
    const route = useRoute()
    const router = useRouter()
    const store = useStore()
    const acronym = computed(() => store.getters.settings.acronym)
    const routeTitle = computed(() => route.meta.title || store.state.settings.dynamicPageTitle)
    const currentRoute = computed(() => route)
    const authorized = computed(() => store.getters['user/user'].authorized)

    // watch page title change
    watch(
      [acronym, routeTitle],
      ([acronym, routeTitle]) => (document.title = `${acronym} | ${routeTitle}` || `${acronym}`),
    )

    // initial auth check
    onMounted(() => {
      store.dispatch('system/GET_USER_ACCESS_RIGHTS')
      store.dispatch('system/GET_ACCESS_RIGHTS')
      store.dispatch('user/LOAD_CURRENT_ACCOUNT')
    })

    // redirect if authorized and current page is login
    watch(authorized, authorized => {
      if (authorized && currentRoute.value.name !== 'viewerPdf') {
        const query = qs.parse(currentRoute.value.fullPath.split('?')[1], {
          ignoreQueryPrefix: true,
        })
        router.push(query.redirect || '/')
      }
    })
  },
}
</script>
