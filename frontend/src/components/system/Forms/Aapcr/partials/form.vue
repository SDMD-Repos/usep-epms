<template>
  <span>
    <a-drawer :title="config.modalTitle"
              :width="800"
              :visible="config.open"
              :mask-closable="false"
              :closable="false"
              :body-style="{ paddingBottom: '80px' }"
              @close="resetFormData">
      <a-form-model :ref="`${drawerId}`" :model="form" :rules="rules" layout="horizontal" :hide-required-mark="true">
        <a-form-model-item label="Type"
                           :label-col="formItemLayout.labelCol"
                           :wrapper-col="formItemLayout.wrapperCol">
          <a-radio-group v-model="config.type" disabled>
            <a-radio value="pi">
              PI
            </a-radio>
            <a-radio value="sub">
              Sub PI
            </a-radio>
          </a-radio-group>
        </a-form-model-item>

        <div v-if="config.type === 'sub'">
          <a-row type="flex">
            <a-col :span="3" :offset="3">
              <label>Parent PI: </label>
            </a-col>
            <a-col :span="14">
              <p class="withNewLine">{{ config.parentDetails.name }}</p>
            </a-col>
          </a-row>
          <br>
        </div>

        <a-form-model-item prop="subCategory"
                           :label-col="formItemLayout.labelCol"
                           :wrapper-col="formItemLayout.wrapperCol">
          <span slot="label">
            <p class="required-asterisk" v-if="drawerId !== 'support_functions'">*</p> Sub Category
          </span>
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
            :disabled="config.type === 'sub'"
            @change="changeNullValue($event, 'subCategory')"
          ></a-tree-select>
        </a-form-model-item>

        <a-form-model-item prop="name"
                           :label-col="formItemLayout.labelCol"
                           :wrapper-col="formItemLayout.wrapperCol">
          <span slot="label">
            <p class="required-asterisk">*</p> Performance Indicator
          </span>
          <a-textarea v-model="form.name" auto-size/>
        </a-form-model-item>

        <a-form-model-item label="Header PI?"
                           prop="isHeader"
                           :label-col="formItemLayout.labelCol"
                           :wrapper-col="formItemLayout.wrapperCol">
          <template v-if="!form.isHeader && config.type !== 'sub'">
            <a-tooltip placement="right" :title="tooltipHeaderText">
              <a-switch v-model="form.isHeader" :disabled="config.type === 'sub'" @change="toggleIsHeader"/>
            </a-tooltip>
          </template>
          <a-switch v-else v-model="form.isHeader" :disabled="config.type === 'sub'" @change="toggleIsHeader"/>
        </a-form-model-item>

        <template v-if="!form.isHeader">
          <a-form-model-item prop="target"
                             :label-col="formItemLayout.labelCol"
                             :wrapper-col="formItemLayout.wrapperCol">
            <span slot="label">
              <p class="required-asterisk">*</p> Target
            </span>
            <a-input v-model="form.target" />
          </a-form-model-item>

          <a-form-model-item prop="measures"
                             :label-col="formItemLayout.labelCol"
                             :wrapper-col="formItemLayout.wrapperCol">
            <span slot="label">
              <p class="required-asterisk">*</p> Measures
            </span>
            <a-select v-model="form.measures"
                      mode="multiple"
                      placeholder="Select"
                      style="width: 100%"
                      label-in-value
                      allow-clear
                      :disabled="config.type === 'sub' && !config.parentDetails.isHeader">
              <a-select-option v-for="measure in measuresList" :value="measure.id" :key="measure.id">
                {{ measure.name }}
              </a-select-option>
            </a-select>
          </a-form-model-item>

          <a-form-model-item label="Allocated Budget" prop="budget"
                             :label-col="formItemLayout.labelCol"
                             :wrapper-col="formItemLayout.wrapperCol">
            <a-input-number v-model="form.budget"
                            style="width: 50%"
                            :step="0.01"
                            :formatter="value => `${value}`.replace(/\B(?=(\d{3})+(?!\d))/g, ',')"
                            :parser="value => value.replace(/\$\s?|(,*)/g, '')"
                            :min="0"/>
          </a-form-model-item>

          <a-form-model-item prop="targetsBasis"
                             :label-col="formItemLayout.labelCol"
                             :wrapper-col="formItemLayout.wrapperCol">
            <span slot="label">
              <p class="required-asterisk">*</p> Targets Basis
            </span>
            <a-auto-complete
              v-model="form.targetsBasis"
              :data-source="targetsBasisList"
              :filter-option="filterBasisOption"
              :disabled="config.type === 'sub' && !config.parentDetails.isHeader"
            />
          </a-form-model-item>

          <a-form-model-item prop="cascadingLevel"
                             :label-col="formItemLayout.labelCol"
                             :wrapper-col="formItemLayout.wrapperCol">
            <span slot="label">
              <p class="required-asterisk">*</p> Casading Level
            </span>
            <a-select v-model="form.cascadingLevel"
                      placeholder="Select"
                      style="width: 100%"
                      label-in-value
                      :disabled="config.type === 'sub' && !config.parentDetails.isHeader">
              <a-select-option v-for="levelItem in cascadingList" :value="levelItem.id" :key="levelItem.id">
                {{ levelItem.name }}
              </a-select-option>
            </a-select>
          </a-form-model-item>

          <a-form-model-item prop="implementing"
                             :label-col="formItemLayout.labelCol"
                             :wrapper-col="formItemLayout.wrapperCol">
            <span slot="label">
              <p class="required-asterisk">*</p> Implementing Office
            </span>
            <div class="row">
              <div class="col-sm-9 col-lg-10">
                <a-tree-select
                  v-model="form.options.implementing"
                  style="width: 100%"
                  :dropdown-style="{ maxHeight: '400px', overflow: 'auto' }"
                  :tree-data="officesList"
                  placeholder="Select an office/s"
                  tree-node-filter-prop="title"
                  :show-checked-strategy="SHOW_PARENT"
                  :max-tag-count="6"
                  :disabled="form.implementing.length > 0"
                  allow-clear
                  tree-checkable
                  label-in-value
                  @change="onOfficeChange(...arguments, 'implementing')" />
              </div>
              <div class="col-sm-2 col-lg-2">
                <a-tooltip :title="!form.implementing.length ? 'Save List' : 'Edit List'">
                  <a-icon v-if="!form.implementing.length"
                          type="check"
                          :style="{ fontSize: '18px', cursor: 'pointer' }"
                          @click="saveOfficeList('implementing')"/>
                  <a-icon v-else
                          type="edit"
                          :style="{ fontSize: '18px', cursor: 'pointer' }"
                          @click="updateOfficeList('implementing')"/>
                </a-tooltip>
              </div>
            </div>
          </a-form-model-item>

          <div v-if="form.implementing.length">
            <div v-for="(office, index) in form.implementing" v-bind:key="index">
              <a-row type="flex" align="middle">
                <a-col :span="5" :offset="3">
                  <label>{{ typeof office.acronym !== 'undefined' ? office.acronym : office.label }} </label>
                </a-col>
                <a-col :span="8">
                  <a-select v-model="form.implementing[index].cascadeTo" style="width: 100%">
                    <a-select-option v-for="category in categories" :value="category.id" :key="category.id">
                      {{ category.name }}
                    </a-select-option>
                  </a-select>
                </a-col>
                <a-col :span="2" :offset="1">
                  <a-icon type="delete"
                          theme="filled"
                          :style="{ fontSize: '18px'}"
                          @click="deleteOfficeItem('implementing', index)"/>
                </a-col>
              </a-row>
              <br />
            </div>
          </div>

          <a-form-model-item label="Supporting Office"
                             prop="supporting"
                             :label-col="formItemLayout.labelCol"
                             :wrapper-col="formItemLayout.wrapperCol">
            <div class="row">
              <div class="col-sm-9 col-lg-10">
                <a-tree-select
                  v-model="form.options.supporting"
                  style="width: 100%"
                  :dropdown-style="{ maxHeight: '400px', overflow: 'auto' }"
                  :tree-data="officesList"
                  placeholder="Select an office/s"
                  tree-node-filter-prop="title"
                  :show-checked-strategy="SHOW_PARENT"
                  :max-tag-count="6"
                  :disabled="form.supporting.length > 0"
                  allow-clear
                  tree-checkable
                  label-in-value
                  @change="onOfficeChange(...arguments, 'supporting')"
                />
              </div>
              <div class="col-sm-2 col-lg-2">
                <a-tooltip :title="!form.supporting.length ? 'Save List' : 'Edit List'">
                  <a-icon v-if="!form.supporting.length" type="check"
                          :style="{ fontSize: '18px', cursor: 'pointer' }"
                          @click="saveOfficeList('supporting')"/>
                  <a-icon v-else
                          type="edit"
                          :style="{ fontSize: '18px', cursor: 'pointer' }"
                          @click="updateOfficeList('supporting')"/>
                </a-tooltip>
              </div>
            </div>
          </a-form-model-item>

          <div v-if="form.supporting.length" >
            <div v-for="(office, index) in form.supporting" v-bind:key="index">
              <a-row type="flex" align="middle">
                <a-col :span="5" :offset="3">
                  <label>{{ typeof office.acronym !== 'undefined' ? office.acronym : office.label }} </label>
                </a-col>
                <a-col :span="8">
                  <a-select v-model="form.supporting[index].cascadeTo" style="width: 100%">
                    <a-select-option v-for="category in categories" :value="category.id" :key="category.id">
                      {{ category.name }}
                    </a-select-option>
                  </a-select>
                </a-col>
                <a-col :span="2" :offset="1">
                  <a-icon type="delete"
                          theme="filled"
                          :style="{ fontSize: '18px'}"
                          @click="deleteOfficeItem('supporting', index)"/>
                </a-col>
              </a-row>
              <br />
            </div>
          </div>

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
        <a-button :style="{ marginRight: '8px' }" @click="resetFormData(0)" :loading="isSubmmiting"
                  v-if="config.type === 'pi' || (config.type !== 'pi' && config.updateId !== null)">
          Cancel
        </a-button>
        <template v-else>
          <a-popconfirm
            title="Create a new parent PI?"
            placement="top"
            ok-text="Yes"
            cancel-text="No"
            @confirm="resetFormData(1)"
            @cancel="resetFormData(0)"
          >
            <a-button :style="{ marginRight: '8px' }" :loading="isSubmmiting" >
              Cancel
            </a-button>
          </a-popconfirm>
        </template>
        <a-button type="primary" @click="validateFields" :loading="isSubmmiting">
          {{ config.okText }}
        </a-button>
      </div>
    </a-drawer>
  </span>
