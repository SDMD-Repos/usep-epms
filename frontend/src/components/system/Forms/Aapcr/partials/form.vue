<template>
  <span>
    <a-drawer :title="otherData.modalTitle"
              :width="800"
              :visible="otherData.open"
              :mask-closable="false"
              :closable="false"
              :body-style="{ paddingBottom: '80px' }"
              @close="resetFormData">
      <a-form-model :ref="`${functionId}`" :model="form" :rules="rules" layout="horizontal" :hide-required-mark="true">
        <a-form-model-item label="Type"
                           :label-col="formItemLayout.labelCol"
                           :wrapper-col="formItemLayout.wrapperCol">
          <a-radio-group v-model="otherData.type" disabled>
            <a-radio value="pi">
              PI
            </a-radio>
            <a-radio value="sub">
              Sub PI
            </a-radio>
          </a-radio-group>
        </a-form-model-item>

        <div v-if="otherData.type === 'sub'">
          <a-row type="flex">
            <a-col :span="3" :offset="3">
              <label>Parent PI: </label>
            </a-col>
            <a-col :span="14">
              <p class="withNewLine">{{ otherData.parentDetails.name }}</p>
            </a-col>
          </a-row>
          <br>
        </div>

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
            :disabled="otherData.type === 'sub'"
            @change="changeNullValue($event, 'subCategory')"
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
          <template v-if="!form.isHeader && otherData.type !== 'sub'">
            <a-tooltip placement="right" :title="tooltipHeaderText">
              <a-switch v-model="form.isHeader" :disabled="otherData.type === 'sub'" @change="toggleIsHeader"/>
            </a-tooltip>
          </template>
          <a-switch v-else v-model="form.isHeader" :disabled="otherData.type === 'sub'" @change="toggleIsHeader"/>
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
            <a-select v-model="form.measures"
                      mode="multiple"
                      placeholder="Select"
                      style="width: 100%"
                      label-in-value
                      allow-clear
                      :disabled="otherData.type === 'sub' && !otherData.parentDetails.isHeader">
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
              :disabled="otherData.type === 'sub' && !otherData.parentDetails.isHeader"
            />
          </a-form-model-item>

          <a-form-model-item label="Casading Level"
                             prop="cascadingLevel"
                             :label-col="formItemLayout.labelCol"
                             :wrapper-col="formItemLayout.wrapperCol">
            <a-select v-model="form.cascadingLevel"
                      placeholder="Select"
                      style="width: 100%"
                      label-in-value
                      :disabled="otherData.type === 'sub' && !otherData.parentDetails.isHeader">
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
                  allow-clear
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
        <a-button :style="{ marginRight: '8px' }" @click="resetFormData(0)" :loading="isSubmmiting"
                  v-if="otherData.type === 'pi' || (otherData.type !== 'pi' && otherData.updateId !== null)">
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
        <a-button type="primary" @click="okModalAction" :loading="isSubmmiting">
          {{ otherData.okText }}
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
    formObject: Object,
    piFormData: Object,
    functionId: String,
    categories: Array,
    targetsBasisList: Array,
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
          this.$refs[this.functionId].validateField(rule.field)
          callback()
        }
      } else {
        this.$refs[this.functionId].validateField(rule.field)
        callback()
      }
    }
    const subCategoryValidator = (rule, value, callback) => {
      if ((this.functionId !== 'support_functions') && typeof value === 'undefined') {
        callback(new Error('Please select at least one'))
      } else {
        this.$refs[this.functionId].validateField(rule.field)
        callback()
      }
    }
    const piFormData = this.piFormData
    const formObject = this.formObject
    return {
      formItemLayout,
      SHOW_PARENT,
      isSubmmiting: false,
      otherData: piFormData,
      tooltipHeaderText: 'Check to disable editing of Target to Other Remarks',
      normalizer: {
        title: 'name',
        value: 'id',
      },
      form: formObject,
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
      openList: false,
      officeType: '',
      officesList: [],
    }
  },
  watch: {
    piFormData(val) {
      this.otherData = val
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
      }
      params = encodeURIComponent(JSON.stringify(params))
      this.$store.dispatch('external/FETCH_MAIN_OFFICES', { payload: params }) // needs to load first
      this.$store.dispatch('formSettings/FETCH_SUB_CATEGORIES')
      this.$store.dispatch('formSettings/FETCH_MEASURES')
      this.$store.dispatch('formSettings/FETCH_CASCADING_LEVELS')
    },
    filterBasisOption(input, option) {
      return (
        option.componentOptions.children[0].text.toUpperCase().indexOf(input.toUpperCase()) >= 0
      )
    },
    toggleIsHeader(checked) {
      if (checked) {
        const { form } = this
        form.target = ''
        form.measures = []
        form.budget = null
        form.targetsBasis = ''
        form.cascadingLevel = ''
        form.implementing = []
        form.supporting = []
        form.otherRemarks = ''
      }
    },
    changeNullValue(value, label) {
      if (typeof value === 'undefined' || value === 0) {
        this.form[label] = null
      }
    },
    okModalAction() {
      this.isSubmmiting = !this.isSubmmiting
      const { otherData, form } = this
      const tempImplementing = this.mappedOfficeList(form.implementing, 'implementing')
      const tempSupporting = this.mappedOfficeList(form.supporting, 'supporting')
      form.implementing = tempImplementing
      form.supporting = tempSupporting
      const that = this
      setTimeout(() => {
        this.$refs[this.functionId].validate(valid => {
          if (valid) {
            let msgContent = ''
            if (otherData.updateId === null) {
              this.$emit('add-table-item', form)
              msgContent = 'Added!'
            } else {
              this.$emit('update-table-item', { formData: form, updateId: otherData.updateId })
              msgContent = 'Updated!'
            }

            this.$emit('reset-form')
            if (otherData.type !== 'pi') {
              const { parentDetails } = otherData
              this.$emit('add-sub-pi', parentDetails.key)
            }
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
    resetFormData(newPI) {
      this.officesList = []
      this.$emit('close-modal', newPI)
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
