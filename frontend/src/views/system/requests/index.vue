<template>
  <div class="row">
    <div class="col-xl-12 col-lg-12">
      <div class="card">
        <div class="card-body">
          <template v-if="requestPermission">
            <a-tabs v-model:activeKey="activeKey" :animated="false">
              <a-tab-pane tab="Unpublish" key="1"><unpublish-requests v-if="activeKey === '1'"/></a-tab-pane>
            </a-tabs>
          </template>
          <div v-else><error403 /></div>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
import { defineComponent, ref } from "vue";
import UnpublishRequests from '@/components/SystemAdmin/Requests/unpublish'
import { usePermission } from '@/services/functions/permission'
import Error403 from '@/components/Errors/403'

export default defineComponent({
  components: { UnpublishRequests, Error403 },
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