</template>

<script>
import { mapState } from 'vuex'
import { TreeSelect, Modal } from 'ant-design-vue'
const SHOW_PARENT = TreeSelect.SHOW_PARENT

const messageKey = 'updatable'

export default {
  name: 'drawer-detail-form',
  props: {
    currentYear: Number,
    formObject: Object,
    drawerConfig: Object,
    drawerId: String,
    categories: Array,
    targetsBasisList: Array,
  },
  computed: {
    ...mapState({
      subCategoryList: state => state.formManager.subCategories,
      measuresList: state => state.formManager.measures,
      cascadingList: state => state.formManager.cascadingLevels,
      officesList: state => state.external.officesAccountable,
      loading: state => state.formManager.loading,
    }),
    filteredSubCategory() {
      return this.subCategoryList.filter((i) => {
        return i.category_id === this.drawerId && i.parent_id === null
      })
    },
  },
  data() {
    const validateNonHeader = (rule, value, callback) => {
      if (!this.form.isHeader) {
        if (value === '' || (Array.isArray(value) && !value.length) || typeof value === 'undefined') {
          if (rule.field === 'implementing' && this.form.options.implementing.length) {
            callback(new Error('Please click the check icon to save the data'))
          } else {
            callback(new Error('This field is required'))
          }
        } else {
          this.$refs[this.drawerId].validateField(rule.field)
          callback()
        }
      } else {
        this.$refs[this.drawerId].validateField(rule.field)
        callback()
      }
    }
    const subCategoryValidator = (rule, value, callback) => {
      if ((this.drawerId !== 'support_functions') && value === null) {
        callback(new Error('Please select at least one'))
      } else {
        this.$refs[this.drawerId].validateField(rule.field)
        callback()
      }
    }
    const drawerConfig = this.drawerConfig
    const formObject = this.formObject
    return {
      formItemLayout: {
        labelCol: { span: 6 },
        wrapperCol: { span: 14 },
      },
      SHOW_PARENT,
      isSubmmiting: false,
      config: drawerConfig,
      tooltipHeaderText: 'Check to disable editing of Target to Other Remarks',
      normalizer: {
        title: 'name',
        value: 'id',
      },
      form: formObject,
      cachedOffice: {
        implementing: [],
        supporting: [],
      },
      storedOffices: {
        implementing: [],
        supporting: [],
      },
      rules: {
        subCategory: [
          { validator: subCategoryValidator, trigger: 'blur' },
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
      },
    }
  },
  watch: {
    drawerConfig(val) {
      this.config = val
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
      let params = {
        checkable: {
          allColleges: true,
          mains: true,
        },
        isAcronym: true,
        currentYear: this.currentYear,
      }
      params = encodeURIComponent(JSON.stringify(params))
      this.$store.dispatch('external/FETCH_OFFICES_ACCOUNTABLE', { payload: params }) // needs to load first
    },
    onOfficeChange() {
      const args = [...arguments] /* 0 - value, 1 - label, 2 - extra, 3 - field */
      const extra = args[2]
      const field = args[3]
      this.storedOffices[field] = []
      const { allCheckedNodes } = extra
      if (typeof allCheckedNodes !== 'undefined' && allCheckedNodes.length > 0) {
        allCheckedNodes.forEach(item => {
          const { dataRef } = (typeof item.node !== 'undefined') ? item.node.data.props : item.data.props
          this.storedOffices[field].push(dataRef)
        })
      }
    },
    filterBasisOption(input, option) {
      return (
        option.componentOptions.children[0].text.toUpperCase().indexOf(input.toUpperCase()) >= 0
      )
    },
    toggleIsHeader(checked) {
      if (checked) {
        const { form, storedOffices } = this
        form.target = ''
        form.measures = []
        form.budget = null
        form.targetsBasis = ''
        form.cascadingLevel = ''
        form.implementing = []
        form.supporting = []
        form.options.implementing = []
        form.options.supporting = []
        form.otherRemarks = ''
        storedOffices.implementing = []
        storedOffices.supporting = []
      }
    },
    changeNullValue(value, label) {
      if (typeof value === 'undefined' || value === 0) {
        this.form[label] = null
      }
    },
    validateFields() {
      this.isSubmmiting = !this.isSubmmiting
      const { form } = this
      const tempImplementing = this.mappedOfficeList(form.implementing, 'implementing')
      const tempSupporting = this.mappedOfficeList(form.supporting, 'supporting')
      form.implementing = tempImplementing
      form.supporting = tempSupporting
      const self = this
      setTimeout(() => {
        this.$refs[this.drawerId].validate(valid => {
          if (valid) {
            if (form.options.supporting.length) {
              Modal.confirm({
                title: 'The Supporting Office was not saved',
                content: 'Data will be lost if you proceed. Do you still want to continue?',
                okText: 'Yes',
                cancelText: 'No',
                onOk() {
                  self.saveForm()
                },
                onCancel() {
                  self.isSubmmiting = !self.isSubmmiting
                },
              })
            } else {
              self.saveForm()
            }
          } else {
            console.log('errror')
            self.isSubmmiting = !self.isSubmmiting
            return false
          }
        })
      }, 500)
    },
    saveForm() {
      const { config, form } = this
      let msgContent = ''
      const self = this
      for (var office in this.storedOffices) {
        self.storedOffices[office] = []
      }
      if (config.updateId === null) {
        this.$emit('add-table-item', form)
        msgContent = 'Added!'
      } else {
        this.$emit('update-table-item', { formData: form, updateId: config.updateId })
        msgContent = 'Updated!'
      }

      this.$emit('reset-form')
      if (config.type !== 'pi') {
        const { parentDetails } = config
        this.$emit('add-sub-pi', parentDetails.key)
      }
      this.$message.success({ content: msgContent, messageKey, duration: 2 })
        .then(() => {
          self.isSubmmiting = !self.isSubmmiting
        })
    },
    resetFormData(newPI) {
      this.$emit('close-modal', newPI)
    },
    saveOfficeList(field) {
      const { form, cachedOffice, storedOffices } = this
      const list = storedOffices[field]
      form[field] = this.mappedOfficeList(list, field)
      form.options[field] = []
      storedOffices[field] = []
      if (cachedOffice[field].length) {
        cachedOffice[field] = []
      }
    },
    updateOfficeList(field) {
      const { form, cachedOffice, storedOffices } = this
      form.options[field] = form[field]
      cachedOffice[field] = form[field]
      storedOffices[field] = form[field]
      form[field] = []
    },
    deleteOfficeItem(field, index) {
      const { form } = this
      Modal.confirm({
        title: 'Are you sure you want to delete this?',
        content: '',
        okText: 'Yes',
        cancelText: 'No',
        onOk() {
          form[field].splice(index, 1)
        },
        onCancel() {},
      })
    },
    mappedOfficeList(list, field) {
      const cascadeTo = field === 'implementing' ? 'core_functions' : 'support_functions'
      const { cachedOffice } = this
      return list.map(item => {
        const container = {}
        let tempCascadeTo = ''
        container.value = item.value
        container.label = typeof item.title !== 'undefined' ? item.title : item.label
        if (typeof item.children !== 'undefined') {
          container.children = true
        } else {
          if (typeof item.isGroup === 'undefined') {
            container.acronym = item.acronym
          }
          container.pId = item.pId
        }
        const hasCached = cachedOffice[field].filter(i => i.value === item.value)
        if (hasCached.length) {
          tempCascadeTo = hasCached[0].cascadeTo
        } else if (typeof (item.cascadeTo) !== 'undefined' && item.cascadeTo) {
          tempCascadeTo = item.cascadeTo
        } else {
          tempCascadeTo = cascadeTo
        }
        container.cascadeTo = tempCascadeTo
        if (typeof item.isGroup !== 'undefined') {
          container.isGroup = item.isGroup
        }
        return container
      })
    },
  },
}
</script>
