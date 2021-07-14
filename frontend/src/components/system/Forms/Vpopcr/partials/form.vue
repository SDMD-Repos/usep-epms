<template>
  <div>
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

        <a-form-model-item label="Program"
                           prop="program"
                           :label-col="formItemLayout.labelCol"
                           :wrapper-col="formItemLayout.wrapperCol">
          <a-select v-model="form.program"
                    placeholder="Select"
                    :disabled="config.type === 'sub'"
                    style="width: 100%" allow-clear>
            <a-select-option v-for="p in filteredProgram" :value="p.id" :key="p.id">
              {{ p.name }}
            </a-select-option>
          </a-select>
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
            :disabled="config.type === 'sub'"
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
          <template v-if="!form.isHeader && config.type !== 'sub'">
            <a-tooltip placement="right" :title="tooltipHeaderText">
              <a-switch v-model="form.isHeader" :disabled="config.type === 'sub'" @change="toggleIsHeader"/>
            </a-tooltip>
          </template>
          <a-switch v-else v-model="form.isHeader" :disabled="config.type === 'sub'" @change="toggleIsHeader"/>
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
                      :disabled="config.type === 'sub' && !config.parentDetails.isHeader">
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
              :disabled="config.type === 'sub' && !config.parentDetails.isHeader"
            />
          </a-form-model-item>

          <a-form-model-item label="Implementing Office"
                             prop="implementing"
                             :label-col="formItemLayout.labelCol"
                             :wrapper-col="formItemLayout.wrapperCol">
            <div class="row">
              <div class="col-sm-9 col-lg-10">
                <a-tree-select
                  v-model="form.options.implementing"
                  style="width: 100%"
                  :dropdown-style="{ maxHeight: '400px', overflow: 'auto' }"
                  :tree-data="mainOfficesChildrenList"
                  placeholder="Select an office/s"
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
                <a-col :span="3" :offset="4">
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
                  :tree-data="mainOfficesChildrenList"
                  placeholder="Select an office/s"
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
                <a-col :span="3" :offset="4">
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

          <a-form-model-item label="Remarks"
                             prop="remarks"
                             :label-col="formItemLayout.labelCol"
                             :wrapper-col="formItemLayout.wrapperCol">
            <a-textarea v-model="form.remarks" auto-size />
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
  </div>
</template>
<script>
import { mapState } from 'vuex'
import FormMixin from '@/services/formMixins/form'

export default {
  mixins: [FormMixin],
  props: {
  },
  computed: {
    ...mapState({
      programList: state => state.formSettings.programs,
    }),
    filteredProgram() {
      return this.programList.filter(i => i.category_id === this.drawerId)
    },
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
      const { drawerId, config } = this
      const hasParent = (typeof config.parentDetails === 'undefined') || (typeof config.parentDetails !== 'undefined' && config.parentDetails.subCategory !== null)
      if (drawerId !== 'support_functions' && value === null && hasParent) {
        callback(new Error('Please select at least one'))
      } else {
        this.$refs[drawerId].validateField(rule.field)
        callback()
      }
    }
    const programValidator = (rule, value, callback) => {
      const { drawerId, config } = this
      const hasParent = (typeof config.parentDetails === 'undefined') || (typeof config.parentDetails !== 'undefined' && config.parentDetails.subCategory !== null)
      if (value === null && hasParent) {
        callback(new Error('Please select at least one'))
      } else {
        this.$refs[drawerId].validateField(rule.field)
        callback()
      }
    }
    return {
      tooltipHeaderText: 'Check to disable editing of Target to Other Remarks',
      rules: {
        program: [{ validator: programValidator, trigger: 'blur' }],
        subCategory: [
          { validator: subCategoryValidator, trigger: 'blur' },
          { type: 'object' },
        ],
        name: [{ required: true, message: 'This field is required', trigger: 'blur' }],
        target: [{ validator: validateNonHeader, trigger: 'blur' }],
        measures: [{ validator: validateNonHeader, trigger: 'blur' }],
        targetsBasis: [{ validator: validateNonHeader, trigger: 'blur' }],
        implementing: [
          { validator: validateNonHeader, trigger: 'blur' },
          { type: 'array' },
        ],
      },
    }
  },
  watch: {
  },
  created() {
    this.onLoad()
  },
  methods: {
    onLoad() {
      let params = {
        checkable: {
          allColleges: true,
          mains: false,
        },
        isAcronym: true,
      }
      params = encodeURIComponent(JSON.stringify(params))
      this.$store.dispatch('external/FETCH_MAIN_OFFICES_CHILDREN', { payload: params }) // needs to load first
    },
    toggleIsHeader(checked) {
      if (checked) {
        const { form, storedOffices } = this
        form.target = ''
        form.measures = []
        form.budget = null
        form.targetsBasis = ''
        form.implementing = []
        form.supporting = []
        form.options.implementing = []
        form.options.supporting = []
        form.remarks = ''
        storedOffices.implementing = []
        storedOffices.supporting = []
      }
    },
  },
}
</script>
