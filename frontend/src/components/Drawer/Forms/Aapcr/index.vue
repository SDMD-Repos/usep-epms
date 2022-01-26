<template>
  <a-drawer v-model:visible="config.open" :title="config.modalTitle" placement="right" :closable="false"
            :mask-closable="false" :body-style="{ paddingBottom: '80px' }" :width="800"
            @close="resetFormData">

    <a-form layout="horizontal" :hide-required-mark="true" :label-col="formItemLayout.labelCol" :wrapper-col="formItemLayout.wrapperCol">
      <a-form-item label="Type">
        <a-radio-group :options="typeOptions" v-model:value="config.type" disabled/>
      </a-form-item>

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

      <a-form-item v-bind="validateInfos.subCategory">
        <template #label>
          <span class="required-indicator" v-if="drawerId !== 'support_functions'">Sub Category</span>
        </template>
        <a-tree-select
          v-model:value="form.subCategory"
          style="width: 100%"
          :dropdown-style="{ maxHeight: '400px', overflow: 'auto' }"
          :tree-data="subCategories"
          placeholder="Select"
          :replace-fields="{ title: 'name', value: 'id',}"
          allow-clear
          tree-default-expand-all
          label-in-value
          :disabled="config.type === 'sub'"
          @change="changeNullValue($event, 'subCategory')"
        ></a-tree-select>
      </a-form-item>

      <a-form-item v-bind="validateInfos.name">
        <template #label>
          <span class="required-indicator">Performance Indicator</span>
        </template>
        <a-textarea v-model:value="form.name" auto-size />
      </a-form-item>

      <a-form-item label="Header PI?" v-bind="validateInfos.isHeader">
        <template v-if="!form.isHeader && config.type !== 'sub'">
          <a-tooltip placement="right" :title="tooltipHeaderText">
            <a-switch v-model:checked="form.isHeader" :disabled="config.type === 'sub'" @change="toggleIsHeader"/>
          </a-tooltip>
        </template>
        <a-switch v-else v-model:checked="form.isHeader" :disabled="config.type === 'sub'" @change="toggleIsHeader"/>
      </a-form-item>

      <template v-if="!form.isHeader">
        <a-form-item v-bind="validateInfos.target">
          <template #label><span class="required-indicator">Target</span></template>

          <a-input v-model:value="form.target" />
        </a-form-item>

        <a-form-item v-bind="validateInfos.measures">
          <template #label><span class="required-indicator">Measures</span></template>

          <a-select v-model:value="form.measures" mode="multiple" placeholder="Select"
                    style="width: 100%" label-in-value allow-clear >
            <a-select-option v-for="measure in measuresList" :value="measure.id" :key="measure.id">
              {{ measure.name }}
            </a-select-option>
          </a-select>
        </a-form-item>

        <a-form-item label="Allocated Budget" v-bind="validateInfos.budget" >
          <a-input-number v-model:value="form.budget" :min="0" style="width: 50%" :step="0.01"
                          :formatter="value => `${value}`.replace(/\B(?=(\d{3})+(?!\d))/g, ',')"
                          :parser="value => value.replace(/\$\s?|(,*)/g, '')"
                          />
        </a-form-item>

        <a-form-item v-bind="validateInfos.targetsBasis">
          <template #label><span class="required-indicator">Targets Basis</span></template>

          <a-auto-complete
            v-model:value="form.targetsBasis"
            :options="targetsBasisList"
            :filter-option="filterBasisOption"
            :disabled="config.type === 'sub' && !config.parentDetails.isHeader"
          />
        </a-form-item>

        <a-form-item v-bind="validateInfos.cascadingLevel">
          <template #label><span class="required-indicator">Casading Level</span></template>

          <a-select v-model:value="form.cascadingLevel" placeholder="Select" style="width: 100%"
                    label-in-value :disabled="config.type === 'sub' && !config.parentDetails.isHeader">
            <a-select-option v-for="levelItem in cascadingList" :value="levelItem.id" :key="levelItem.id">
              {{ levelItem.name }}
            </a-select-option>
          </a-select>
        </a-form-item>

        <a-form-item v-bind="validateInfos.implementing">
          <template #label><span class="required-indicator">Implementing Office</span></template>
          <a-row :gutter="0">
            <a-col :span="22">
              <a-tree-select
                v-model:value="form.options.implementing"
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
                @change="(value, label, extra) => { onOfficeChange(value, label, extra, 'implementing') }" />
            </a-col>
            <a-col :span="2">
              <a-tooltip :title="!form.implementing.length ? 'Save List' : 'Edit List'">
                <a-button v-if="!form.implementing.length" type="primary" @click="saveOfficeList('implementing')">
                  <template #icon><CheckOutlined /></template>
                </a-button>
                <a-button v-else type="primary" @click="updateOfficeList('implementing')">
                  <template #icon><EditOutlined /></template>
                </a-button>
              </a-tooltip>
            </a-col>
          </a-row>
        </a-form-item>

        <div v-if="form.implementing.length">
          <div v-for="(office, index) in form.implementing" :key="index">
            <a-row type="flex" align="middle">
              <a-col :span="5" :offset="3">
                <label>{{ typeof office.acronym !== 'undefined' ? office.acronym : office.label }} </label>
              </a-col>
              <a-col :span="8">
                <a-select v-model:value="form.implementing[index].cascadeTo" style="width: 100%">
                  <a-select-option v-for="category in categories" :value="category.id" :key="category.id">
                    {{ category.name }}
                  </a-select-option>
                </a-select>
              </a-col>
              <a-col :span="2" :offset="1">
                <DeleteFilled @click="deleteOfficeItem('implementing', index)"/>
              </a-col>
            </a-row>
            <br />
          </div>
        </div>

        <a-form-item v-bind="validateInfos.supporting">
          <template #label><span class="required-indicator">Supporting Office</span></template>
          <a-row :gutter="0">
            <a-col :span="22">
              <a-tree-select
                v-model:value="form.options.supporting"
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
                @change="(value, label, extra) => { onOfficeChange(value, label, extra, 'supporting') }" />
            </a-col>
            <a-col :span="2">
              <a-tooltip :title="!form.supporting.length ? 'Save List' : 'Edit List'">
                <a-button v-if="!form.supporting.length" type="primary" @click="saveOfficeList('supporting')">
                  <template #icon><CheckOutlined /></template>
                </a-button>
                <a-button v-else type="primary" @click="updateOfficeList('supporting')">
                  <template #icon><EditOutlined /></template>
                </a-button>
              </a-tooltip>
            </a-col>
          </a-row>
        </a-form-item>

        <div v-if="form.supporting.length">
          <div v-for="(office, index) in form.supporting" :key="index">
            <a-row type="flex" align="middle">
              <a-col :span="5" :offset="3">
                <label>{{ typeof office.acronym !== 'undefined' ? office.acronym : office.label }} </label>
              </a-col>
              <a-col :span="8">
                <a-select v-model:value="form.supporting[index].cascadeTo" style="width: 100%">
                  <a-select-option v-for="category in categories" :value="category.id" :key="category.id">
                    {{ category.name }}
                  </a-select-option>
                </a-select>
              </a-col>
              <a-col :span="2" :offset="1">
                <DeleteFilled @click="deleteOfficeItem('supporting', index)"/>
              </a-col>
            </a-row>
            <br />
          </div>
        </div>

        <a-form-item label="Other Remarks" v-bind="validateInfos.otherRemarks">
          <a-textarea v-model:value="form.otherRemarks" auto-size />
        </a-form-item>
      </template>
    </a-form>

    <div class="drawer-form-footer">
      <a-button  v-if="config.type === 'pi' || (config.type !== 'pi' && config.updateId !== null)"
                 :loading="isSubmmiting" style="margin-right: 8px" @click="resetFormData(0)">
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
      <a-button type="primary" @click="validateFields" :loading="isSubmmiting">{{ config.okText }}</a-button>
    </div>
  </a-drawer>
