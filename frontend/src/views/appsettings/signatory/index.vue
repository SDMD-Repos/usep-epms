<template>
  <div>
    <div class="row">
      <div class="col-xl-12 col-lg-12">
        <div class="card">
          <div class="card-header card-header-flex flex-column">
            <a-tabs defaultActiveKey="1" class="kit-tabs kit-tabs-bold" @change="callback">
              <template v-for="(form, index) in formList">
                <a-tab-pane :tab="form.abbreviation" :key="index + 1" />
              </template>
            </a-tabs>
          </div>
          <div class="card-body">
            <aapcr-signatory-form v-if="activeKey === 1" :formName="`aapcr`" key="1" />
            <aapcr-signatory-form v-if="activeKey === 2" :formName="`vpopcr`" key="2" />
            <aapcr-signatory-form v-if="activeKey === 3" :formName="`opcr`" key="3" />
            <aapcr-signatory-form v-if="activeKey === 4" :formName="`cpcr`" key="4" />
            <aapcr-signatory-form v-if="activeKey === 5" :formName="`ipcr`" key="5" />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
import { mapState } from 'vuex'
import AapcrSignatoryForm from '@/components/system/AppSettings/Signatory'

export default {
  components: {
    AapcrSignatoryForm,
  },
  computed: {
    ...mapState({
      formList: state => state.formSettings.forms,
    }),
  },
  data() {
    return {
      activeKey: 1,
    }
  },
  created() {
    this.$store.dispatch('formSettings/FETCH_ALL_FORMS')
  },
  methods: {
    callback: function (key) {
      this.activeKey = key
    },
  },
}
</script>
