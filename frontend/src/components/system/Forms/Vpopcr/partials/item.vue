<template>
  <div class="row">
    <div class="col-xl-12 col-lg-12">
      <a-table
        :columns="columns"
        :data-source="filteredDataSource"
        bordered
        size="middle"
        :scroll="{ x: 'calc(2600px + 50%)', y: 600 }"
        :loading="loading"
      >

        <template slot="title" v-if="enableForm">
          <a-button type="primary" @click="openModal('Add')">New</a-button>
        </template>

        <!-- Custom data row render-->
        <span slot="count" slot-scope="text, record">
          {{ record.count }}
        </span>

        <template slot="isHeader" slot-scope="text, record" v-if="record.type === 'pi'">
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
          {{ record.budget | numbersWithCommasDecimal }}
        </template>

        <template slot="measures" slot-scope="type, record">
          <ul class="form-ul-list">
            <li v-for="measure in record.measures" :key="measure.key">
              {{ measure.label }}
            </li>
          </ul>
        </template>

        <template slot="implementing" slot-scope="type, record">
          <ul class="form-ul-list">
            <li v-for="office in record.implementing" :key="office.key">
              {{ office.acronym ? office.acronym : office.label }}
            </li>
          </ul>
        </template>

        <template slot="supporting" slot-scope="type, record">
          <ul class="form-ul-list">
            <li v-for="office in record.supporting" :key="office.key">
              {{ office.acronym ? office.acronym : office.label }}
            </li>
          </ul>
        </template>

        <template slot="action" slot-scope="text, record">
          <a-icon type="edit" theme="filled" @click="handleEdit(record.key, record.type)" v-if="!record.isCascaded"/>
          <a-divider type="vertical" v-if="!record.isCascaded"/>
          <template v-if="record.type === 'pi'">
            <a-icon type="plus-circle" theme="filled" @click="handleAddSub(record.key)"/>
            <a-divider type="vertical" v-if="!record.isCascaded"/>
          </template>
          <a-popconfirm
            title="Are you sure you want to delete this?"
            @confirm="handleDelete(record.key, record.type)"
            okText="Yes"
            cancelText="No"
            v-if="!record.isCascaded"
          >
            <a-icon type="delete" theme="filled"/>
          </a-popconfirm>
        </template>
      </a-table>

      <drawer-detail-form v-if="opened === functionId"
                          :drawer-id="opened"
                          :form-object="form"
                          :drawer-config="drawerConfig"
                          :categories="categories"
                          :targets-basis-list="targetsBasisList"
                          @close-modal="changeModalState"
                          @add-table-item="addTableItem"
                          @update-table-item="updateTableItem"
                          @reset-form="resetForm"
                          @add-sub-pi="handleAddSub"/>
    </div>
  </div>
</template>
<script>
import { mapState } from 'vuex'
import DrawerDetailForm from './form'
import ItemMixin from '@/services/formMixins/item'
import { Modal } from 'ant-design-vue'

const getDefaultFormData = () => {
  return {
    program: null,
    subCategory: null,
    name: '',
    isHeader: false,
    target: '',
    measures: [],
    budget: null,
    targetsBasis: '',
    cascadingLevel: '',
    implementing: [],
    supporting: [],
    options: {
      implementing: [],
      supporting: [],
    },
    remarks: '',
  }
}

const formData = getDefaultFormData()

