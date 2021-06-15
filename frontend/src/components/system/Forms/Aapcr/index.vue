<template>
  <div>
    <a-spin :spinning="loading" tip="Checking form availability...">
      <div class="mb-5">
        <a-breadcrumb>
          <a-breadcrumb-item>Home</a-breadcrumb-item>
          <a-breadcrumb-item><router-link to="/aapcr/list">List</router-link></a-breadcrumb-item>
          <a-breadcrumb-item>AAPCR Form</a-breadcrumb-item>
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
              <a-select v-model="year" placeholder="Select year" style="width: 200px" @change="checkFinalizedAapcr">
                <template v-for="(y, i) in years">
                  <a-select-option :value="y" :key="i">
                    {{ y }}
                  </a-select-option>
                </template>
              </a-select>
              <div class="mt-4" v-if="enableForm">
                <a-collapse v-model="activeKey" accordion>
                  <a-collapse-panel v-for="(category, key) in categories" :key="`${key}`" :header="category.name">
                    <item-list v-bind:key="`${key}`" :year="year"
                               :function-id="category.id"
                               :categories="categories"
                               :pi-source="dataSource"
                               :budget-list="budgetList"
                               :targets-basis-list="targetsBasisList"
                               :drawer="drawer"
                               :counter="counter"
                               @update-counter="updateSourceCount"
                               @add-budget-list-item="addBudgetListItem"
                               @add-targets-basis-item="addTargetsBasisItem"
                               @add-deleted-id="addDeletedId"
                               @update-data-source="updateDataSource"
                               @update-drawer-status="updateDrawerStatus"/>
                  </a-collapse-panel>
                </a-collapse>
              </div>
              <div class="mt-4" v-if="budgetList.length">
                <a-list item-layout="horizontal" :data-source="budgetList">
                  <a-list-item slot="renderItem" slot-scope="item, index">
                    <template v-if="item.editable">
                      <a-list-item-meta
                        description=""
                      >
                        <a slot="title">
                          <a-col :xs="{ span: 12 }" :sm="{ span: 12 }" :md="{ span: 8 }" :lg="{ span: 5 }">
                            {{ item.mainCategory.label }} :
                          </a-col>
                          <a-col :xs="{ span: 9, offset: 1 }" :sm="{ span: 8 }" :md="{ span: 8 }" :lg="{ span: 5 }">
                            <a-input-number v-model="item.categoryBudget" style="width: 100%"
                                            :formatter="value => `${value}`.replace(/\B(?=(\d{3})+(?!\d))/g, ',')"
                                            :parser="value => value.replace(/\$\s?|(,*)/g, '')"
                                            :min="0" />
                          </a-col>
                        </a>
                      </a-list-item-meta>
                      <a slot="actions" @click="updateBudget(index)">update</a>
                      <a slot="actions" @click="cancelEdit(index)">cancel</a>
                    </template>
                    <template v-else>
                    <span>
                    {{ item.mainCategory.label }} - <b>₱ {{ numbersWithCommas(item.categoryBudget) }}</b>
                  </span>
                      <template v-if="editingKey === ''" >
                        <a slot="actions" @click="editBudget(index)">edit</a>
                        <a slot="actions" @click="deleteBudget(index)">delete</a>
                      </template>
                    </template>
                  </a-list-item>
                </a-list>
              </div>
              <div class="mt-4" v-if="enableForm">
                <a-row type="flex" justify="center" align="middle">
                  <a-col :sm="{ span: 3 }" :md="{ span: 3 }" :lg="{ span: 2 }" >
                    <a-button type="primary" ghost @click="validateForm(0)">{{ !editMode ? 'Save as draft' : 'Update' }}</a-button>
                  </a-col>
                  <a-col :sm="{ span: 4, offset: 1 }" :md="{ span: 4, offset: 1 }" :lg="{ span: 4, offset: 1 }" v-if="!isFinalized">
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
import ItemList from './partials/itemList'
import { numbersWithCommas } from '@/services/filters'
import { Modal } from 'ant-design-vue'
import * as apiForm from '@/services/mainForms/aapcr'

