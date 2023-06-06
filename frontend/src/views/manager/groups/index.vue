<template>
  <a-card>
    <a-tabs v-model:activeKey="activeKey" :animated="false">
      <a-tab-pane tab="Personnel" key="1"><personnel-groups /></a-tab-pane>
      <a-tab-pane tab="Offices" key="2"><office-groups /></a-tab-pane>
    </a-tabs>

  </a-card>
</template>

<script>

import PersonnelGroups from '@/components/Manager/Groups/Personnel'
import OfficeGroups from '@/components/Manager/Groups/Offices'

import { ref } from "vue";

export default {
  components: {
    PersonnelGroups,
    OfficeGroups,
  },
  setup() {
    return {
      activeKey: ref('1'),
    }
  },
}
</script>
