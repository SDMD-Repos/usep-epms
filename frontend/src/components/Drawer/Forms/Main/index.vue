<template>
  <a-drawer v-model:visible="config.open" :title="config.modalTitle" placement="right" :closable="false"
            :mask-closable="false" :body-style="{ paddingBottom: '80px' }" :width="800"
            @close="resetFormData">

    <a-form layout="horizontal" :hide-required-mark="true" :label-col="formItemLayout.labelCol" :wrapper-col="formItemLayout.wrapperCol">
      <a-form-item label="Type">
        <a-radio-group :options="typeOptions" v-model:value="config.type" disabled/>
      </a-form-item>

      <a-form-item>
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

      <a-form-item>
        <template #label>
          <span class="required-indicator">Performance Indicator</span>
        </template>
        <a-textarea v-model:value="form.name" auto-size />
      </a-form-item>

      <a-form-item label="Header PI?">
        <template v-if="!form.isHeader && config.type !== 'sub'">
          <a-tooltip placement="right" :title="tooltipHeaderText">
            <a-switch v-model:checked="form.isHeader" :disabled="config.type === 'sub'" @change="toggleIsHeader"/>
          </a-tooltip>
        </template>
        <a-switch v-else v-model:checked="form.isHeader" :disabled="config.type === 'sub'" @change="toggleIsHeader"/>
      </a-form-item>

      <template v-if="!form.isHeader">
        <a-form-item>
          <template #label><span class="required-indicator">Target</span></template>

          <a-input v-model:value="form.target" />
        </a-form-item>

        <a-form-item>
          <template #label><span class="required-indicator">Measures</span></template>

          <a-select v-model:value="form.measures" mode="multiple" placeholder="Select"
                    style="width: 100%" label-in-value allow-clear >
            <a-select-option v-for="measure in measuresList" :value="measure.id" :key="measure.id">
              {{ measure.name }}
            </a-select-option>
          </a-select>
        </a-form-item>

        <a-form-item label="Allocated Budget">
          <a-input-number v-model:value="form.budget" :min="0" style="width: 50%" :step="0.01"
                          :formatter="value => `${value}`.replace(/\B(?=(\d{3})+(?!\d))/g, ',')"
                          :parser="value => value.replace(/\$\s?|(,*)/g, '')"
                          />
        </a-form-item>

        <a-form-item>
          <template #label><span class="required-indicator">Targets Basis</span></template>

          <a-auto-complete
            v-model="form.targetsBasis"
            :options="targetsBasisList"
            :filter-option="filterBasisOption"
            :disabled="config.type === 'sub' && !config.parentDetails.isHeader"
          />
        </a-form-item>

        <a-form-item>
          <template #label><span class="required-indicator">Casading Level</span></template>

          <a-select v-model:value="form.cascadingLevel" placeholder="Select" style="width: 100%"
                    label-in-value :disabled="config.type === 'sub' && !config.parentDetails.isHeader">
            <a-select-option v-for="levelItem in cascadingList" :value="levelItem.id" :key="levelItem.id">
              {{ levelItem.name }}
            </a-select-option>
          </a-select>
        </a-form-item>

        <a-form-item>
          <template #label><span class="required-indicator">Casading Level</span></template>
          <a-row :gutter="24">
            <a-col :span="8"></a-col>
            <a-col :span="8"></a-col>
          </a-row>
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
import {defineComponent, ref, watch, computed, onMounted} from "vue"
import { useStore} from "vuex"

export default defineComponent({
  name: "IndicatorFormDrawer",
  props: {
    drawerConfig: { type: Object, default: () => { return {} }},
    formObject: { type: Object, default: () => { return {} }},
    drawerId: { type: String, default: "" },
    targetsBasisList: { type: Array, default: () => { return [] }},
  },
  emits: ['close-drawer', 'toggle-is-header'],
  setup(props, { emit }) {
    const store = useStore()

    // DATA
    const config = ref({})
    const isSubmmiting = ref(false)
    const form = ref({})

    // STATIC DATA
    const typeOptions = ['pi','sub']
    const formItemLayout = { labelCol: { span: 6 }, wrapperCol: { span: 14 }}
    const tooltipHeaderText = 'Check to disable the editing of Target to Other Remarks'

    // COMPUTED
    const subCategories = computed(() => {
      const subs = store.getters['formManager/subCategories']
      return subs.filter(i => { return i.category_id === props.drawerId && i.parent_id === null})
    })

    const measuresList  = computed(() => store.getters['formManager/manager'].measures)
    const cascadingList  = computed(() => store.getters['formManager/manager'].cascadingLevels)

    // EVENTS
    watch(() => [props.drawerConfig, props.formObject] , ([drawerConfig, formObject]) => {
      config.value = drawerConfig
      form.value = formObject
    })

    // onMounted(() => {
    //   initializeFormFields()
    // })

    // METHODS
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
        // storedOffices.implementing = []
        // storedOffices.supporting = []
      }
    }

    const resetFormData = isNewIndicator => {
      emit('close-drawer', isNewIndicator)
    }

    const validateFields = () => {
      console.log('validated')
    }

    return {
      config,
      isSubmmiting,
      form,

      typeOptions,
      formItemLayout,
      tooltipHeaderText,

      subCategories,
      measuresList,
      cascadingList,

      changeNullValue,
      filterBasisOption,
      toggleIsHeader,
      resetFormData,
      validateFields,
    }
  },
})
</script>
