<template>

 <a-collapse v-model:activeKey="activeKey">
    <a-collapse-panel key="1" header="AAPCR" :disabled="!isCreate && !allAccess">
         <form-admin/>
    </a-collapse-panel>
    <a-collapse-panel key="2" header="OPCR (VPs)" >
      <p>OPCR (VPs)</p>
    </a-collapse-panel>
    <a-collapse-panel key="3" header="OPCR" :disabled="!opcrFormPermission">
      <form-admin-opcr/>
    </a-collapse-panel>
    <a-collapse-panel key="4" header="CPCR">
      <p>CPCR</p>
    </a-collapse-panel>
    <a-collapse-panel key="5" header="IPCR">
      <p>IPCR</p>
    </a-collapse-panel>
  </a-collapse>

</template>


<script>
import { computed, defineComponent, onMounted, ref  } from 'vue';
import { useStore } from 'vuex'
import FormAdmin from '@/components/SystemAdmin/Forms/aapcr'
import { usePermissionAccessRights } from '@/services/functions/permission/accessrights/forms'
import FormAdminOpcr from '@/components/SystemAdmin/Forms/opcr'
export default defineComponent({
    name:"FormAdminTable",
    components: {
        FormAdmin,
        FormAdminOpcr,
    },
    setup() {
      const store = useStore()
      const activeKey = ref([]);
      const opcrFormPermission = computed(() => store.getters['system/permission'].opcrFormPermission)
      const {
          // DATA
        isCreate, allAccess,
          // METHODS

      } = usePermissionAccessRights()

      onMounted(() => {
        const opcrFormPermissions = [
          21, //ACCESS PERMISSION
          24, //FORM
          29, //Set assigned office head to each OPCR (VP)
        ]
        store.dispatch('system/CHECK_OPCR_FORM_PERMISSION', { payload: opcrFormPermissions })
      })

      return {
        activeKey,
        allAccess,
        isCreate,
        opcrFormPermission,
      }

    },
})
</script>