export default {
  mixins: [ItemMixin],
  name: 'list-item',
  props: {
    vpOffice: Object,
    enableForm: Boolean,
  },
  computed: {
    ...mapState({
      loading: state => state.opcrvp.loading,
    }),
    filteredDataSource() {
      const copy = [...this.dataSource.filter(i => i.category === this.functionId)]
      copy.forEach((x, y) => {
        copy[y].count = y + 1
      })
      return copy
    },
  },
  data() {
    const enableForm = this.enableForm
    return {
      columns: [],
      form: formData,
      isEnable: enableForm,
    }
  },
  created() {
    this.initializeColumns()
  },
  watch: {
    itemSource(val) {
      this.dataSource = val
    },
    drawer(val) {
      this.opened = val
    },
    counter(val) {
      this.count = val
    },
  },
  methods: {
    initializeColumns() {
      let { columns } = this
      const remarksIndex = this.getFormColumns.findIndex(i => i.key === 'otherRemarks')
      columns = [...this.getFormColumns]
      columns.splice(remarksIndex, 0, {
        title: 'Remarks',
        key: 'remarks',
        dataIndex: 'remarks',
        className: 'column-other-remarks',
        width: 200,
        ellipsis: true,
      })
      const deleteKeys = ['subCategory', 'cascadingLevel', 'otherRemarks']
      columns = [...columns.filter(i => deleteKeys.indexOf(i.key) === -1)]
      const addtl = {
        title: '#',
        key: 'count',
        className: 'column-count',
        width: 60,
        scopedSlots: { customRender: 'count' },
      }
      columns.splice(0, 0, addtl)
      this.columns = columns
    },
    resetForm() {
      Object.assign(this.form, getDefaultFormData())
    },
    addTableItem(data) {
      const { count, drawerConfig } = this
      if (!data.isHeader) {
        if (data.targetsBasis !== '' && typeof data.targetsBasis !== 'undefined' && this.targetsBasisList.indexOf(data.targetsBasis) === -1) {
          this.$emit('add-targets-basis-item', data.targetsBasis)
        }
      }
      const key = 'new_' + count
      const newData = {
        key: key,
        id: key,
        category: this.functionId,
        type: drawerConfig.type,
        subCategory: data.subCategory,
        program: data.program,
        name: data.name,
        isHeader: data.isHeader,
        target: data.target,
        measures: data.measures,
        budget: data.budget,
        targetsBasis: data.targetsBasis,
        cascadingLevel: data.cascadingLevel,
        implementing: data.implementing,
        supporting: data.supporting,
        remarks: data.remarks,
        deleted: 0,
      }
      if (drawerConfig.type === 'pi') {
        this.dataSource.push(newData)
        if (data.isHeader) {
          const that = this
          Modal.confirm({
            title: 'Do you want to add a sub PI?',
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
        const { parentDetails } = drawerConfig
        const source = [...this.dataSource]
        const target = source.filter(item => parentDetails.key === item.key)[0]
        if (typeof target.children === 'undefined') {
          target.children = []
        }
        target.children.push(newData)
        this.$emit('update-data-source', source)
      }
      this.$emit('update-counter', count + 1)
    },
    handleEdit(key, type) {
      const { dataSource } = this
      let editData = null
      if (type === 'pi') {
        editData = dataSource.filter(item => key === item.key)[0]
        this.drawerConfig.updateId = dataSource.findIndex(record => record.key === key)
      } else {
        this.drawerConfig.type = type
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
              this.drawerConfig.updateId = item.children.findIndex(i => i.key === key)
              this.drawerConfig.parentDetails = { ...item }
              return
            }
            console.log(temp)
          }
        })
      }
      this.form = {
        // category: this.functionId,
        subCategory: editData.subCategory,
        program: editData.program,
        name: editData.name,
        isHeader: editData.isHeader,
        target: editData.target,
        measures: editData.measures,
        budget: editData.budget,
        targetsBasis: editData.targetsBasis,
        cascadingLevel: editData.cascadingLevel,
        implementing: editData.implementing,
        supporting: editData.supporting,
        options: {
          implementing: [],
          supporting: [],
        },
        remarks: editData.remarks,
      }
      this.openModal('Update')
    },
    handleAddSub(key) {
      const { form, functionId } = this
      const newData = this.dataSource.filter(item => {
        return key === item.key && functionId === item.category
      })[0]
      this.drawerConfig.parentDetails = { ...newData }
      form.subCategory = newData.subCategory
      form.program = newData.program
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
  },
  components: {
    DrawerDetailForm,
  },
}
</script>
