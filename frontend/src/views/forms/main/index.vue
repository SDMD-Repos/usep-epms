<template>
  <div>
    <a-card>
      <aapcr-form v-if="formId === `aapcr`" :form-id="formId" />
      <opcr-vp-form v-if="formId === `vpopcr`" :form-id="formId" />
      <opcr-form v-if="formId === `opcr`" :form-id="formId" />
      <opcr-template-form v-if="formId === `opcrtemplate`" :form-id="formId" />
    </a-card>
  </div>
</template>
<script>
import { defineComponent, ref, watch, onMounted } from "vue"
import { useRoute } from 'vue-router'
import AapcrForm from '@/components/Forms/Aapcr/Main'
import OpcrVpForm from '@/components/Forms/Vpopcr/Main'
import OpcrForm from '@/components/Forms/Opcr/Main'
import OpcrTemplateForm from "@/components/Forms/Opcr/Template/Main";

export default defineComponent({
  components: {
    AapcrForm,
    OpcrVpForm,
    OpcrForm,
    OpcrTemplateForm,
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
      console.log(to.params, "Hello")
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
