<template>
  <div>
    <aapcr-form :form-id="formId" v-if="formId === 'aapcr'"/>
    <vp-opcr-form :form-id="formId" v-if="formId === 'opcrvp'"/>
  </div>
</template>
<script>
import AapcrForm from '@/components/system/Forms/Aapcr/'
import VpOpcrForm from '@/components/system/Forms/Vpopcr/'

export default {
  components: {
    AapcrForm,
    VpOpcrForm,
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
