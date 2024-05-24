<template>
  <div>
    <a-card>
      <aapcr-list v-if="formId === `aapcr`" :form-id="formId" />
      <vp-opcr-list v-if="formId === `vpopcr`" :form-id="formId" />
      <opcr-list v-if="formId === `opcr`" :form-id="formId" />
      <opcr-template-list v-if="formId === `opcrtemplate`" :form-id="formId" />
    </a-card>
  </div>
</template>
<script>
import { defineComponent, ref, watch, onMounted } from "vue"
import { useRoute } from 'vue-router'
import AapcrList from '@/components/Forms/Aapcr/List'
import VpOpcrList from '@/components/Forms/Vpopcr/List'
import OpcrList from '@/components/Forms/Opcr/List'
import OpcrTemplateList from '@/components/Forms/Opcr/Template/List'

export default defineComponent({
  components: {
    AapcrList,
    VpOpcrList,
    OpcrList,
    OpcrTemplateList,
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
