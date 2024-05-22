<template>
  <a-layout-sider
    :width="settings.leftMenuWidth"
    :collapsible="!settings.isMobileView"
    :collapsed="settings.isMenuCollapsed && !settings.isMobileView"
    :class="{
      [$style.menu]: true,
      [$style.white]: settings.menuColor === 'white',
      [$style.gray]: settings.menuColor === 'gray',
      [$style.dark]: settings.menuColor === 'dark',
      [$style.unfixed]: settings.isMenuUnfixed,
      [$style.shadow]: settings.isMenuShadow,
    }"
    @collapse="onCollapse"
  >
    <div
      :class="$style.menuOuter"
      :style="{
        width:
          settings.isMenuCollapsed && !settings.isMobileView
            ? '80px'
            : settings.leftMenuWidth + 'px',
        height:
          settings.isMobileView || settings.isMenuUnfixed
            ? 'calc(100% - 64px)'
            : 'calc(100% - 110px)',
      }"
    >
      <div :class="$style.logoContainer">
        <div :class="$style.logo">
          <router-link to="/dashboard">
            <img v-if="settings.isMenuCollapsed && !settings.isMobileView" :src="`${mainStore.imagesPath}USeP Logo.png`" alt="USeP Logo" height="40">
            <img v-else :src="settings.theme === 'default' ? `${mainStore.imagesPath}ePMS.png` : `${mainStore.imagesPath}ePMS-dark-2.png`" alt="ePMS Branding" height="37">
          </router-link>
          <!-- <div :class="$style.name">{{ settings.logo }}</div>
          <div :class="$style.descr" class="text-capitalize">{{ settings.description }}</div> -->
        </div>
      </div>
      <perfect-scrollbar :style="{ height: '100%' }">
        <a-menu
          v-model:open-keys="openKeys"
          :mode="'inline'"
          :selected-keys="selectedKeys"
          :inline-indent="15"
          :class="$style.navigation"
          @click="handleClick"
          @openChange="handleOpenChange"
        >
          <template v-for="(item, index) in menuData">
            <template v-if="!item.roles || item.roles.includes(user.role)">
              <a-menu-item-group v-if="item.category" :key="index">
                <template #title>
                  {{ item.title }}
                </template>
              </a-menu-item-group>
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
        <!-- <div :class="$style.banner">
          <p>More components, more style, more themes, and premium support!</p>
          <a
            href="https://themeforest.net/item/clean-ui-react-admin-template/21938700"
            target="_blank"
            rel="noopener noreferrer"
            class="btn btn-sm btn-success btn-rounded px-3"
            >Buy Bundle</a
          >
        </div> -->
      </perfect-scrollbar>
    </div>
  </a-layout-sider>
</template>

<script>
import { ref, onMounted, computed, watch } from 'vue'
import { useStore } from 'vuex'
import { useRoute } from 'vue-router'
import { default as localStore } from 'store'
import find from 'lodash/find'
import { getMenuData } from '@/services/menu'
import SubMenu from './partials/submenu'
import Item from './partials/item'
import { usePermission } from '@/services/functions/permission'


export default {
  name: 'MenuLeft',
  components: { SubMenu, Item },
  setup() {
    const store = useStore()
    const route = useRoute()
    const permission = { adminPermission: ["adminPermission"] }
    const { adminPermissionRef } = usePermission(permission)
    const menuData = computed(() => filterMenuData(getMenuData))
    const selectedKeys = ref([])
    const openKeys = ref([])
    const settings = computed(() => store.getters.settings)
    const isMenuCollapsed = computed(() => store.getters.settings.isMenuCollapsed)
    const user = computed(() => store.getters['user/user'])
    const pathname = computed(() => route.path)
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
        pathname.value,
      ])
      selectedKeys.value = selectedItem ? [selectedItem.key] : []
    }

    onMounted(() => {
      openKeys.value = localStore.get('app.menu.openedKeys') || []
      selectedKeys.value = localStore.get('app.menu.selectedKeys') || []
      setSelectedKeys()
    })

    watch(pathname, () => setSelectedKeys())
    watch(isMenuCollapsed, () => (openKeys.value = []))

    function filterMenuData(menuData) {
      if(adminPermissionRef.value==true) {
        return menuData;
      }else {
        const filteredMenu = [];
        for (let i = 0; i < menuData.length; i++) {
          const item = menuData[i];
          if (item.title === 'System Admin' || item.title === 'Access Rights') {
            // If it's the "System Admin" skip it and its children
            while (i < menuData.length && menuData[i].title !== 'Access Rights') {
              // Skip children of "Access Rights" category
              i++;
            }
          } else {
            // Add non-"System Admin" category or non-category item
            filteredMenu.push(item);
          }
        }
        return filteredMenu;
      }
    }

    return {
      menuData,
      selectedKeys,
      openKeys,
      settings,
      user,
      onCollapse,
      handleClick,
      handleOpenChange,
      mainStore,
      adminPermissionRef,
    }
  },
}
</script>

<style lang="scss" module>
@import './style.module.scss';
</style>
