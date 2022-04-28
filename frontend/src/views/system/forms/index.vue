<template>

 <a-collapse v-model:activeKey="activeKey">
    <a-collapse-panel key="1" header="AAPCR" :disabled="!aapcrFormPermission && !aapcrHeadPermission">
         <form-admin/>
    </a-collapse-panel>
    <a-collapse-panel key="2" header="OPCR (VPs)" :disabled=" !vpopcrHeadPermission">
      <form-admin-opcr-vp/>
    </a-collapse-panel>
    <a-collapse-panel key="3" header="OPCR" :disabled=" !opcrHeadPermission">
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

import { usePermission } from '@/services/functions/permission'

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
      // const opcrFormPermission = computed(() => store.getters['system/permission'].opcrFormPermission)
      const aapcrHeadPermission = computed(() => store.getters['system/permission'].aapcrHeadPermission)
      const opcrHeadPermission = computed(() => store.getters['system/permission'].opcrHeadPermission)
      const vpopcrHeadPermission = computed(() => store.getters['system/permission'].vpopcrHeadPermission)
      // const createVpOpcrPermission = computed(() => store.getters['system/permission'].createVpOpcrPermission)
      const permission ={
                        listAapcr: ["adminPermission","ap-form", "apf-aapcr"],
                      }
      const {
          // DATA
        aapcrFormPermission,
          // METHODS
      } = usePermission(permission)

      
      onMounted(() => {
      store.dispatch('system/CHECK_APCR_HEAD_PERMISSION', {
                                                          payload: {
                                                                    pmaps_id: store.state.user.pmapsId,
                                                                    form_id:aapcrFormId,
                                                                     },
                                                            })
      store.dispatch('system/CHECK_VPOPCR_HEAD_PERMISSION', { 
                                                          payload: {
                                                                    pmaps_id: store.state.user.pmapsId,
                                                                    form_id: 'vpopcr',
                                                                     },
                                                            })

      })

      return {
        activeKey,
        // opcrFormPermission,
        aapcrFormPermission,
        aapcrHeadPermission,
        opcrHeadPermission,
        vpopcrHeadPermission,
        // createVpOpcrPermission,
        
      }

    },
})
</script>
