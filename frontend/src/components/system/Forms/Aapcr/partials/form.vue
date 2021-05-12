<template>
  <span>
    <a-drawer :title="modalTitle"
              :width="800"
              :visible="isOpen"
              :mask-closable="false"
              :closable="false"
              :body-style="{ paddingBottom: '80px' }"
              @close="resetFormData">
      <a-form-model ref="spmsForm" :model="form" :rules="rules" layout="horizontal" :hide-required-mark="true">
        <a-form-model-item label="Type"
                           prop="type"
                           :label-col="formItemLayout.labelCol"
                           :wrapper-col="formItemLayout.wrapperCol">
          <a-radio-group v-model="form.type" disabled>
            <a-radio value="pi">
              PI
            </a-radio>
            <a-radio value="sub">
              Sub PI
            </a-radio>
          </a-radio-group>
        </a-form-model-item>

        <a-form-model-item label="Parent PI"
                           prop="parentDetails"
                           :label-col="formItemLayout.labelCol"
                           :wrapper-col="formItemLayout.wrapperCol" v-if="isSubPI">
          <a-textarea v-model="form.parentDetails.name" auto-size disabled/>
        </a-form-model-item>

        <a-form-model-item label="Sub Category"
                           prop="subCategory"
                           :label-col="formItemLayout.labelCol"
                           :wrapper-col="formItemLayout.wrapperCol">
          <a-tree-select
            v-model="form.subCategory"
            style="width: 100%"
            :dropdown-style="{ maxHeight: '400px', overflow: 'auto' }"
            :tree-data="filteredSubCategory"
            placeholder="Select"
            :replace-fields="normalizer"
            allow-clear
            tree-default-expand-all
            label-in-value
          ></a-tree-select>
        </a-form-model-item>

        <a-form-model-item label="Performance Indicator"
                           prop="name"
                           :label-col="formItemLayout.labelCol"
                           :wrapper-col="formItemLayout.wrapperCol">
          <a-textarea v-model="form.name" auto-size/>
        </a-form-model-item>

        <a-form-model-item label="Header PI?"
                           prop="isHeader"
                           :label-col="formItemLayout.labelCol"
                           :wrapper-col="formItemLayout.wrapperCol">
          <a-switch v-model="form.isHeader"/>
        </a-form-model-item>

        <template v-if="!form.isHeader">
          <a-form-model-item label="Target"
                             prop="target"
                             :label-col="formItemLayout.labelCol"
                             :wrapper-col="formItemLayout.wrapperCol">
            <a-input v-model="form.target" />
          </a-form-model-item>

          <a-form-model-item label="Measures"
                             prop="measures"
                             :label-col="formItemLayout.labelCol"
                             :wrapper-col="formItemLayout.wrapperCol">
            <a-select v-model="form.measures" mode="multiple" placeholder="Select" style="width: 100%" label-in-value>
              <a-select-option v-for="measure in measuresList" :value="measure.id" :key="measure.id">
                {{ measure.name }}
              </a-select-option>
            </a-select>
          </a-form-model-item>

          <a-form-model-item label="Allocated Budget"
                             prop="budget"
                             :label-col="formItemLayout.labelCol"
                             :wrapper-col="formItemLayout.wrapperCol">
            <a-input-number v-model="form.budget"
                            style="width: 50%"
                            :step="0.01"
                            :formatter="value => `${value}`.replace(/\B(?=(\d{3})+(?!\d))/g, ',')"
                            :parser="value => value.replace(/\$\s?|(,*)/g, '')"
                            :min="0"/>
          </a-form-model-item>

          <a-form-model-item label="Targets Basis"
                             prop="targetsBasis"
                             :label-col="formItemLayout.labelCol"
                             :wrapper-col="formItemLayout.wrapperCol">
            <a-auto-complete
              v-model="form.targetsBasis"
              :data-source="targetsBasisList"
              :filter-option="filterBasisOption"
            />
          </a-form-model-item>

          <a-form-model-item label="Casading Level"
                             prop="cascadingLevel"
                             :label-col="formItemLayout.labelCol"
                             :wrapper-col="formItemLayout.wrapperCol">
            <a-select v-model="form.cascadingLevel" placeholder="Select" style="width: 100%" label-in-value>
              <a-select-option v-for="levelItem in cascadingList" :value="levelItem.id" :key="levelItem.id">
                {{ levelItem.name }}
              </a-select-option>
            </a-select>
          </a-form-model-item>

          <a-form-model-item label="Implementing Office"
                             prop="implementing"
                             :label-col="formItemLayout.labelCol"
                             :wrapper-col="formItemLayout.wrapperCol">
            <div class="row">
              <div class="col-sm-9 col-lg-10">
                <a-tree-select
                  v-model="form.implementing"
                  style="width: 100%"
                  :dropdown-style="{ maxHeight: '400px', overflow: 'auto' }"
                  :tree-data="mainOfficesList"
                  placeholder="Select an office/s"
                  :show-checked-strategy="SHOW_PARENT"
                  :max-tag-count="6"
                  allow-clear
                  tree-checkable
                  label-in-value />
              </div>
              <div class="col-sm-2 col-lg-2">
                <a-tooltip title="View List">
                  <a-icon type="edit"
                          theme="filled"
                          :style="{ fontSize: '18px', cursor: 'pointer' }"
                          @click="viewOfficeList('implementing')"/>
                </a-tooltip>
              </div>
            </div>
          </a-form-model-item>

          <a-form-model-item label="Supporting Office"
                             prop="supporting"
                             :label-col="formItemLayout.labelCol"
                             :wrapper-col="formItemLayout.wrapperCol">
            <div class="row">
              <div class="col-sm-9 col-lg-10">
                <a-tree-select
                  v-model="form.supporting"
                  style="width: 100%"
                  :dropdown-style="{ maxHeight: '400px', overflow: 'auto' }"
                  :tree-data="mainOfficesList"
                  placeholder="Select an office/s"
                  :show-checked-strategy="SHOW_PARENT"
                  :max-tag-count="6"
                  show-search
                  tree-checkable
                  label-in-value
                />
              </div>
              <div class="col-sm-2 col-lg-2">
                <a-tooltip title="View List">
                  <a-icon type="edit"
                          theme="filled"
                          :style="{ fontSize: '18px', cursor: 'pointer' }"
                          @click="viewOfficeList('supporting')"/>
                </a-tooltip>
              </div>
            </div>
          </a-form-model-item>

          <a-form-model-item label="Other Remarks"
                             prop="otherRemarks"
                             :label-col="formItemLayout.labelCol"
                             :wrapper-col="formItemLayout.wrapperCol">
            <a-textarea v-model="form.otherRemarks" auto-size />
          </a-form-model-item>
        </template>
      </a-form-model>
      <div
        :style="{
          position: 'absolute',
          right: 0,
          bottom: 0,
          width: '100%',
          borderTop: '1px solid #e9e9e9',
          padding: '10px 16px',
          background: '#fff',
          textAlign: 'right',
          zIndex: 1,
        }"
      >
        <a-button :style="{ marginRight: '8px' }" @click="resetFormData">
          Cancel
        </a-button>
        <a-button type="primary" @click="okModalAction" :loading="isSubmmiting">
          {{ okText }}
        </a-button>
      </div>
    </a-drawer>

    <!-- View Offices List Modal -->
    <a-modal v-model="openList"
             title="Office List"
             :closable="false"
             ok-text="Save"
             @ok="handleOk"
             @cancel="handleCancel">
      <template v-if="officesList.length">
        <div class="row mb-2" v-for="(office, i) in officesList" v-bind:key="i">
          <div class="col-sm-6 col-lg-6">
            <span>{{ office.label }}</span>
          </div>
          <div class="col-sm-6 col-lg-6">
            <a-select v-model="officesList[i].cascadeTo" style="width: 100%">
              <a-select-option v-for="category in categories" :value="category.id" :key="category.id">
                {{ category.name }}
              </a-select-option>
            </a-select>
          </div>
        </div>
      </template>
    </a-modal>
  </span>
