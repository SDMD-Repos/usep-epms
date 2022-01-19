<template>
  <div>
    <a-card>
      <aapcr-list v-if="formId === `aapcr`" :form-id="formId" />
      <vp-opcr-list v-if="formId === `opcrvp`" :form-id="formId" />
    </a-card>
  </div>
</template>
<script>
import { defineComponent, ref, watch, onMounted } from "vue"
import { useRoute } from 'vue-router'
import AapcrList from '@/components/Forms/Aapcr/List'
import VpOpcrList from '@/components/Forms/Vpopcr/List'

export default defineComponent({
  components: {
    AapcrList,
    VpOpcrList,
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
