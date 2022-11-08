<template>
  <a-card>
    <a-tabs v-model:activeKey="activeKey">
      <a-tab-pane key="1" tab="Ratings"><rating v-if="activeKey === '1'"/></a-tab-pane>
      <a-tab-pane key="2" tab="Items"><items v-if="activeKey === '2'"/></a-tab-pane>
    </a-tabs>
  </a-card>
</template>
<script>
import { defineComponent, ref } from 'vue'
import Items from '@/components/Manager/Measures/Items'
import Rating from '@/components/Manager/Measures/Rating'

export default defineComponent({
  components: {
    Items, Rating,
  },
  setup() {
    return {
      activeKey: ref('1'),
    };
  },
})
</script>
