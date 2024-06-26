<template>
  <a-card>
    <a-collapse v-model:activeKey="activeKey">
      <a-collapse-panel key="1" header="AAPCR" v-if="aapcrFormPermission || aapcrHeadPermission">
        <form-admin/>
      </a-collapse-panel>
      <a-collapse-panel key="2" header="OPCR (VP)" v-if=" opcrvpFormPermission || vpopcrHeadPermission">
        <form-admin-opcr-vp/>
      </a-collapse-panel>
      <a-collapse-panel key="3" header="OPCR" v-if="opcrFormPermission || opcrHeadPermission">
        <form-admin-opcr/>
      </a-collapse-panel>
      <a-collapse-panel key="4" header="CPCR">
        <p>CPCR</p>
      </a-collapse-panel>
    </a-collapse>
  </a-card>
</template>

<script>
import { defineComponent, onMounted, onUnmounted, ref, computed } from 'vue';
import { useStore } from 'vuex'
import FormAdmin from '@/components/SystemAdmin/AccessRights/Forms/aapcr'
import FormAdminOpcrVp from '@/components/SystemAdmin/AccessRights/Forms/opcrvp'
import FormAdminOpcr from '@/components/SystemAdmin/AccessRights/Forms/opcr'
import { usePermission } from '@/services/functions/permission'

export default defineComponent({
    name:"FormAdminTable",
    components: { FormAdmin, FormAdminOpcr, FormAdminOpcrVp },
    setup() {
      const store = useStore()

      // DATA
      const activeKey = ref(['1','2','3','4']);
      const aapcrFormId = 'aapcr'
      const opcrFormId = 'opcr'
      const vpopcrFormId = 'vpopcr'

      const permission = {
        listAapcr: ["adminPermission","ap-form", "apf-aapcr"],
        listOpcrvp: ["adminPermission","ap-form", "apf-opcrvr"],
        listOpcr: ["adminPermission","ap-form", "apf-opcr"],
      }

      const { aapcrFormPermission, opcrvpFormPermission, opcrFormPermission } = usePermission(permission)

      // COMPUTED
      const aapcrHeadPermission = computed(() => store.getters['system/permission'].aapcrHeadPermission)
      const opcrHeadPermission = computed(() => store.getters['system/permission'].opcrHeadPermission)
      const vpopcrHeadPermission = computed(() => store.getters['system/permission'].vpopcrHeadPermission)

      // EVENTS
      onMounted(() => {
        store.dispatch('system/CHECK_APCR_HEAD_PERMISSION', {
          payload: {
            pmaps_id: store.state.user.pmapsId,
            form_id: aapcrFormId,
          },
        })

        store.dispatch('system/CHECK_VPOPCR_HEAD_PERMISSION', {
          payload: {
            pmaps_id: store.state.user.pmapsId,
            form_id: vpopcrFormId,
          },
        })

        store.dispatch('system/CHECK_OPCR_HEAD_PERMISSION', {
          payload: {
            pmaps_id: store.state.user.pmapsId,
            form_id: opcrFormId,
          },
        })
      })

      onUnmounted(() => {
        resetPermission()
      })

      // METHODS
      const resetPermission = () => {
        aapcrFormPermission.value = false
        aapcrHeadPermission.value = false
        opcrvpFormPermission.value = false
        vpopcrHeadPermission.value = false
        opcrFormPermission.value = false
        opcrHeadPermission.value = false
      }

      return {
        activeKey,
        opcrFormPermission,
        aapcrFormPermission,
        aapcrHeadPermission,
        opcrHeadPermission,
        vpopcrHeadPermission,
        opcrvpFormPermission,
      }
    },
})
</script>

