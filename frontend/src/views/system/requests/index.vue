<template>
  <div v-if="requestPermission">
    <div class="row">
      <div class="col-xl-12 col-lg-12">
        <div class="card">
          <div class="card-body">
            <a-tabs v-model:activeKey="activeKey" :animated="false">
              <a-tab-pane tab="Unpublish" key="1"><unpublish-requests v-if="activeKey === '1'"/></a-tab-pane>
            </a-tabs>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div v-else><span>You have no permission to access this page.</span></div>
</template>
<script>
import { defineComponent, ref } from "vue";
import UnpublishRequests from '@/components/SystemAdmin/Requests/unpublish'
import { usePermission } from '@/services/functions/permission'

export default defineComponent({
  components: { UnpublishRequests },
  setup() {

    const permission = { request: [ "adminRequests" ] }
    const { requestPermission } = usePermission(permission)
    return {
      activeKey: ref('1'),
      requestPermission,
    }
  },
})
</script>