</template>

<script>
import { mapState } from 'vuex'
import { TreeSelect, Modal } from 'ant-design-vue'
const SHOW_PARENT = TreeSelect.SHOW_PARENT

const formItemLayout = {
  labelCol: { span: 6 },
  wrapperCol: { span: 14 },
}

const messageKey = 'updatable'

export default {
  name: 'drawer-pi-form',
  props: {
    formObject: {
      type: Object,
    },
    open: {
      type: Boolean,
      required: true,
    },
    okText: {
      type: String,
      required: true,
    },
    modalTitle: {
      type: String,
    },
    targetsBasisList: {
      type: Array,
    },
    functionId: {
      type: String,
    },
    categories: {
      type: Array,
    },
    updateId: {
      type: Number,
    },
  },
  computed: {
    ...mapState({
      subCategoryList: state => state.formSettings.subCategories,
      measuresList: state => state.formSettings.measures,
      cascadingList: state => state.formSettings.cascadingLevels,
      mainOfficesList: state => state.external.mainOffices,
      loading: state => state.formSettings.loading,
    }),
    filteredSubCategory() {
      return this.subCategoryList.filter((i) => {
        return i.category_id === this.functionId && i.parent_id === null
      })
    },
  },
  data() {
    const validateNonHeader = (rule, value, callback) => {
      if (!this.form.isHeader) {
        if (value === '' || (Array.isArray(value) && !value.length) || typeof value === 'undefined') {
          callback(new Error('This field is required'))
        } else {
          this.$refs.spmsForm.validateField(rule.field)
          callback()
        }
      } else {
        this.$refs.spmsForm.validateField(rule.field)
        callback()
      }
    }
    const open = this.open
    const formObject = this.formObject
    return {
      formItemLayout,
      SHOW_PARENT,
      isSubmmiting: false,
      isOpen: open,
      isSubPI: false,
      normalizer: {
        title: 'name',
        value: 'id',
      },
      form: formObject,
      rules: {
        subCategory: [
          { required: true, message: 'Please select at least one', trigger: 'blur' },
          { type: 'object' },
        ],
        name: [{ required: true, message: 'This field is required', trigger: 'blur' }],
        target: [{ validator: validateNonHeader, trigger: 'blur' }],
        measures: [{ validator: validateNonHeader, trigger: 'blur' }],
        targetsBasis: [{ validator: validateNonHeader, trigger: 'blur' }],
        cascadingLevel: [{ validator: validateNonHeader, trigger: 'blur' }],
        implementing: [
          { validator: validateNonHeader, trigger: 'blur' },
          { type: 'array' },
        ],
        supporting: [
          { validator: validateNonHeader, trigger: 'blur' },
          { type: 'array' },
        ],
      },
      openList: false,
      officeType: '',
      officesList: [],
    }
  },
  watch: {
    open(val) {
      this.isOpen = val
    },
    formObject(val) {
      this.form = val
    },
  },
  created() {
    this.onLoad()
  },
  methods: {
    onLoad() {
      this.$store.dispatch('external/FETCH_MAIN_OFFICES') // needs to load first
      this.$store.dispatch('formSettings/FETCH_SUB_CATEGORIES')
      this.$store.dispatch('formSettings/FETCH_MEASURES')
      this.$store.dispatch('formSettings/FETCH_CASCADING_LEVELS')
    },
    filterBasisOption(input, option) {
      return (
        option.componentOptions.children[0].text.toUpperCase().indexOf(input.toUpperCase()) >= 0
      )
    },
    okModalAction() {
      console.log(this.form)
      this.isSubmmiting = !this.isSubmmiting
      const tempImplementing = this.mappedOfficeList(this.form.implementing, 'implementing')
      const tempSupporting = this.mappedOfficeList(this.form.supporting, 'supporting')
      this.form.implementing = tempImplementing
      this.form.supporting = tempSupporting
      const that = this
      setTimeout(() => {
        this.$refs.spmsForm.validate(valid => {
          if (valid) {
            let msgContent = ''
            if (this.updateId === null) {
              this.$emit('add-table-item', this.form)
              msgContent = 'Added!'
            } else {
              this.$emit('update-table-item', { formData: this.form, updateId: this.updateId })
              msgContent = 'Updated!'
            }
            this.$refs.spmsForm.resetFields()
            this.$message.success({ content: msgContent, messageKey, duration: 2 })
              .then(() => {
                that.isSubmmiting = !that.isSubmmiting
              })
          } else {
            console.log('errror')
            that.isSubmmiting = !that.isSubmmiting
            return false
          }
        })
      }, 500)
    },
    resetFormData() {
      this.officesList = []
      this.$emit('close-modal')
      this.$refs.spmsForm.resetFields()
    },
    viewOfficeList(field) {
      const list = this.form[field]
      if (list.length) {
        this.openList = !this.openList
        const mappedList = this.mappedOfficeList(list, field)
        this.officeType = field
        this.officesList = mappedList
      } else {
        const modal = Modal.warning({
          title: 'Please select at least one (1) office',
          content: '',
        })
        setTimeout(() => {
          modal.destroy()
        }, 2500)
      }
    },
    mappedOfficeList(list, field) {
      const cascadeTo = field === 'implementing' ? 'core_functions' : 'support_functions'
      return list.map(item => {
        const container = {}
        let tempCascadeTo = ''
        container.value = item.value
        container.label = item.label
        tempCascadeTo = cascadeTo
        if (typeof (item.cascadeTo) !== 'undefined') {
          tempCascadeTo = item.cascadeTo
        }
        container.cascadeTo = tempCascadeTo
        return container
      })
    },
    toggleType(e) {
      const value = e.target.value
      if (value === 'sub') {
        this.isSubPI = true
      } else {
        this.isSubPI = false
      }
    },
    // Modal for managing where PI should be cascaded
    handleOk() {
      this.openList = !this.openList
      this.form[this.officeType] = this.officesList
      this.officesList = []
    },
    handleCancel() {
      this.openList = !this.openList
      this.officesList = []
    },
  },
}
</script>