</template>
<script>
import { defineComponent, ref, watch, computed, reactive, onMounted, createVNode } from "vue"
import { useStore} from "vuex"
import { TreeSelect } from "ant-design-vue"
import { Modal, Form } from "ant-design-vue"
import { CheckOutlined, EditOutlined, DeleteFilled, ExclamationCircleOutlined } from '@ant-design/icons-vue'

const useForm = Form.useForm

export default defineComponent({
  name: "AapcrFormDrawer",
  components: { CheckOutlined, EditOutlined, DeleteFilled },
  props: {
    drawerConfig: { type: Object, default: () => { return {} }},
    formObject: { type: Object, default: () => { return {} }},
    drawerId: { type: String, default: "" },
    targetsBasisList: { type: Array, default: () => { return [] }},
    categories: { type: Array, default: () => { return [] }},
    currentYear: { type: Number, default: new Date().getFullYear() },
  },
  emits: ['close-drawer', 'toggle-is-header'],
  setup(props, { emit }) {
    const store = useStore()

    // DATA
    const config = ref({})
    const isSubmmiting = ref(false)
    const form = ref({})
    const storedOffices = ref({ implementing: [], supporting: [] })
    const cachedOffice = ref({ implementing: [], supporting: [] })

    const rules = reactive({
      subCategory: [
        { validator: subCategoryValidator, trigger: 'blur' },
        { type: 'object' },
      ],
      name: [{ required: true, message: 'This field is required' }],
      target: [{ validator: validateNonHeader, trigger: 'blur' }],
      measures: [{ validator: validateNonHeader, trigger: 'blur' }],
      targetsBasis: [{ validator: validateNonHeader, trigger: 'blur' }],
      cascadingLevel: [{ validator: validateNonHeader, trigger: 'blur' }],
      implementing: [
        { validator: validateNonHeader, trigger: 'blur' },
        { type: 'array' },
      ],
    })

    // STATIC DATA
    const SHOW_PARENT = TreeSelect.SHOW_PARENT
    const typeOptions = [{ label: 'PI', value: 'pi'}, { label: 'Sub PI', value: 'sub' }]
    const formItemLayout = { labelCol: { span: 6 }, wrapperCol: { span: 14 }}
    const tooltipHeaderText = 'Check to disable the editing of Target to Other Remarks'

    // COMPUTED
    const subCategories = computed(() => {
      const subs = store.getters['formManager/subCategories']
      return subs.filter(i => { return i.category_id === props.drawerId && i.parent_id === null})
    })

    const measuresList  = computed(() => store.getters['formManager/manager'].measures)
    const cascadingList  = computed(() => store.getters['formManager/manager'].cascadingLevels)
    const officesList  = computed(() => store.getters['external/external'].officesAccountable)

    // EVENTS
    watch(() => [props.drawerConfig, props.formObject] , ([drawerConfig, formObject]) => {
      config.value = drawerConfig
      form.value = formObject
    })

    onMounted(() => {
      onLoad()
    })

    // VALIDATORS
    const { resetFields, validate, validateInfos } = useForm(form, rules)

    const validateNonHeader = (rule, value) => {
      if (!form.value.isHeader) {
        if (value === '' || (Array.isArray(value) && !value.length) || typeof value === 'undefined') {
          if (rule.field === 'implementing' && form.value.options.implementing.length) {
            return Promise.reject('Please click the check icon to save the data')
          } else {
            return Promise.reject('This field is required')
          }
        } else {
          return Promise.resolve()
        }
      } else {
        return Promise.resolve()
      }
    }

    const subCategoryValidator = (rule, value) => {
      if ((props.drawerId !== 'support_functions') && value === null) {
        return Promise.reject('Please select at least one')
      } else {
        return Promise.resolve()
      }
    }

    // METHODS
    const onLoad = () => {
      let params = {
        checkable: {
          allColleges: true,
          mains: true,
        },
        isAcronym: true,
        currentYear: props.currentYear,
      }
      params = encodeURIComponent(JSON.stringify(params))
      store.dispatch('external/FETCH_OFFICES_ACCOUNTABLE', { payload: params })
    }

    const changeNullValue = (value, label) => {
      if (typeof value === 'undefined' || value === 0) {
        form.value[label] = null
      }
    }

    const filterBasisOption = (input, option) => {
      return (
        option.componentOptions.children[0].text.toUpperCase().indexOf(input.toUpperCase()) >= 0
      )
    }

    const toggleIsHeader = checked => {
      if(checked) {
        emit('toggle-is-header')
      }
    }

    const onOfficeChange = (value, label, extra, field) => {
      storedOffices.value[field] = []
      const { allCheckedNodes } = extra
      if (typeof allCheckedNodes !== 'undefined' && allCheckedNodes.length > 0) {
        allCheckedNodes.forEach(item => {
          const { dataRef } = (typeof item.node !== 'undefined') ? item.node.props : item.data.props
          storedOffices.value[field].push(dataRef)
        })
      }
    }

    const deleteOfficeItem = (field, index) => {
      Modal.confirm({
        title: () => 'Are you sure you want to delete this?',
        icon: () => createVNode(ExclamationCircleOutlined),
        content: () => '',
        okText: 'Yes',
        cancelText: 'No',
        onOk() {
          form.value[field].splice(index, 1)
        },
        onCancel() {},
      })
    }

    const saveOfficeList = field => {
      const list = storedOffices.value[field]
      form.value[field] = mappedOfficeList(list, field)
      form.value.options[field] = []
      storedOffices.value[field] = []
      if (cachedOffice.value[field].length) {
        cachedOffice.value[field] = []
      }
    }

    const updateOfficeList = field => {
      form.value.options[field] = form.value[field]
      cachedOffice.value[field] = form.value[field]
      storedOffices.value[field] = form.value[field]
      form.value[field] = []
    }

    const mappedOfficeList = (list, field) => {
      const cascadeTo = field === 'implementing' ? 'core_functions' : 'support_functions'
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
        const hasCached = cachedOffice.value[field].filter(i => i.value === item.value)
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
    }

    const resetFormData = isNewIndicator => {
      emit('close-drawer', isNewIndicator)
    }

    const validateFields = () => {
      console.log('validated')
    }

    return {
      SHOW_PARENT,
      config,
      isSubmmiting,
      form,

      typeOptions,
      formItemLayout,
      tooltipHeaderText,

      subCategories,
      measuresList,
      cascadingList,
      officesList,

      validateInfos,

      changeNullValue,
      filterBasisOption,
      toggleIsHeader,
      onOfficeChange,
      deleteOfficeItem,
      saveOfficeList,
      updateOfficeList,
      resetFormData,
      validateFields,
    }
  },
})
</script>
