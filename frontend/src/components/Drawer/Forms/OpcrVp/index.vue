<template>
  <a-drawer v-model:visible="config.open" :title="config.modalTitle" placement="right" :closable="false"
            :mask-closable="false" :body-style="{ paddingBottom: '80px' }" :width="800"
            @close="resetFormData(0)">

    <a-form ref="opcrVpForm" name="opcr_vp_indicator_form" :model="form"
            layout="horizontal" :hide-required-mark="true" :label-col="formItemLayout.labelCol"
            :wrapper-col="formItemLayout.wrapperCol" @finish="onFinish">
      <a-form-item name="type" label="Type">
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

      <a-form-item name="program" :rules="rules.program">
        <template #label>
          <span class="required-indicator">Program</span>
        </template>
        <a-select v-model:value="form.program" placeholder="Select" :disabled="config.type === 'sub'">
          <a-select-option v-for="(y, i) in programsByFunction" :value="y.id" :key="i">
            {{ y.name }}
          </a-select-option>
        </a-select>
      </a-form-item>

      <a-form-item name="subCategory" label="Sub Category">
        <a-tree-select
          v-model:value="form.subCategory" style="width: 100%" placeholder="Select"
          :dropdown-style="{ maxHeight: '400px', overflow: 'auto' }"
          :tree-data="subCategories" :replace-fields="{ title: 'name', value: 'id'}"
          allow-clear tree-default-expand-all label-in-value
          :disabled="config.type === 'sub'"
          @change="changeNullValue($event, 'subCategory')"
        ></a-tree-select>
      </a-form-item>

      <a-form-item name="name" :rules="rules.name">
        <template #label>
          <span class="required-indicator">Performance Indicator</span>
        </template>
        <a-textarea v-model:value="form.name" auto-size/>
      </a-form-item>

      <a-form-item name="isHeader" label="Header PI?">
        <template v-if="!form.isHeader && config.type !== 'sub'">
          <a-tooltip placement="right" :title="tooltipHeaderText">
            <a-switch v-model:checked="form.isHeader" :disabled="config.type === 'sub'" @change="toggleIsHeader"/>
          </a-tooltip>
        </template>
        <a-switch v-else v-model:checked="form.isHeader" :disabled="config.type === 'sub'" @change="toggleIsHeader"/>
      </a-form-item>

      <template v-if="!form.isHeader">
        <a-form-item name="target" :rules="rules.target">
          <template #label><span class="required-indicator">Target</span></template>

          <a-input v-model:value="form.target"/>
        </a-form-item>

        <a-form-item name="measures" :rules="rules.measures">
          <template #label><span class="required-indicator">Measures</span></template>

          <a-select v-model:value="form.measures" mode="multiple" placeholder="Select"
                    style="width: 100%" label-in-value allow-clear >
            <a-select-option v-for="measure in measuresList" :value="measure.id" :key="measure.id">
              {{ measure.name }}
            </a-select-option>
          </a-select>
        </a-form-item>

        <a-form-item name="budget" label="Allocated Budget" >
          <a-input-number v-model:value="form.budget" :min="0" style="width: 50%" :step="0.01"
                          :formatter="value => `${value}`.replace(/\B(?=(\d{3})+(?!\d))/g, ',')"
                          :parser="value => value.replace(/\$\s?|(,*)/g, '')" />
        </a-form-item>

        <a-form-item name="targetsBasis" :rules="rules.targetsBasis">
          <template #label><span class="required-indicator">Targets Basis</span></template>

          <a-auto-complete
            v-model:value="form.targetsBasis" :options="targetsBasisList" :filter-option="filterBasisOption"
            :disabled="config.type === 'sub' && !config.parentDetails.isHeader"
          />
        </a-form-item>

        <a-form-item :name="['options', 'implementing']" ref="implementing" :rules="rules.implementing" :auto-link="false">
          <template #label><span class="required-indicator">Implementing Office</span></template>
          <a-row :gutter="0">
            <a-col :span="22">
              <a-tree-select
                v-model:value="form.options.implementing"
                style="width: 100%" placeholder="Select office/s" tree-node-filter-prop="title"
                :dropdown-style="{ maxHeight: '400px', overflow: 'auto' }" :tree-data="officesList"
                :show-checked-strategy="SHOW_PARENT" :max-tag-count="6" :disabled="form.implementing.length > 0"
                allow-clear tree-checkable label-in-value
                @blur="() => { $refs.implementing.onFieldBlur() }"
                @change="(value, label, extra) => {
                  $refs.implementing.onFieldChange()
                  onOfficeChange(value, label, extra, 'implementing')
                }" />
            </a-col>
            <a-col :span="2">
              <a-tooltip :title="form.implementing && !form.implementing.length ? 'Save List' : 'Edit List'">
                <a-button v-if="form.implementing && !form.implementing.length" type="primary" @click="saveOfficeListVp('implementing')">
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
          <template v-for="(office, index) in form.implementing" :key="index">
            <a-form-item :name="['implementing', index, 'cascadeTo']" :rules="rules.cascadeTo">
              <template #label>{{ typeof office.acronym !== 'undefined' ? office.acronym : office.label }}</template>
              <a-row :gutter="0">
                <a-col :span="18" :offset="1">
                  <a-tree-select
                    v-model:value="office.cascadeTo"
                    style="width: 100%"
                    :dropdown-style="{ maxHeight: '400px', overflow: 'auto' }"
                    :tree-data="functionsWithProgram"
                    placeholder="Select a Program"
                    tree-default-expand-all
                  />
                </a-col>
                <a-col :span="2" :offset="1">
                  <DeleteFilled @click="deleteOfficeItem('implementing', index)"/>
                </a-col>
              </a-row>
            </a-form-item>
          </template>
        </div>

        <a-form-item label="Supporting Office" :name="['options', 'supporting']" ref="supporting" :rules="rules.supporting" :auto-link="false">
          <a-row :gutter="0">
            <a-col :span="22">
              <a-tree-select
                v-model:value="form.options.supporting"
                style="width: 100%" placeholder="Select office/s" tree-node-filter-prop="title"
                :dropdown-style="{ maxHeight: '400px', overflow: 'auto' }" :tree-data="officesList"
                :show-checked-strategy="SHOW_PARENT" :max-tag-count="6" :disabled="form.supporting ? form.supporting.length > 0 : false"
                allow-clear tree-checkable label-in-value
                @blur="() => { $refs.supporting.onFieldBlur() }"
                @change="(value, label, extra) => {
                  $refs.supporting.onFieldChange()
                  onOfficeChange(value, label, extra, 'supporting')
                }" />
            </a-col>
            <a-col :span="2">
              <a-tooltip :title="form.supporting && !form.supporting.length ? 'Save List' : 'Edit List'">
                <a-button v-if="form.supporting && !form.supporting.length" type="primary" @click="saveOfficeListVp('supporting')">
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
          <template v-for="(o, i) in form.supporting" :key="i">
            <a-form-item :name="['supporting', i, 'cascadeTo']" :rules="rules.cascadeTo">
              <template #label>{{ typeof o.acronym !== 'undefined' ? o.acronym : o.label }}</template>
              <a-row :gutter="0">
                <a-col :span="18" :offset="1">
                  <a-tree-select
                    v-model:value="o.cascadeTo"
                    style="width: 100%"
                    :dropdown-style="{ maxHeight: '400px', overflow: 'auto' }"
                    :tree-data="functionsWithProgram"
                    placeholder="Select a Program"
                    tree-default-expand-all
                  />
                </a-col>
                <a-col :span="2" :offset="1">
                  <DeleteFilled @click="deleteOfficeItem('supporting', i)"/>
                </a-col>
              </a-row>
            </a-form-item>
          </template>
        </div>

        <a-form-item name="remarks" label="Remarks">
          <a-textarea v-model:value="form.remarks" auto-size />
        </a-form-item>
      </template>

      <div class="drawer-form-footer">
        <a-form-item>
          <a-button  v-if="config.type === 'pi' || (config.type !== 'pi' && config.updateId !== null)"
                     style="margin-right: 8px" @click="resetFormData(0)" :loading="isSubmitting">
            Cancel
          </a-button>
          <template v-else>
            <a-popconfirm
              title="Create a new parent PI?" placement="top" ok-text="Yes" cancel-text="No"
              @confirm="resetFormData(1)" @cancel="resetFormData(0)" >
              <a-button :style="{ marginRight: '8px' }" :loading="isSubmitting">
                Cancel
              </a-button>
            </a-popconfirm>
          </template>
          <a-button type="primary" html-type="submit" :loading="isSubmitting">{{ config.okText }}</a-button>
        </a-form-item>
      </div>
    </a-form>
  </a-drawer>
