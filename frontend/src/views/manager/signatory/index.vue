<template>
  <a-spin :spinning="loading" tip="Fetching data in HRIS...">
    <a-card v-if="userForms.length">
      <a-tabs v-model:activeKey="activeKey" :animated="false">
        <template v-for="(form, index) in userForms" :key="index">
          <a-tab-pane :tab="form.form.abbreviation"><signatory-form v-if="activeKey === index" :form-name="form.form.id" /></a-tab-pane>
        </template>
      </a-tabs>
    </a-card>
    <a-card v-else-if="formsByPermission.length ">
      <a-tabs v-model:activeKey="activeKey" :animated="false">
        <template v-for="(form, index) in formsByPermission" :key="index">
          <a-tab-pane :tab="form.abbreviation"><signatory-form v-if="activeKey === index" :form-name="form.id" /></a-tab-pane>
        </template>
      </a-tabs>
    </a-card>
    <a-card v-else><error403 /></a-card>
  </a-spin>
</template>
<script>
import SignatoryForm from '@/components/Manager/Signatory/Main'
import { defineComponent, ref, onMounted, onBeforeMount, computed } from 'vue'
import { useStore } from 'vuex'
import { usePermission } from '@/services/functions/permission'
import Error403 from '@/components/Errors/403'

export default defineComponent({
  components: { SignatoryForm, Error403 },
  setup() {
    const store = useStore()

    // COMPUTED
    const userForms = computed(() => store.getters['formManager/manager'].userFormAccess)
    const formsByPermission = computed(() => store.getters['formManager/manager'].formsByPermission)
    const loading = computed(() => {
      return store.getters['external/external'].loading || store.getters['formManager/manager'].loading
    })

    const permission = {
      listAapcr: [ "form", "f-aapcr" ],
      listOpcrvp: ["form","f-opcrvp"],
      listOpcr: ["form","f-opcr"],
      listCpcr: ["form","f-cpcr"],
      listIpcr: ["form","f-ipcr"],
    }

    const { allForms } = usePermission(permission)

    // EVENTS
    onBeforeMount(() => {
      store.dispatch('formManager/FETCH_USER_FORM_ACCESS', {
        payload: { pmapsId: store.state.user.pmapsId },
      })

      store.dispatch('formManager/FETCH_ALL_FORMS_BY_PERMISSION', { payload: { allForms : allForms } })
    })

    onMounted( () => {

    })

    return {
      activeKey: ref(0),
      userForms,
      formsByPermission,
      loading,
    }
  },
})
</script>
