<template>
  <div>
    <a-spin :spinning="loading" tip="Initializing data...">
      <div class="mb-5">
        <a-breadcrumb>
          <a-breadcrumb-item>Home</a-breadcrumb-item>
          <a-breadcrumb-item><router-link to="/list/opcrvp">List</router-link></a-breadcrumb-item>
          <a-breadcrumb-item>OPCR (VP) Form</a-breadcrumb-item>
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
                  <a-select v-model="year" placeholder="Select year" style="width: 100%">
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
                      VP Office :
                    </strong>
                  </label>
                </div>
                <div class="col-12 col-md-7 col-lg-5">
                  <a-select v-model="vpOffice"
                            placeholder="Select VP Office"
                            style="width: 100%"
                            @change="checkDetails()"
                            allow-clear
                            label-in-value>
                    <template v-for="(office, i) in vpOfficesList">
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
                               :vp-office="vpOffice"
                               :categories="categories"
                               :targets-basis-list="targetsBasisList"
                               :counter="counter"
                               @update-counter="updateSourceCount"
                               @update-drawer-status="updateDrawerStatus"
                               @add-targets-basis-item="addTargetsBasisItem"
                               @update-data-source="updateDataSource"/>
                  </div>
                </template>
              </div>
              <div class="mt-4" v-if="enableForm">
                <a-row type="flex" justify="center" align="middle">
                  <a-col :xs="{ span: 10 }" :sm="{ span: 8 }" :md="{ span: 4 }" :lg="{ span: 3 }" >
                    <a-button type="primary" ghost @click="validateForm(0)">{{ !editMode ? 'Save as draft' : 'Update' }}</a-button>
                  </a-col>
                  <a-col :xs="{ span: 6 }" :sm="{ span: 5 }" :md="{ span: 4, offset: 1 }" :lg="{ span: 3, offset: 1 }" v-if="!isFinalized">
                    <a-button type="primary" @click="validateForm(1)">Finalize</a-button>
                  </a-col>
                </a-row>
              </div>
            </div>
          </div>
        </div>
      </div>
    </a-spin>
  </div>
</template>
<script>
import { mapState } from 'vuex'
import ListItem from './partials/item'
import * as apiForm from '@/services/mainForms/opcrvp'
import IndexMixin from '@/services/formMixins'
import { Modal } from 'ant-design-vue'

export default {
  title: 'OPCR (VP) Form',
  mixins: [IndexMixin],
  data() {
    return {
      year: new Date().getFullYear(),
      aapcrId: null,
      vpOffice: undefined,
      activeKey: '0',
      drawer: '',
      dataSource: [],
      targetsBasisList: [],
      counter: 0,
    }
  },
  computed: {
    ...mapState({
      categories: state => state.formSettings.functions,
      loading: state => state.formSettings.loading,
      vpOfficesList: state => state.external.vpOffices,
    }),
    years() {
      const now = new Date().getFullYear()
      const min = 10
      const lists = []
      for (let i = now; i >= (now - min); i--) {
        lists.push(i)
      }
      return lists
    },
  },
  created() {
    this.onLoad()
  },
  methods: {
    onLoad() {
      this.$store.dispatch('external/FETCH_VP_OFFICES', { payload: { officesOnly: 1 } })
      this.$store.dispatch('formSettings/FETCH_FUNCTIONS')
      this.$store.dispatch('formSettings/FETCH_PROGRAMS')
      this.$store.dispatch('formSettings/FETCH_SUB_CATEGORIES')
      this.$store.dispatch('formSettings/FETCH_MEASURES')
      this.$store.dispatch('formSettings/FETCH_CASCADING_LEVELS')
    },
    resetDetails() {
      this.enableForm = false
      this.aapcrId = null
      this.dataSource = []
      this.targetsBasisList = []
    },
    checkDetails() {
      if (this.counter) {
        const that = this
        Modal.confirm({
          title: 'Changes were not yet saved',
          content: 'Do you still want to continue?',
          okText: 'Yes',
          cancelText: 'No',
          onOk() {
            that.loadDetails()
          },
          onCancel() {},
        })
      } else {
        if (typeof this.vpOffice !== 'undefined') {
          this.loadDetails()
        } else {
          this.resetDetails()
        }
      }
    },
    loadDetails() {
      const { year, vpOffice } = this
      this.enableForm = false
      this.$store.commit('opcrvp/SET_STATE', {
        loading: true,
      })
      const getAapcrDetailsByOffice = apiForm.getAapcrDetailsByOffice
      getAapcrDetailsByOffice(vpOffice.key, year).then(response => {
        if (response) {
          this.enableForm = true
          this.aapcrId = response.aapcrId
          this.dataSource = response.dataSource
          this.targetsBasisList = response.targetsBasisList
        }
        this.$store.commit('opcrvp/SET_STATE', {
          loading: false,
        })
      })
    },
    validateForm(isFinal) {
      const { dataSource } = this
      const self = this
      if (!dataSource.length) {
        Modal.error({
          title: 'Unable to save the form',
          content: 'No PIs were added to the list',
        })
      } else {
        let title = ''
        if (isFinal) {
          title = 'This will finalize and save your form'
        } else if (!this.editMode) {
          title = 'This will save your form as draft'
        } else {
          title = 'This will update your form'
        }
        Modal.confirm({
          title: title,
          content: 'Are you sure you want to proceed?',
          okText: 'Yes',
          cancelText: 'No',
          onOk() {
            self.handleSave(isFinal)
          },
          onCancel() {},
        })
      }
    },
    handleSave(isFinal) {
      const { dataSource, year } = this
      const { editMode, deletedIds, isFinalized } = this
      const { vpOffice, aapcrId } = this
      const details = {
        dataSource: dataSource,
        fiscalYear: year,
        isFinalized: isFinal,
        vpOffice: vpOffice,
        aapcrId: aapcrId,
      }
      if (!editMode) {
        details.isFinalized = isFinal
        this.$store.dispatch('opcrvp/SAVE', { payload: details })
          .then(() => {
            this.$router.push({ name: 'form.list', params: { formId: this.formId } })
          })
      } else {
        details.isFinalized = isFinal || isFinalized
        details.deletedIds = deletedIds
        details.aapcrId = aapcrId
        // this.$store.dispatch('opcrvp/UPDATE', { payload: details })
        //   .then(() => {
        //     this.$router.push({ name: 'form.list', params: { formId: this.formId } })
        //   })
      }
    },
  },
  components: {
    ListItem,
  },
}
</script>
