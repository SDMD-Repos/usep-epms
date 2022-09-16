<template>
  <div
    :class="{
      [$style.menu]: true,
      [$style.white]: settings.menuColor === 'white',
      [$style.gray]: settings.menuColor === 'gray',
      [$style.dark]: settings.menuColor === 'dark',
    }"
  >
    <div :class="$style.logoContainer">
      <div :class="$style.logo">
        <router-link to="/dashboard">
          <img class="mr-2" :src="`${mainStore.imagesPath}USeP Logo.png`" alt="USeP Logo" height="40">
        </router-link>
        <div :class="$style.name">{{ settings.acronym }}</div>
        <div :class="$style.descr" class="text-capitalize">
          {{ settings.logo }}
        </div>
      </div>
    </div>
    <div :class="$style.navigation">
      <a-menu :mode="'horizontal'" :selected-keys="selectedKeys" @click="handleClick">
        <template v-for="item in menuData">
          <template v-if="!item.roles || item.roles.includes(user.role)">
            <item
              v-if="!item.children && !item.category"
              :key="item.key"
              :menu-info="item"
              :styles="$style"
            />
            <sub-menu v-if="item.children" :key="item.key" :menu-info="item" :styles="$style" />
          </template>
        </template>
      </a-menu>
    </div>
    <div class="mr-4 mt-3" v-if="routeName !== 'main.form'">
      <a-dropdown>
        <setting-outlined :style="{ fontSize: '16px' }" />
        <template #overlay>
          <a-menu @click="handleMenuSettingsClick">
            <a-menu-item-group>
              <template #title>Menu Layout</template>
              <a-menu-item key="left">
                <vertical-right-outlined />
                Vertical
              </a-menu-item>
            </a-menu-item-group>
          </a-menu>
        </template>
      </a-dropdown>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, watch, computed } from 'vue'
import { useStore } from 'vuex'
import { useRoute } from 'vue-router'
import { default as localStore } from 'store'
import find from 'lodash/find'
import { getMenuData } from '@/services/menu'
import SubMenu from './partials/submenu'
import Item from './partials/item'
import { SettingOutlined, VerticalRightOutlined } from '@ant-design/icons-vue';

export default {
  name: 'MenuTop',
  components: { SubMenu, Item, SettingOutlined, VerticalRightOutlined },
  setup() {
    const store = useStore()
    const route = useRoute()
    const menuData = computed(() => getMenuData)
    const selectedKeys = ref([])
    const openKeys = ref([])
    const settings = computed(() => store.getters.settings)
    const isMenuCollapsed = computed(() => store.getters.settings.isMenuCollapsed)
    const menuLayoutType = computed(() => store.getters.settings.menuLayoutType)
    const user = computed(() => store.getters['user/user'])
    const pathname = computed(() => route.pathname)
    const routeName = computed(() => route.name)
    const mainStore = computed(() => store.getters.mainStore)

    const onCollapse = (collapsed, type) => {
      const value = !settings.value.isMenuCollapsed
      store.commit('CHANGE_SETTING', { setting: 'isMenuCollapsed', value })
    }

    const handleClick = e => {
      if (e.key === 'settings') {
        store.commit('CHANGE_SETTING', {
          setting: 'isSettingsOpen',
          value: true,
        })
        return
      }
      localStore.set('app.menu.selectedKeys', [e.key])
      selectedKeys.value = [e.key]
    }

    const handleOpenChange = openKeys => {
      localStore.set('app.menu.openedKeys', openKeys)
      openKeys.value = openKeys
    }

    const setSelectedKeys = () => {
      const flattenItems = (items, key) =>
        items.reduce((flattenedItems, item) => {
          flattenedItems.push(item)
          if (Array.isArray(item[key])) {
            return flattenedItems.concat(flattenItems(item[key], key))
          }
          return flattenedItems
        }, [])
      const selectedItem = find(flattenItems(menuData.value.concat(), 'children'), [
        'url',
        pathname,
      ])
      selectedKeys.value = selectedItem ? [selectedItem.key] : []
    }

    const handleMenuSettingsClick = e => {
      store.commit('CHANGE_SETTING', { setting: 'menuLayoutType', value: e.key })
    }

    onMounted(() => {
      openKeys.value = localStore.get('app.menu.openedKeys') || []
      selectedKeys.value = localStore.get('app.menu.selectedKeys') || []
      setSelectedKeys()
    })

    watch(pathname, () => setSelectedKeys())
    watch(isMenuCollapsed, () => (openKeys.value = []))

    return {
      menuData,
      selectedKeys,
      openKeys,
      settings,
      user,
      mainStore,
      menuLayoutType,
      routeName,
      onCollapse,
      handleClick,
      handleOpenChange,
      handleMenuSettingsClick,
    }
  },
}
</script>

<style lang="scss" module>
@import './style.module.scss';
</style>