</template>

<script>
import { defineComponent, ref, watch, onMounted, computed } from 'vue'
import { useStore } from "vuex";
import { message, TreeSelect } from "ant-design-vue"
import { CheckOutlined, EditOutlined, DeleteFilled } from "@ant-design/icons-vue"
import { useFormFields } from '@/services/functions/form/main'
import { cloneDeep } from 'lodash-es'

export default defineComponent({
  name: 'OpcrVpFormDrawer',
  components: { CheckOutlined, EditOutlined, DeleteFilled },
  props: {
    drawerConfig: { type: Object, default: () => { return {} }},
    formObject: { type: Object, default: () => { return {} }},
    drawerId: { type: Number, default: null },
    targetsBasisList: { type: Array, default: () => { return [] }},
    rules: { type: Object, default: () => { return {} }},
    currentYear: { type: Number, default: new Date().getFullYear() },
  },
  emits: ['close-drawer', 'toggle-is-header', 'add-table-item', 'update-table-item'],
  setup(props, { emit }) {
    const store = useStore()

    // DATA
    const opcrVpForm = ref()
    const config = ref({})
    const form = ref({})
    const isSubmitting = ref(false)

    // STATIC DATA
    const SHOW_PARENT = TreeSelect.SHOW_PARENT

    // COMPUTED
    const programsByFunction = computed( () => {
      const programs = store.getters['formManager/manager'].programs
      return programs.filter(i => i.category_id === props.drawerId)
    })

    const functionsWithProgram = computed( () => {
      const functions = store.getters['formManager/manager'].functions

      return functions.map(function(functionValue){
        const programs = store.getters['formManager/manager'].programs
        const otherPrograms = store.getters['formManager/manager'].otherPrograms
        const mergedPrograms = otherPrograms.length ? programs.concat(otherPrograms) : programs

        return {
          'children' : mergedPrograms.filter(programValue => programValue.category_id === functionValue.id).map(function(mapValue){
            let mappedKey = functionValue.id + "-" + mapValue.id
            if(typeof mapValue.form_id !== 'undefined') {
              mappedKey = mappedKey + "-" + mapValue.form_id
            }
            mapValue.key = mappedKey
            mapValue.title = mapValue.name
            mapValue.value = mappedKey
            return mapValue
          }),
          value: functionValue.id,
          key: functionValue.id.toString(),
          title: functionValue.name,
          selectable: false,
        }
      })
    })

    const subCategories = computed(() => {
      const subs = store.getters['formManager/subCategories']
      return subs.filter(i => { return i.category_id === props.drawerId && i.parent_id === null})
    })

    const measuresList  = computed(() => store.getters['formManager/manager'].measures)
    const officesList  = computed(() => store.getters['external/external'].officesAccountable)

    // EVENTS
    watch(() => [props.drawerConfig, props.formObject], ([drawerConfig, formObject]) => {
      config.value = drawerConfig
      form.value = formObject
    })

    onMounted(() => {
      onLoad()
    })

    const {
      // DATA
      typeOptions, formItemLayout, tooltipHeaderText, storedOffices,
      // METHODS
      changeNullValue, filterBasisOption, onOfficeChange, saveOfficeListVp, updateOfficeList, deleteOfficeItem,
    } = useFormFields(form)

    // METHODS
    const onLoad = () => {
      let params = {
        checkable: {
          allColleges: true,
          mains: false,
        },
        isAcronym: true,
        currentYear: props.currentYear,
      }
      params = encodeURIComponent(JSON.stringify(params))
      store.dispatch('external/FETCH_OFFICES_ACCOUNTABLE', { payload: params })
    }

    const toggleIsHeader = checked => {
      if(checked) {
        emit('toggle-is-header')
      }
    }

    const onFinish = async values => {
      await changeSubmitStatus()
      let msgContent = ''
      for (let office in storedOffices) {
        storedOffices.value[office] = []
      }

      if (config.value.updateId === null) {
        values.implementing = cloneDeep(form.value.implementing)
        values.supporting = cloneDeep(form.value.supporting)

        await emit('add-table-item', { data: values, resetFields: resetForm() })
        msgContent = 'Added!'
      } else {
        await emit('update-table-item', {
          updateData: form,
          updateId: config.value.updateId,
          resetFields: resetForm(),
        })
        msgContent = 'Updated!'
      }
      await message.success(msgContent, 2)
      await changeSubmitStatus()
    }

    const changeSubmitStatus = () => {
      isSubmitting.value = !isSubmitting.value
    }

    const resetFormData = async isNewIndicator => {
      await emit('close-drawer', { isNewIndicator: isNewIndicator, callback: resetForm() })
    }

    const resetForm = () => {
      return opcrVpForm.value.resetFields()
    }

    return {
      opcrVpForm, config, form, isSubmitting,

      SHOW_PARENT,

      programsByFunction, functionsWithProgram, subCategories, measuresList, officesList,

      typeOptions, formItemLayout, tooltipHeaderText,

      changeNullValue, filterBasisOption, onOfficeChange, saveOfficeListVp, updateOfficeList, deleteOfficeItem,

      toggleIsHeader, onFinish, resetFormData,
    }
  },
})
</script>