export default {
  name: 'aapcr-form',
  props: ['formId'],
  components: {
    ItemList,
  },
  data() {
    return {
      aapcrId: null,
      year: new Date().getFullYear(),
      cachedYear: null,
      dataSource: [],
      budgetList: [],
      targetsBasisList: [],
      isFinalized: false,
      editMode: false,
      drawer: null,
      counter: 0,
      editingKey: '',
      edited: {},
      activeKey: '0',
      enableForm: false,
      deletedIds: [],
    }
  },
  computed: {
    ...mapState({
      categories: state => state.formSettings.functions,
      loading: state => state.aapcr.loading,
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
    this.aapcrId = this.$route.params.id
    if (this.aapcrId) {
      this.getFormDetails()
    } else {
      this.checkFinalizedAapcr()
    }
  },
  methods: {
    numbersWithCommas,
    onLoad() {
      this.$store.dispatch('formSettings/FETCH_FUNCTIONS')
    },
    getFormDetails() {
      const { aapcrId } = this
      this.$store.commit('aapcr/SET_STATE', {
        loading: true,
      })
      const fetchFormDetails = apiForm.fetchFormDetails
      fetchFormDetails(aapcrId).then(response => {
        if (response) {
          this.enableForm = true
          this.onLoad()

          this.year = response.year
          this.cachedYear = response.year
          this.dataSource = response.dataSource
          this.budgetList = response.budgetList
          this.targetsBasisList = response.targetsBasisList
          this.isFinalized = response.isFinalized
          this.editMode = true
        }
        this.$store.commit('aapcr/SET_STATE', {
          loading: false,
        })
      })
    },
    checkFinalizedAapcr() {
      const { year, cachedYear } = this
      if (year !== cachedYear) {
        this.$store.commit('aapcr/SET_STATE', {
          loading: true,
        })
        const checkSavedForm = apiForm.checkSavedForm
        checkSavedForm(year).then(response => {
          if (response) {
            const { hasSaved } = response
            if (hasSaved) {
              Modal.error({
                title: 'Unable to create an AAPCR for the year ' + this.year,
                content: 'Please check the AAPCR list or select a different year to create a new AAPCR',
              })
              if (this.editMode) {
                this.year = cachedYear
              } else {
                this.enableForm = false
              }
            } else {
              if (!this.editMode) {
                this.enableForm = true
                this.onLoad()
              }
            }
          }
          this.$store.commit('aapcr/SET_STATE', {
            loading: false,
          })
        })
      }
    },
    editBudget(index) {
      const editData = [...this.budgetList]
      const target = editData[index]
      if (target) {
        Object.assign(this.edited, this.budgetList[index])
        this.editingKey = index
        target.editable = true
        this.budgetList = editData
      }
    },
    cancelEdit(index) {
      const editData = [...this.budgetList]
      const target = editData[index]
      if (target) {
        this.editingKey = ''
        target.categoryBudget = this.edited.categoryBudget
        delete target.editable
        this.edited = {}
      }
    },
    updateBudget(index) {
      const editData = [...this.budgetList]
      const target = editData[index]
      if (target) {
        this.editingKey = ''
        delete target.editable
        this.budgetList = editData
        this.edited = {}
      }
    },
    deleteBudget(index) {
      const that = this
      Modal.confirm({
        title: 'Are you sure you want to delete this?',
        content: '',
        okText: 'Yes',
        cancelText: 'No',
        onOk() {
          that.budgetList.splice(index, 1)
        },
        onCancel() {},
      })
    },
    validateForm(isFinal) {
      const { dataSource, budgetList } = this
      const self = this
      if (!dataSource.length) {
        Modal.error({
          title: 'Unable to save the form',
          content: 'No PIs were added to the list',
        })
      } else if (!budgetList.length) {
        Modal.confirm({
          title: 'Budget for each program was not indicated. Do you want to proceed?',
          content: '',
          okText: 'Yes',
          cancelText: 'No',
          onOk() {
            self.handleSave(isFinal)
          },
          onCancel() {},
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
      const { dataSource, budgetList, year } = this
      const { editMode, deletedIds, aapcrId, isFinalized } = this
      const documentName = this.formId.toUpperCase() + '_' + year
      const details = {
        dataSource: dataSource,
        fiscalYear: year,
        documentName: documentName,
        programBudgets: budgetList,
      }
      if (!editMode) {
        details.isFinalized = isFinal
        this.$store.dispatch('aapcr/SAVE_AAPCR', { payload: details })
          .then(() => {
            this.$router.push({ name: 'aapcr.list' })
          })
      } else {
        const finalStatus = isFinal || isFinalized

        details.isFinalized = finalStatus
        details.deletedIds = deletedIds
        details.aapcrId = aapcrId
        this.$store.dispatch('aapcr/UPDATE_AAPCR', { payload: details })
          .then(() => {
            this.$router.push({ name: 'aapcr.list' })
          })
      }
    },

    // ItemList component events
    updateSourceCount(data) {
      this.counter = data
    },
    updateDataSource(data) {
      this.dataSource = data
    },
    addBudgetListItem(data) {
      this.budgetList.push(data)
    },
    addTargetsBasisItem(data) {
      this.targetsBasisList.push(data)
    },
    updateDrawerStatus(data) {
      this.drawer = data
    },
    addDeletedId(data) {
      this.deletedIds.push(data)
    },
  },
}
</script>
