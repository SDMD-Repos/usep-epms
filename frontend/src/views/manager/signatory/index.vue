<template>
  <a-card v-if="formList.length">
    <a-tabs v-model:activeKey="activeKey" :animated="false">
      <template v-for="(form, index) in formList" :key="index">
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
    const formList = computed(() => store.getters['formManager/manager'].forms)

    // EVENTS
    onMounted(() => {
      store.dispatch('formManager/FETCH_ALL_FORMS',{payload: {
                                                              pmaps_id: store.state.user.pmapsId,
                                                            },
                                                    })
    })

    return {
      activeKey: ref(0),
      formList,
    }
  },
})
</script>
