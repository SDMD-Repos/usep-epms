<template>
  <div class="row">
    <div class="col-xl-12 col-lg-12">
      <div>
        <a-select v-model="mainCategory"
                  placeholder="Select"
                  style="width: 30%"
                  :loading="loading"
                  @change="loadPIs"
                  label-in-value>
          <template v-for="(y, i) in filteredProgram">
            <a-select-option :value="y.id" :key="i">
              {{ y.name }}
            </a-select-option>
          </template>
        </a-select>
      </div>
      <br />
      <a-table
        v-if="displayPiList"
        :columns="getFormColumns"
        :data-source="filteredDataSource"
        bordered
        size="middle"
        :scroll="{ x: 'calc(2600px + 50%)', y: 600 }"
      >
        <template slot="title">
          <a-button type="primary" @click="openModal('Add')">New</a-button>
        </template>
        <template slot="footer" v-if="filteredDataSource.length">
          <a-row type="flex" align="middle" :gutter="[16,16]">
            <a-col :span="2" >
              <label>Budget: </label>
            </a-col>
            <a-col :span="5">
              <a-input-number v-model="categoryBudget" style="width: 100%"
                              :formatter="value => `${value}`.replace(/\B(?=(\d{3})+(?!\d))/g, ',')"
                              :parser="value => value.replace(/\$\s?|(,*)/g, '')"
                              :min="0"/>
            </a-col>
            <a-col :span="2">
              <a-icon type="check" :style="{ fontSize: '20px' }" @click="saveProgramBudget"/>
            </a-col>
          </a-row>
        </template>
        <template slot="targetYearColumn">
          {{ year }}
        </template>

        <!-- Custom data row render-->
        <span slot="subCategory" slot-scope="text, record">
          {{ record.type === 'pi' ? record.subCategory.label : ''}}
        </span>

        <template slot="isHeaderPI" slot-scope="text, record" v-if="record.type === 'pi'">
          <a-icon type="check-circle"
                  theme="filled"
                  :style="{ fontSize: '18px', color: '#2b5c17' }"
                  v-if="record.isHeader"/>
          <a-icon type="close-circle"
                  theme="filled"
                  :style="{ fontSize: '18px', color: '#eb2f2f' }"
                  v-else/>
        </template>

        <template slot="budget" slot-scope="type, record">
          {{ numbersWithCommas(record.budget) }}
        </template>

        <template slot="measures" slot-scope="type, record">
          <ul>
            <li v-for="measure in record.measures" :key="measure.key">
              {{ measure.label }}
            </li>
          </ul>
        </template>

        <span slot="cascadingLevel" slot-scope="text, record" v-if="!record.isHeader">
          {{ record.cascadingLevel.label }}
        </span>

        <template slot="implementing" slot-scope="type, record">
          <ul>
            <li v-for="office in record.implementing" :key="office.key">
              {{ office.label }}
            </li>
          </ul>
        </template>

        <template slot="supporting" slot-scope="type, record">
          <ul>
            <li v-for="office in record.supporting" :key="office.key">
              {{ office.label }}
            </li>
          </ul>
        </template>

        <template slot="action" slot-scope="text, record">
          <a-icon type="edit" theme="filled" @click="handleEdit(record.key, record.type)"/>
          <a-divider type="vertical" />
          <template v-if="record.type === 'pi'">
            <a-icon type="plus-circle" theme="filled" @click="handleAddSub(record.key)"/>
            <a-divider type="vertical" />
          </template>
          <a-popconfirm
            title="Are you sure you want to delete this?"
            @confirm="handleDelete(record.key, record.type)"
            okText="Yes"
            cancelText="No"
          >
            <a-icon type="delete" theme="filled"/>
          </a-popconfirm>
        </template>
      </a-table>
        <drawer-pi-form
          :form-object="form"
          :pi-form-data="piFormData"
          :function-id="functionId"
          :categories="categories"
          :targets-basis-list="targetsBasisList"
          @add-table-item="addTableItem"
          @update-table-item="updateTableItem"
          @close-modal="resetModalData"/>
    </div>
  </div>
</template>
<script>
import { mapState } from 'vuex'
import { Modal } from 'ant-design-vue'
import DrawerPiForm from './form'
import { getFormColumns } from '@/services/formColumns'
import { numbersWithCommas } from '@/services/filters'

const getPiFormDataDefault = () => {
  return {
    open: false,
    okText: '',
    modalTitle: '',
    updateId: null,
    type: 'pi',
    parentDetails: undefined,
  }
}

const addtlFromData = getPiFormDataDefault()

