<template>
  <div>
    <div class="row">
      <div class="col-xl-12 col-lg-12">
        <div class="card">
          <div class="card-header card-header-flex align-items-center">
            <div class="d-flex flex-column justify-content-center mr-auto">
            </div>
            <div>
              <a-tooltip placement="top">
                <a-button type="primary" icon="plus" class="mr-3" @click="openModal('create', null)">New Measure</a-button>
              </a-tooltip>
            </div>
          </div>
          <div class="card-body">
            <a-table :columns="columns" :dataSource="measuresList" :loading="loading">
              <template slot="action" slot-scope="text, record">
                <a type="primary" @click="openModal('view', record)">View</a>
                <a-divider type="vertical" />
                <a-popconfirm
                  title="Are you sure you want to delete this?"
                  @confirm="onDelete(record.key)"
                  okText="Yes"
                  cancelText="No"
                >
                  <a type="primary">Delete</a>
                </a-popconfirm>
              </template>
            </a-table>
            <a-modal v-model="open"
                     :title="modalTitle"
                     :closable="false"
                     :ok-text="okText"
                     @ok="okAction"
                     @cancel="resetFormData">
              <a-form-model ref="measureForm"
                            :model="form"
                            :rules="rules"
                            :label-col="labelCol"
                            :wrapper-col="wrapperCol">
                <a-form-model-item label="Measure name" ref="name" prop="name">
                  <a-input v-model="form.name" :disabled="action === 'view'"
                           @blur="
                    () => {
                      $refs.name.onFieldBlur();
                    }
                  "/>
                </a-form-model-item>
                <a-divider type="horizontal" />
                <label>Scales</label>
                <br />
                <a-input-group size="default">
                  <a-row>
                    <a-col :span="5" :offset="1" >
                      <a-input-number placeholder="Rate" :min="1" v-model="rate" :disabled="action === 'view'"/>
                    </a-col>
                    <a-col :span="12" :offset="1">
                      <a-input placeholder="Description" v-model="description" :disabled="action === 'view'"/>
                    </a-col>
                    <a-col :span="2" :offset="1">
                      <a-button type="primary" icon="plus" @click="addMeasureItem"
                                :disabled="rate === null || description === ''"></a-button>
                    </a-col>
                  </a-row>
                </a-input-group>
                <br />
                <a-list item-layout="horizontal" :data-source="itemsText">
                  <a-list-item slot="renderItem" slot-scope="item, index">
                    <a slot="actions" @click="deleteItem(index)" v-if="action !== 'view'">
                      delete
                    </a>
                    {{ item }}
                  </a-list-item>
                </a-list>
              </a-form-model>
            </a-modal>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<script>

import { mapState } from 'vuex'
import { Modal } from 'ant-design-vue'

const columns = [
  { title: 'Name', dataIndex: 'name', key: 'name' },
  { title: 'Action', dataIndex: '', key: 'x', scopedSlots: { customRender: 'action' } },
]

export default {
  name: 'MeasureComponent',
  components: {
  },
  computed: {
    ...mapState({
      measuresList: state => state.formSettings.measures,
    }),
    loading() {
      return this.$store.state.formSettings.loading
    },
  },
  created() {
    this.onLoad()
  },
  data() {
    return {
      columns,
      open: false,
      action: '',
      modalTitle: '',
      okText: '',
      measureId: null,
      labelCol: { span: 6 },
      wrapperCol: { span: 16 },
      form: {
        id: null,
        name: '',
        items: [],
        deleted: [],
      },
      itemsText: [],
      rate: null,
      description: '',
      rules: {
        name: [
          { required: true, message: 'This field is required', trigger: 'change' },
          { whitespace: true, message: 'Please input a valid Measure name', trigger: 'change' },
          { min: 3, message: 'Length should be at least 3 characters', trigger: 'change' },
        ],
      },
    }
  },
  methods: {
    changeAction(action) {
      if (action === 'create') {
        this.modalTitle = 'New Measure'
        this.okText = 'Create'
        this.action = 'create'
      } else if (action === 'view') {
        this.modalTitle = 'View Measure'
        this.okText = 'Edit'
        this.action = 'view'
      } else if (action === 'update') {
        this.modalTitle = 'Update Measure'
        this.okText = 'Update'
        this.action = 'update'
      }
    },
    onLoad() {
      this.$store.dispatch('formSettings/FETCH_MEASURES')
    },
    onDelete(key) {
      this.$store.dispatch('formSettings/DELETE_MEASURE', { payload: key })
    },
    openModal(event, record) {
      this.resetFormData()
      this.open = true
      this.measureId = record !== null ? record.id : record
      if (this.measureId) {
        const formItems = record.items
        this.form.id = record.id
        this.form.name = record.name
        this.form.items = formItems
        formItems.forEach(item => {
          const text = item.rate + ' - ' + item.description
          this.itemsText.push(text)
        })
      }
      this.changeAction(event)
    },
    addMeasureItem() {
      this.form.items.push({
        status: 'new',
        rate: this.rate,
        description: this.description,
      })
      const text = this.rate + ' - ' + this.description
      this.itemsText.push(text)
      this.rate = null
      this.description = ''
    },
    deleteItem(index) {
      const item = this.form.items[index]
      this.form.items.splice(index, 1)
      this.itemsText.splice(index, 1)
      if (this.action === 'update' && (typeof (item.status) === 'undefined' || item.status !== 'new')) {
        console.log(item.id)
        this.form.deleted.push(item.id)
      }
    },
    resetFormData() {
      this.form = {
        id: null,
        name: '',
        items: [],
        deleted: [],
      }
      this.itemsText = []
      this.rate = null
      this.description = ''
    },
    okAction() {
      if (this.action === 'view') {
        this.changeAction('update')
      } else {
        this.submitForm()
      }
    },
    submitForm() {
      const that = this
      console.log(that.form)
      that.$refs.measureForm.validate(valid => {
        if (valid) {
          if (that.form.items.length < 3) {
            Modal.error({
              title: 'Please add at least three (3) scales',
              content: '',
            })
            return false
          } else {
            Modal.confirm({
              title: 'Are you sure you want to save this?',
              content: '',
              okText: 'Yes',
              cancelText: 'No',
              onOk() {
                if (that.action === 'create') {
                  that.$store.dispatch('formSettings/CREATE_MEASURE', { payload: that.form })
                } else {
                  that.$store.dispatch('formSettings/UPDATE_MEASURE', { payload: that.form })
                }
                that.resetFormData()
                that.open = false
              },
            })
          }
        } else {
          return false
        }
      })
    },
  },
}
</script>
