<template>
  <div>
    <aapcr-list v-if="formId === `aapcr`" :form-id="formId"/>
    <vp-opcr-list v-if="formId === `opcrvp`" :form-id="formId"/>
  </div>
</template>

<script>
import AapcrList from '@/components/system/Forms/Aapcr/list'
import VpOpcrList from '@/components/system/Forms/Vpopcr/list'

export default {
  components: {
    AapcrList,
    VpOpcrList,
  },
  data() {
    return {
      formId: null,
    }
  },
  watch: {
    '$route'(to) {
      this.formId = to.params.formId
    },
  },
  created() {
    this.setFormId(this.$route.params.formId)
  },
  methods: {
    setFormId(formId) {
      this.formId = formId
    },
  },
}
</script>
