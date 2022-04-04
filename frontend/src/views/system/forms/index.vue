<template>

 <a-collapse v-model:activeKey="activeKey">
    <a-collapse-panel key="1" header="AAPCR" :disabled="!aapcrFormPermission && !aapcrHeadPermission">
         <form-admin/>
    </a-collapse-panel>
    <a-collapse-panel key="2" header="OPCR (VPs)" >
      <form-admin-opcr-vp/>
    </a-collapse-panel>
    <a-collapse-panel key="3" header="OPCR" :disabled="!opcrFormPermission && !opcrHeadPermission">
      <form-admin-opcr/>
    </a-collapse-panel>
    <a-collapse-panel key="4" header="CPCR">
      <p>CPCR</p>
    </a-collapse-panel>
   
  </a-collapse>

</template>


<script>
import { computed, defineComponent, onMounted, ref  } from 'vue';
import { useStore } from 'vuex'
import FormAdmin from '@/components/SystemAdmin/Forms/aapcr'

import FormAdminOpcr from '@/components/SystemAdmin/Forms/opcr'
import FormAdminOpcrVp from '@/components/SystemAdmin/Forms/opcrvp'
export default defineComponent({
    name:"FormAdminTable",
    components: {
        FormAdmin,
        FormAdminOpcr,
        FormAdminOpcrVp,
    },
    setup() {
      const store = useStore()
      const activeKey = ref([]);
      const aapcrFormId = 'aapcr'
      const opcrFormId = 'opcr'
      const opcrFormPermission = computed(() => store.getters['system/permission'].opcrFormPermission)
      const aapcrFormPermission = computed(() => store.getters['system/permission'].aapcrFormPermission)
      const aapcrHeadPermission = computed(() => store.getters['system/permission'].aapcrHeadPermission)
      const opcrHeadPermission = computed(() => store.getters['system/permission'].opcrHeadPermission)
      onMounted(() => {
        const opcrFormPermissions = [
          "adminPermission", //ACCESS PERMISSION
          "ap-form", //FORM
          "apf-opcr", //Set assigned office head to each OPCR
        ]
        store.dispatch('system/CHECK_OPCR_FORM_PERMISSION', { payload: opcrFormPermissions })

        const aapcrFormPermissions = [
          "adminPermission", //ACCESS PERMISSION
          "ap-form", //FORM
          "apf-aapcr", //Set assigned office head to each OPCR
        ]
        store.dispatch('system/CHECK_AAPCR_FORM_PERMISSION', { payload: aapcrFormPermissions })
        store.dispatch('system/CHECK_APCR_HEAD_PERMISSION', {
                                                          payload: {
                                                                    pmaps_id: store.state.user.pmapsId,
                                                                    form_id:aapcrFormId,
                                                                     },
                                                            })
        store.dispatch('system/CHECK_OPCR_HEAD_PERMISSION', {
          payload: {
            pmaps_id: store.state.user.pmapsId,
            form_id:opcrFormId,
          },
        })

      })

      return {
        activeKey,
        opcrFormPermission,
        aapcrFormPermission,
        aapcrHeadPermission,
        opcrHeadPermission,
      }

    },
})
</script>
