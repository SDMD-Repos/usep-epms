<template>
  <a-card v-if="userForms.length">
    <a-tabs v-model:activeKey="activeKey" :animated="false">
      <template v-for="(form, index) in userForms" :key="index">
        <a-tab-pane :tab="form.form.abbreviation"><signatory-form v-if="activeKey === index" :form-name="form.form.id" /></a-tab-pane>
      </template>
    </a-tabs>
  </a-card>
  <div v-else>You have no permission to access this page</div>
</template>
<script>
import SignatoryForm from '@/components/Manager/Signatory/Main'
import { defineComponent, ref, computed, onMounted } from 'vue'
import { useStore } from 'vuex'

export default defineComponent({
  components: {
    SignatoryForm,
  },
  setup() {
    const store = useStore()

    // COMPUTED
    const userForms = computed(() => store.getters['formManager/manager'].userFormAccess)

    // EVENTS
    onMounted(() => {
      store.dispatch('formManager/FETCH_USER_FORM_ACCESS', {
        payload: { pmapsId: store.state.user.pmapsId },
      })
    })

    return {
      activeKey: ref(0),
      userForms,
    }
  },
})
</script>
