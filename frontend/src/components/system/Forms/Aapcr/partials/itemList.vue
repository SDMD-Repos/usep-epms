<template>
  <div class="row">
    <div class="col-xl-12 col-lg-12">
      <div class="col-xl-4 col-lg-4">
        <a-select v-model="mainCategory" placeholder="Select" style="width: 100%" @change="loadPIs" :loading="loading">
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
        <template slot="targetYearColumn">
          {{ year }}
        </template>

        <!-- Custom data row render-->
        <span slot="subCategory" slot-scope="text, record">
          {{ record.subCategory.label }}
        </span>

        <template slot="isHeaderPI" slot-scope="text, record">
          <a-icon type="check-circle"
                  theme="filled"
                  :style="{ fontSize: '18px', color: '#2b5c17' }"
                  v-if="record.isHeader"/>
          <a-icon type="close-circle"
                  theme="filled"
                  :style="{ fontSize: '18px', color: '#eb2f2f' }"
                  v-else/>
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
          <a-icon type="edit" theme="filled" @click="handleEdit(record.key)"/>
          <a-divider type="vertical" />
          <a-icon type="plus-circle" theme="filled" @click="handleAddSub(record.key)"/>
          <a-divider type="vertical" />
          <a-popconfirm
            title="Are you sure you want to delete this?"
            @confirm="handleDelete(record.key)"
            okText="Yes"
            cancelText="No"
          >
            <a-icon type="delete" theme="filled"/>
          </a-popconfirm>
        </template>
      </a-table>
        <drawer-pi-form
          :form-object="form"
          :open="open"
          :ok-text="okText"
          :modal-title="modalTitle"
          :targets-basis-list="targetsBasisList"
          :function-id="functionId"
          :categories="categories"
          :update-id="updateId"
          @add-table-item="addTableItem"
          @update-table-item="updateTableItem"
          @close-modal="resetModalData"/>
    </div>
  </div>
</template>
<script>
import { mapState } from 'vuex'
import DrawerPiForm from './form'
import { getFormColumns } from '@/services/formColumns'

export default {
  props: ['year', 'functionId', 'categories'],
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
      return this.dataSource.filter(i => i.program === this.mainCategory)
    },
  },
  data() {
    return {
      getFormColumns,
      displayPiList: 0,
      mainCategory: undefined,
      dataSource: [],
      targetsBasisList: [],
      open: false,
      okText: '',
      modalTitle: '',
      updateId: null,
      form: {
        type: 'pi',
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
        parentDetails: undefined,
      },
    }
  },
  created() {
    this.onLoad()
  },
  methods: {
    onLoad() {
      this.$store.dispatch('formSettings/FETCH_PROGRAMS')
    },
    loadPIs(e) {
      if (e !== '' && typeof e !== 'undefined') {
        this.displayPiList = 1
      }
    },
    openModal(action) {
      this.open = !this.open
      if (action === 'Add') {
        this.okText = action
        this.modalTitle = 'Add New'
        this.updateId = null
      } else if (action === 'Update') {
        this.okText = action
        this.modalTitle = 'Update Details'
      }
    },
    addTableItem(data) {
      const count = this.dataSource.length
      if (!data.isHeader) {
        if (data.targetsBasis !== '' && typeof data.targetsBasis !== 'undefined' && this.targetsBasisList.indexOf(data.targetsBasis) === -1) {
          this.targetsBasisList.push(data.targetsBasis)
        }
      }
      const key = 'new_' + count
      const newData = {
        key: key,
        id: key,
        subCategory: data.subCategory,
        program: this.mainCategory,
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
      this.dataSource.push(newData)
    },
    updateTableItem(details) {
      const newData = [...this.dataSource]
      Object.assign(newData[details.updateId], details.formData)
    },
    resetModalData() {
      this.open = !this.open
    },
    handleEdit(key) {
      const newData = this.dataSource.filter(item => key === item.key)[0]
      this.form = { ...newData }
      this.updateId = this.dataSource.findIndex((record, i) => record.key === key)
      this.openModal('Update')
    },
    handleDelete(key) {
      const recordKey = this.dataSource.findIndex((record, i) => record.key === key)
      this.dataSource.splice(recordKey, 1)
    },
    handleAddSub(key) {
      const newData = this.dataSource.filter(item => key === item.key)[0]
      this.form = { ...newData }
    },
  },
}
</script>
<style lang="scss">
@import "@/components/system/Forms/Aapcr/style.module.scss";
</style>
