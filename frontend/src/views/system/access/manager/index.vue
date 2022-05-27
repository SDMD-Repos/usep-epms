<template>
 <a-card style="width: 100%">
   <a-tabs v-model:activeKey="activeKey">
    <a-tab-pane key="1" tab="Manager"><system-admin v-if="activeKey === '1'"/></a-tab-pane>
    <a-tab-pane key="2" tab="Forms"><form-admin v-if="activeKey === '2'"/></a-tab-pane>
  </a-tabs>
  </a-card>
</template>

<script>
import { defineComponent,ref } from 'vue'
import SystemAdmin from '@/components/SystemAdmin/AccessRights/Manager'
import FormAdmin from '@/views/system/access/forms'

export default defineComponent({
   components: { SystemAdmin, FormAdmin },
  setup() {
     return {
      activeKey: ref('1'),
    }
  },
})
</script>
