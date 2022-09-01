<template>
  <a-spin :spinning="loading" tip="Fetching data in HRIS...">
    <a-card v-if="allowedForms.length">
      <a-tabs v-model:activeKey="activeKey" :animated="false">
        <template v-for="(form, index) in allowedForms" :key="index">
          <a-tab-pane :tab="typeof form.form === 'undefined' ? form.abbreviation : form.form.abbreviation"><signatory-form v-if="activeKey === index" :form-name="typeof form.form === 'undefined' ? form.id : form.form.id" /></a-tab-pane>
        </template>
      </a-tabs>
    </a-card>
<!--    <a-card v-else-if="formsByPermission.length ">-->
<!--      <a-tabs v-model:activeKey="activeKey" :animated="false">-->
<!--        <template v-for="(form, index) in formsByPermission" :key="index">-->
<!--          <a-tab-pane :tab="form.abbreviation"><signatory-form v-if="activeKey === index" :form-name="form.id" /></a-tab-pane>-->
<!--        </template>-->
<!--      </a-tabs>-->
<!--    </a-card>-->
    <a-card v-else><error403 /></a-card>
  </a-spin>
</template>
<script>
import SignatoryForm from '@/components/Manager/Signatory/Main'
import {defineComponent, ref, onMounted, onBeforeMount, computed, watch} from 'vue'
import { useStore } from 'vuex'
import { usePermission } from '@/services/functions/permission'
import Error403 from '@/components/Errors/403'
import {cloneDeep} from "lodash";

export default defineComponent({
  name:"SignatoryMain",
  components: { SignatoryForm, Error403 },
  setup() {
    const store = useStore()
    const allowedForms = ref({})

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

    watch(() => [formsByPermission.value, userForms.value], ([formsByPermission, userForms]) => {
      let clonedFormsByPermission = cloneDeep(formsByPermission)
      if (clonedFormsByPermission && Object.keys(clonedFormsByPermission).length > 0){
        if (userForms && Object.keys(userForms).length > 0)
          for (let frm of userForms)
            if (clonedFormsByPermission.filter(datum => datum.id === frm.form.id).length === 0) clonedFormsByPermission.push(frm)
        allowedForms.value = clonedFormsByPermission
      }else allowedForms.value = userForms

    })

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
      allowedForms,
    }
  },
})
</script>
