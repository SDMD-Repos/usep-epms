<template>
  <div>
    <div class="row">
      <div class="col-xl-12 col-lg-12">
        <div class="card">
          <div class="card-body">
            <a-tabs v-model:activeKey="activeKey" :animated="false">
              <a-tab-pane tab="Functions" key="1"><functions-form v-if="activeKey === '1'"/></a-tab-pane>
            </a-tabs>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
import { defineComponent, ref } from "vue";

export default defineComponent({
  setup() {
    return {
      activeKey: ref('1'),
    }
  },
})
</script>
