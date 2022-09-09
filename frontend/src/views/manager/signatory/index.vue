<template>
  <a-spin :spinning="loading" tip="Fetching data in HRIS...">
    <a-card v-if="allowedForms.length">
      <a-tabs v-model:activeKey="activeKey" :animated="false">
        <template v-for="(form, index) in allowedForms" :key="index">
          <a-tab-pane :tab="typeof form.form === 'undefined' ? form.abbreviation : form.form.abbreviation">
            <signatory-form v-if="activeKey === index"
                            :form-name="typeof form.form === 'undefined' ? form.id : form.form.id"
                            :form-access="formAccess"/>
          </a-tab-pane>
        </template>
      </a-tabs>
    </a-card>
    <a-card v-else><error403 v-if="!loading"/></a-card>
  </a-spin>
</template>
<script>
import { defineComponent, ref, onBeforeMount, computed } from 'vue'
import { useStore } from 'vuex'
import { usePermission } from '@/services/functions/permission'
import SignatoryForm from '@/components/Manager/Signatory/Main'
import Error403 from '@/components/Errors/403'

export default defineComponent({
  name: "SignatoryMain",
  components: { SignatoryForm, Error403 },
  setup() {
    const store = useStore()

    const permission = {
      listAapcr: [ "form", "f-aapcr" ],
      listOpcrvp: ["form","f-opcrvp"],
      listOpcr: ["form","f-opcr", 'fo-formlist'],
      listCpcr: ["form","f-cpcr"],
      listIpcr: ["form","f-ipcr"],
    }

    const { allForms, formAccess } = usePermission(permission)

    // COMPUTED
    const forms = computed(() => store.getters['formManager/manager'].forms)
    const loading = computed(() => {
      return store.getters['external/external'].loading || store.getters['formManager/manager'].loading
    })

    const allowedForms = computed(() => {
      const extractFormId = formAccess.value.map(({ form_id }) => form_id)
      const result = [...new Set([...allForms, ...extractFormId])]

      return forms.value.filter(i => { return result.includes(i.id) })
    })

    // EVENTS
    onBeforeMount(() => {
      store.dispatch('formManager/FETCH_ALL_FORMS')
    })

    return {
      activeKey: ref(0),
      loading,
      allowedForms,
      formAccess,
    }
  },
})
</script>