export default {
  props: ['year', 'functionId', 'categories', 'piSource'],
  components: {
    DrawerPiForm,
  },
  computed: {
    ...mapState({
      programList: state => state.formSettings.programs,
      loading: state => state.formSettings.loading,
    }),
    filteredProgram() {
      return this.programList.filter(i => i.category_id === this.functionId)
    },
    filteredDataSource() {
      return this.dataSource.filter(i => i.program === this.mainCategory.key)
    },
  },
  data() {
    const piSource = this.piSource
    return {
      getFormColumns,
      displayPiList: 0,
      mainCategory: undefined,
      categoryBudget: null,
      count: 0,
      dataSource: piSource,
      targetsBasisList: [],
      piFormData: addtlFromData,
      form: {
        subCategory: undefined,
        name: '',
        isHeader: false,
        target: '',
        measures: [],
        budget: null,
        targetsBasis: undefined,
        cascadingLevel: undefined,
        implementing: [],
        supporting: [],
        otherRemarks: '',
      },
    }
  },
  watch: {
    piSource(val) {
      this.dataSource = val
    },
  },
  created() {
    this.onLoad()
  },
  methods: {
    numbersWithCommas,
    onLoad() {
      this.$store.dispatch('formSettings/FETCH_PROGRAMS')
    },
    loadPIs(e) {
      if (e !== '' && typeof e !== 'undefined') {
        this.displayPiList = 1
      }
    },
    openModal(action) {
      const { piFormData } = this
      piFormData.open = true
      if (action === 'Add') {
        piFormData.okText = action
        piFormData.modalTitle = 'Add New'
        piFormData.updateId = null
        piFormData.type = 'pi'
      } else if (action === 'Update') {
        piFormData.okText = action
        piFormData.modalTitle = 'Update Details'
      } else if (action === 'newsub') {
        piFormData.okText = 'Add Sub PI'
        piFormData.modalTitle = 'New Sub PI'
        piFormData.updateId = null
        piFormData.type = 'sub'
      }
    },
    addTableItem(data) {
      const { dataSource, count, piFormData } = this
      if (!data.isHeader) {
        if (data.targetsBasis !== '' && typeof data.targetsBasis !== 'undefined' && this.targetsBasisList.indexOf(data.targetsBasis) === -1) {
          this.targetsBasisList.push(data.targetsBasis)
        }
      }
      const key = 'new_' + count
      const newData = {
        key: key,
        id: key,
        type: piFormData.type,
        subCategory: data.subCategory,
        program: this.mainCategory.key,
        name: data.name,
        isHeader: data.isHeader,
        target: data.target,
        measures: data.measures,
        budget: data.budget,
        targetsBasis: data.targetsBasis,
        cascadingLevel: data.cascadingLevel,
        implementing: data.implementing,
        supporting: data.supporting,
        otherRemarks: data.otherRemarks,
      }
      if (piFormData.type === 'pi') {
        this.dataSource = [...dataSource, newData]
        if (data.isHeader) {
          const that = this
          Modal.confirm({
            title: 'Do you want to add sub PI?',
            content: '',
            okText: 'Yes',
            cancelText: 'No',
            onOk() {
              that.handleAddSub(key)
            },
            onCancel() {},
          })
        }
      } else {
        const { parentDetails } = piFormData
        const source = [...this.dataSource]
        const target = source.filter(item => parentDetails.key === item.key)[0]
        if (typeof target.children === 'undefined') {
          target.children = []
        }
        target.children.push(newData)
        this.dataSource = source
      }
      this.count = count + 1
    },
    updateTableItem(details) {
      const newData = [...this.dataSource]
      const { piFormData } = this
      if (piFormData.type === 'pi') {
        Object.assign(newData[details.updateId], details.formData)
      } else {
        const { parentDetails } = this.piFormData
        const parentIndex = newData.findIndex(i => i.key === parentDetails.key)
        const { children } = newData[parentIndex]
        Object.assign(children[details.updateId], details.formData)
      }
      this.resetModalData(0)
    },
    resetModalData(newPI) {
      Object.assign(this.piFormData, getPiFormDataDefault())
      if (newPI) {
        this.openModal('Add')
      }
    },
    handleEdit(key, type) {
      const { dataSource } = this
      let editData = null
      if (type === 'pi') {
        editData = dataSource.filter(item => key === item.key)[0]
        this.piFormData.updateId = dataSource.findIndex(record => record.key === key)
      } else {
        this.piFormData.type = type
        let shouldBreak = false
        dataSource.forEach(item => {
          if (typeof item.children !== 'undefined') {
            const temp = item.children.filter(i => i.key === key)
            if (shouldBreak) {
              return
            }
            if (temp.length) {
              editData = temp[0]
              shouldBreak = true
              this.piFormData.updateId = item.children.findIndex(i => i.key === key)
              this.piFormData.parentDetails = { ...item }
              return
            }
            console.log(temp)
          }
        })
      }
      this.form = {
        subCategory: editData.subCategory,
        name: editData.name,
        isHeader: editData.isHeader,
        target: editData.target,
        measures: editData.measures,
        budget: editData.budget,
        targetsBasis: editData.targetsBasis,
        cascadingLevel: editData.cascadingLevel,
        implementing: editData.implementing,
        supporting: editData.supporting,
        otherRemarks: editData.otherRemarks,
      }
      this.openModal('Update')
    },
    handleDelete(key, type) {
      if (type === 'pi') {
        const recordKey = this.dataSource.findIndex((record, i) => record.key === key)
        this.dataSource.splice(recordKey, 1)
      } else {
        const source = [...this.dataSource]
        source.forEach((item, index) => {
          if (typeof item.children !== 'undefined') {
            const recordKey = item.children.findIndex(i => i.key === key)
            if (recordKey !== -1) {
              item.children.splice(recordKey, 1)
              if (!item.children.length) {
                delete item.children
              }
              return
            }
            console.log(recordKey)
          }
        })
        this.dataSource = source
        console.log(source)
      }
    },
    handleAddSub(key) {
      const { form } = this
      const newData = this.dataSource.filter(item => key === item.key)[0]
      this.piFormData.parentDetails = { ...newData }
      form.subCategory = newData.subCategory
      if (!newData.isHeader) {
        form.measures = newData.measures
        form.targetsBasis = newData.targetsBasis
        form.cascadingLevel = newData.cascadingLevel
        form.implementing = newData.implementing
        form.supporting = newData.supporting
      }
      this.form = form
      this.openModal('newsub')
    },
    saveProgramBudget() {
      const { mainCategory, categoryBudget } = this
      if (!categoryBudget) {
        Modal.error({
          title: 'No data was saved!',
          content: 'Please input a valid amount',
        })
      } else {
        this.$emit('add-budget-list-item', { mainCategory, categoryBudget })
        this.categoryBudget = null
      }
    },
  },
}
</script>
<style lang="scss">
@import "@/components/system/Forms/Aapcr/style.module.scss";
</style>
