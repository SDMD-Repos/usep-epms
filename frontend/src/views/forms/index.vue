<template>
  <div>
    <aapcr-form :form-id="formId" v-if="formId === 'aapcr'"/>
    <vp-opcr-form :form-id="formId" v-if="formId === 'opcrvp'"/>
    <ocpcr-form :form-id="formId" v-if="formId === 'opcr'"/>
  </div>
</template>
<script>
import AapcrForm from '@/components/system/Forms/Aapcr/'
import VpOpcrForm from '@/components/system/Forms/Vpopcr/'
import OcpcrForm from '@/components/system/Forms/Ocpcr/'

export default {
  components: {
    AapcrForm,
    VpOpcrForm,
    OcpcrForm,
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
