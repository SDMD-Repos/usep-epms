<template>
  <div>
    <div class="air__utils__heading">
      <h5>AAPCR</h5>
    </div>
    <div class="row">
      <div class="col-xl-12 col-lg-12">
        <div class="card">
          <div class="card-header card-header-flex">
            <div class="d-flex flex-column justify-content-center mr-auto">
              <h5 class="mb-0">Create New</h5>
            </div>
          </div>
          <div class="card-body" style="text-align:justify">
            <a-select v-model="year" placeholder="Select year" style="width: 200px">
              <template v-for="(y, i) in years">
                <a-select-option :value="y" :key="i">
                  {{ y }}
                </a-select-option>
              </template>
            </a-select>
            <div class="mt-4">
              <a-tabs defaultActiveKey="0" @change="tabCallback">
                <template v-for="(category, key) in categories">
                  <a-tab-pane :key="`${key}`" :tab="category.name">
                    <a-button @click="showDrawer">Click me</a-button>
                    <div :ref="saveContainer">
                      <a-drawer
                        :key="category.id"
                        :get-container="sampleRef"
                        title="Basic Drawer"
                        placement="right"
                        :closable="false"
                        :visible="visible"
                        :after-visible-change="afterVisibleChange"
                        @close="onClose"
                      >
                        <p v-for="f in filteredSubCategory(category.id)" v-bind:key="f.id">
                          {{ f.name }}
                        </p>
                      </a-drawer>
                    </div>
                  </a-tab-pane>
                </template>
              </a-tabs>
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
                  </template>
                  <template v-else>
                    <span>
                    {{ item.mainCategory.label }} - <b>₱ {{ numbersWithCommas(item.categoryBudget) }}</b>
                  </span>
                    <a slot="actions" @click="editBudget(index)">edit</a>
                    <a slot="actions" @click="deleteBudget(index)">delete</a>
                  </template>
                </a-list-item>
              </a-list>
            </div>
            <div class="mt-4">
              <a-row type="flex" justify="center" align="middle">
                <a-col :lg="{ span: 3 }">
                  <a-button type="primary" ghost @click="handleSave(0)">Save as draft</a-button>
                </a-col>
                <a-col :lg="{ span: 4 }">
                  <a-button type="primary" @click="handleSave(1)">Finalize</a-button>
                </a-col>
              </a-row>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
import { mapState } from 'vuex'
import { numbersWithCommas } from '@/services/filters'
import { Modal } from 'ant-design-vue'

export default {
  props: ['formId'],
  components: {
  },
  data() {
    return {
      sampleRef: () => {

      },
      year: new Date().getFullYear(),
      dataSource: [],
      budgetList: [],
      targetsBasisList: [],
      activeKey: '0',
      visible: false,
    }
  },
  computed: {
    ...mapState({
      categories: state => state.formSettings.functions,
      subCategoryList: state => state.formSettings.subCategories,
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
    numbersWithCommas,
    tabCallback(key) {
      this.activeKey = key
    },
    afterVisibleChange(val) {
      console.log('visible', val)
    },
    showDrawer() {
      this.visible = true
    },
    onClose() {
      this.visible = false
    },
    filteredSubCategory(functionId) {
      return this.subCategoryList.filter((i) => {
        return i.category_id === functionId && i.parent_id === null
      })
    },
    onLoad() {
      this.$store.dispatch('formSettings/FETCH_FUNCTIONS')
      this.$store.dispatch('formSettings/FETCH_SUB_CATEGORIES')
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
    editBudget(index) {
      const editData = [...this.budgetList]
      const target = editData[index]
      if (target) {
        target.editable = true
        this.budgetList = editData
      }
    },
    updateBudget(index) {
      const editData = [...this.budgetList]
      const target = editData[index]
      if (target) {
        delete target.editable
        this.budgetList = editData
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
    handleSave(isFinal) {
      const { dataSource, budgetList, year } = this
      let proceed = 1
      if (!dataSource.length) {
        Modal.error({
          title: 'Unable to save form!',
          content: 'No PIs were added on the list',
        })
        proceed = 0
      } else if (!budgetList.length) {
        Modal.confirm({
          title: 'Budget for each program was not indicated',
          content: 'Do you want to proceed?',
          okText: 'Yes, proceed',
          cancelText: 'No',
          onOk() {},
          onCancel() {
            proceed = 0
          },
        })
      } else {
        let title = ''
        if (isFinal) {
          title = 'This is will finalize and save your data'
        } else {
          title = 'This is will save your data as draft'
        }
        Modal.confirm({
          title: title,
          content: 'Are you sure do you want to proceed?',
          okText: 'Yes, proceed',
          cancelText: 'No',
          onOk() {},
          onCancel() {
            proceed = 0
          },
        })
      }

      if (proceed) {
        const documentName = this.formId.toUpperCase() + '_' + year
        const data = {
          dataSource: dataSource,
          fiscalYear: year,
          isFinalized: isFinal,
          documentName: documentName,
          programBudgets: budgetList,
        }
        this.$store.dispatch('aapcr/SAVE_AAPCR', { payload: data })
        console.log(data)
      }
    },
  },
}
</script>
