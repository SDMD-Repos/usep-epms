<template>
  <a-drawer v-model:visible="config.open" :title="config.modalTitle" placement="right" :closable="false"
            :mask-closable="false" :body-style="{ paddingBottom: '80px' }" :width="800"
            @close="resetFormData(0)">

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
          <span>Sub Category</span>
        </template>
        <a-tree-select
          v-model:value="form.subCategory" style="width: 100%" placeholder="Select"
          :dropdown-style="{ maxHeight: '400px', overflow: 'auto' }"
          :tree-data="subCategories" :replace-fields="{ title: 'name', value: 'id',}"
          allow-clear tree-default-expand-all label-in-value
          :disabled="config.type === 'sub'"
          @change="changeNullValue($event, 'subCategory')"
        ></a-tree-select>
      </a-form-item>

      <a-form-item v-bind="validateInfos.name">
        <template #label>
          <span class="required-indicator">Performance Indicator</span>
        </template>
        <a-textarea v-model:value="form.name" auto-size
                    @blur="validate('name', { trigger: 'blur' }).catch(() => {})"/>
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

          <a-input v-model:value="form.target"
                   @blur="validate('target', { trigger: 'blur' }).catch(() => {})"/>
        </a-form-item>

        <a-form-item v-bind="validateInfos.measures">
          <template #label><span class="required-indicator">Measures</span></template>

          <a-select v-model:value="form.measures" mode="multiple" placeholder="Select"
                    style="width: 100%" label-in-value allow-clear
                    @blur="validate('measures', { trigger: 'blur' }).catch(() => {})" >
            <a-select-option v-for="measure in measuresList" :value="measure.id.toString()" :key="measure.id">
              {{ measure.name }}
            </a-select-option>
          </a-select>
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
          title="Create a new parent PI?" placement="top" ok-text="Yes" cancel-text="No"
          @confirm="resetFormData(1)" @cancel="resetFormData(0)" >
          <a-button :style="{ marginRight: '8px' }" :loading="isSubmmiting" >
            Cancel
          </a-button>
        </a-popconfirm>
      </template>
      <a-button type="primary" @click.prevent="validateFields" :loading="isSubmmiting">{{ config.okText }}</a-button>
    </div>
  </a-drawer>
</template>
<script>
import { defineComponent, ref, watch, computed, onMounted  } from 'vue'
import { useStore } from 'vuex'
import { TreeSelect, message } from 'ant-design-vue'
import { useFormFields } from '@/services/functions/form/main'

export default defineComponent({
  name: "OpcrTemplateFormDrawer",
  components: { },
  props: {
    drawerConfig: { type: Object, default: () => { return {} }},
    formObject: { type: Object, default: () => { return {} }},
    drawerId: { type: Number, default: null },
    categories: { type: Array, default: () => { return [] }},
    currentYear: { type: Number, default: new Date().getFullYear() },
    validate: { type: Function, default: () => {} },
    validateInfos: { type: Object, default: () => { return {} }},
  },
  emits: ['close-drawer', 'toggle-is-header', 'add-table-item', 'update-table-item'],
  setup(props, { emit }) {
    const store = useStore()

    // DATA
    const config = ref({})
    const isSubmmiting = ref(false)
    const form = ref({})

    // STATIC DATA
    const SHOW_PARENT = TreeSelect.SHOW_PARENT
    const typeOptions = [{ label: 'PI', value: 'pi'}, { label: 'Sub PI', value: 'sub' }]
    const formItemLayout = { labelCol: { span: 6 }, wrapperCol: { span: 14 }}
    const tooltipHeaderText = 'Check to disable the editing of Target to Other Remarks'

    // COMPUTED
    const subCategories = computed(() => {
      const subs = store.getters['formManager/subCategories']
      return subs.filter(i => { return i.category_id === parseInt(props.drawerId) && i.parent_id === null})
    })

    const measuresList  = computed(() => store.getters['formManager/manager'].measures)

    // EVENTS
    watch(() => [props.drawerConfig, props.formObject] , ([drawerConfig, formObject]) => {
      config.value = drawerConfig
      form.value = formObject
    })

    onMounted(() => {
      onLoad()
    })

    const {
      // DATA
      storedOffices,
      // METHODS
      changeNullValue, filterBasisOption, onOfficeChange, saveOfficeList, updateOfficeList, deleteOfficeItem,
    } = useFormFields(form)

    // METHODS
    const onLoad = () => {
      let params = {
        checkable: { allColleges: true, mains: true },
        isAcronym: true,
        currentYear: props.currentYear,
      }

      store.dispatch('external/FETCH_OFFICES_ACCOUNTABLE', { payload: params })
    }

    const toggleIsHeader = checked => {
      if(checked) {
        emit('toggle-is-header')
      }
    }

    const resetFormData = isNewIndicator => {
      emit('close-drawer', isNewIndicator)
    }

    const validateFields = async () => {
      isSubmmiting.value = true

      await props.validate()
        .then(() => {
          saveForm()
        })
        .catch(err => {
          isSubmmiting.value = !isSubmmiting.value
          console.log('error', err);
        });
    }

    const saveForm = async () => {
      let msgContent = ''
      for (let office in storedOffices) {
        storedOffices.value[office] = []
      }

      if (config.value.updateId === null) {
        await emit('add-table-item', form)
        msgContent = 'Added!'
      } else {
        await emit('update-table-item', { updateData: form, updateId: config.value.updateId })
        msgContent = 'Updated!'
      }
      await message.success(msgContent, 2)
      isSubmmiting.value = !isSubmmiting.value
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

      // useFormFields
      changeNullValue,
      filterBasisOption,
      onOfficeChange,
      saveOfficeList,
      updateOfficeList,
      deleteOfficeItem,

      toggleIsHeader,
      resetFormData,
      validateFields,
    }
  },
})
</script>
