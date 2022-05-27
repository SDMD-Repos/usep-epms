<template>
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
  <div v-else>You have no permission to access this page</div>
</template>
<script>
import SignatoryForm from '@/components/Manager/Signatory/Main'
import { defineComponent, ref, computed, onMounted } from 'vue'
import { useStore } from 'vuex'
// import { usePermission } from '@/services/functions/permission'
import { usePermission } from '../../../services/functions/permission'
export default defineComponent({
  components: {
    SignatoryForm,
  },
  setup() {
    const store = useStore()

    // COMPUTED
    const userForms = computed(() => store.getters['formManager/manager'].userFormAccess)
    const formsByPermission = computed(() => store.getters['formManager/manager'].formsByPermission)
    // EVENTS
    const permission = { listAapcr: [ "form", "f-aapcr" ],
                        listOpcrvp: ["form","f-opcrvp"] }
    const {
          // DATA
       allForms,
          // METHODS
      } = usePermission(permission)

      // console.log(allForms) 
    onMounted(() => {
      store.dispatch('formManager/FETCH_USER_FORM_ACCESS', {
        payload: { pmapsId: store.state.user.pmapsId },
      })

      store.dispatch('formManager/FETCH_ALL_FORMS_BY_PERMISSION', {payload: { allForms : allForms},
                                                            })
    
    })

    return {
      activeKey: ref(0),
      userForms,
      formsByPermission,
    }
  },
})
</script>
