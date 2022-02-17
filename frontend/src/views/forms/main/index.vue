<template>
  <div>
    <a-card>
      <aapcr-form v-if="formId === `aapcr`" :form-id="formId" />
      <opcr-vp-form v-if="formId === `opcrvp`" :form-id="formId" />
      <opcr-template-form v-if="formId === `opcrtemplate`" :form-id="formId" />
    </a-card>
  </div>
</template>
<script>
import { defineComponent, ref, watch, onMounted } from "vue"
import { useRoute } from 'vue-router'
import AapcrForm from '@/components/Forms/Aapcr/Main'
import OpcrTemplateForm from "@/components/Forms/OpcrTemplate/Main";
import OpcrVpForm from '@/components/Forms/Vpopcr/Main'

export default defineComponent({
  components: {
    OpcrTemplateForm,
    AapcrForm,
    OpcrVpForm,
  },
  setup() {
    const formId = ref(null)
    const route = useRoute()

    // EVENTS
    onMounted(() => {
      setFormId(route.params.formId)
    })

    watch(route, to => {
      formId.value = to.params.formId
    })

    // METHODS
    const setFormId = id =>{
      formId.value = id
    }

    return {
      formId,
    }
  },
})
</script>
