<template>
  <div>
    <a-card>
      <aapcr-list v-if="formId === `aapcr`" :form-id="formId" />
    </a-card>
  </div>
</template>
<script>
import { defineComponent, ref, watch, onMounted } from "vue"
import { useRoute } from 'vue-router'
import AapcrList from '@/components/Forms/Aapcr/aapcrList'

export default {
  components: {
    AapcrList,
  },
  setup() {
    const formId = ref(null)
    const route = useRoute()

    // EVENTS
    onMounted(() => {
      setFormId(route.params.formId)
    })

    watch(route, to => {
      formId.value = to.params.formid
    })

    // METHODS
    const setFormId = id =>{
      formId.value = id
    }

    return {
      formId,
    }
  },
}
</script>
