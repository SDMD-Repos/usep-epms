<template>
  <div>
    <div class="mb-5">
      <a-breadcrumb>
        <a-breadcrumb-item>Home</a-breadcrumb-item>
        <a-breadcrumb-item><router-link to="/list/opcr">List</router-link></a-breadcrumb-item>
        <a-breadcrumb-item>OPCR Form</a-breadcrumb-item>
      </a-breadcrumb>
    </div>
    <div class="row">
      <div class="col-xl-12 col-lg-12">
        <div class="card">
          <div class="card-header card-header-flex">
            <div class="d-flex flex-column justify-content-center mr-auto">
              <h5 class="mb-0">Form</h5>
            </div>
          </div>
          <div class="card-body" style="text-align:justify">
            <div class="row">
              <div class="col-3 col-md-3 col-lg-2">
                <label>
                  <strong>
                    Fiscal Year :
                  </strong>
                </label>
              </div>
              <div class="col-12 col-md-4 col-lg-3">
                <a-select v-model="year"
                          placeholder="Select year"
                          style="width: 100%"
                          :disabled="editMode"
                          @change="checkDetails()">
                  <template v-for="(y, i) in years">
                    <a-select-option :value="y" :key="i">
                      {{ y }}
                    </a-select-option>
                  </template>
                </a-select>
              </div>
              <div class="w-100 mt-2"></div>
              <div class="col-3 col-md-3 col-lg-2">
                <label>
                  <strong>
                    Offices :
                  </strong>
                </label>
              </div>
              <div class="col-12 col-md-7 col-lg-5">
                <a-select v-model="officeId"
                          placeholder="Select an office"
                          style="width: 100%"
                          allow-clear
                          label-in-value
                          @change="checkDetails()">
                  <template v-for="(office, i) in personnelOffices">
                    <a-select-option :value="office.value" :key="i">
                      {{ office.title }}
                    </a-select-option>
                  </template>
                </a-select>
              </div>
            </div>
            <div class="mt-5">
              <template v-for="(category, key) in categories">
                <div :key="`${key}`">
                  <a-divider type="horizontal"><b>{{ category.name }}</b></a-divider>
                  <list-item :item-source="dataSource"
                             :function-id="category.key"
                             :drawer="drawer"
                             :office="officeId"
                             :categories="categories"
                             :targets-basis-list="targetsBasisList"
                             :counter="counter"
                             :enable-form="enableForm"
                             @update-counter="updateSourceCount"
                             @update-drawer-status="updateDrawerStatus"
                             @add-deleted-id="addDeletedId"
                             @add-targets-basis-item="addTargetsBasisItem"
                             @update-data-source="updateDataSource"/>
                </div>
              </template>
            </div>
<!--            <div class="mt-4" v-if="enableForm">-->
<!--              <a-row type="flex" justify="center" align="middle">-->
<!--                <a-col :xs="{ span: 10 }" :sm="{ span: 8 }" :md="{ span: 4 }" :lg="{ span: 3 }" >-->
<!--                  <a-button type="primary" ghost @click="validateForm(0)">{{ !editMode ? 'Save as draft' : 'Update' }}</a-button>-->
<!--                </a-col>-->
<!--                <a-col :xs="{ span: 6 }" :sm="{ span: 5 }" :md="{ span: 4, offset: 1 }" :lg="{ span: 3, offset: 1 }" v-if="!isFinalized">-->
<!--                  <a-button type="primary" @click="validateForm(1)">Finalize</a-button>-->
<!--                </a-col>-->
<!--              </a-row>-->
<!--            </div>-->
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
import { mapState } from 'vuex'
import IndexMixin from '@/services/formMixins'
import ListItem from './partials/item'
// import { Modal } from 'ant-design-vue'
import * as apiForm from '@/services/mainForms/ocpcr'

export default {
  title: 'OPCR Form',
  mixins: [IndexMixin],
  data() {
    return {
      officeId: undefined,
      drawer: '',
      counter: 0,
    }
  },
  computed: {
    ...mapState({
      personnelOffices: state => state.external.personnelOffices,
      categories: state => state.formSettings.functions,
    }),
  },
  created() {
    this.onLoad()
  },
  methods: {
    onLoad() {
      this.$store.dispatch('external/FETCH_OFFICES_BY_PERSONNEL', { payload: { formId: this.formId } })
      this.$store.dispatch('formSettings/FETCH_FUNCTIONS')
    },
    checkDetails() {
      this.loadDetails()
    },
    loadDetails() {
      const { year, officeId, formId } = this
      this.enableForm = false
      this.$store.commit('ocpcr/SET_STATE', {
        loading: true,
      })
      const payload = {
        year: year,
        officeId: officeId.key,
        formId: formId,
      }
      const getVpOpcrDetailsByOffice = apiForm.getVpOpcrDetailsByOffice
      getVpOpcrDetailsByOffice(payload).then(response => {
        if (response) {
          console.log(response)
        }
        this.$store.commit('ocpcr/SET_STATE', {
          loading: false,
        })
      })
    },
  },
  components: {
    ListItem,
  },
}
</script>
