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

      <a-form-item v-bind="validateInfos.program">
        <template #label>
          <span class="required-indicator">Program</span>
        </template>
        <a-select v-model:value="form.program" placeholder="Select" :options="programsByFunction"
                  :disabled="config.type === 'sub'" />
      </a-form-item>

      <a-form-item label="Sub Category" v-bind="validateInfos.subCategory">
        <a-tree-select
          v-model:value="form.subCategory" style="width: 100%" placeholder="Select"
          :dropdown-style="{ maxHeight: '400px', overflow: 'auto' }"
          :tree-data="subCategories" :field-names="{ label: 'name', value: 'id'}"
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
                    style="width: 100%" label-in-value allow-clear :options="measuresList"
                    @blur="validate('measures', { trigger: 'blur' }).catch(() => {})" >
            <template #option="{ label, items }">
              {{ label }} &nbsp;&nbsp;
              <a-tooltip placement="right">
                <template #title>
                  <template v-for="item in items" :key="item.id">
                    <div>{{ item.rate }} - {{ item.description }}</div>
                  </template>
                </template>
                <info-circle-filled :style="{ fontSize: '12px'}"/>
              </a-tooltip>
            </template>
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
            v-model:value="form.targetsBasis" :options="targetsBasisList" :filter-option="filterBasisOption"
            :disabled="config.type === 'sub' && !config.parentDetails.isHeader"
            @blur="validate('targetsBasis', { trigger: 'blur' }).catch(() => {})"
          />
        </a-form-item>

        <a-form-item v-bind="validateInfos.implementing">
          <template #label><span class="required-indicator">Implementing</span></template>
          <a-row :gutter="0">
            <a-col :span="22">
              <a-tree-select
                v-model:value="form.options.implementing"
                style="width: 100%" placeholder="Select an office/s" tree-node-filter-prop="title"
                :dropdown-style="{ maxHeight: '400px', overflow: 'auto' }" :tree-data="officesList"
                :show-checked-strategy="SHOW_PARENT" :max-tag-count="6" :disabled="form.implementing ? form.implementing.length > 0 : false"
                allow-clear tree-checkable label-in-value
                @change="(value, label, extra) => { onOfficeChange(value, label, extra, 'implementing') }" />
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

        <div v-if="form.implementing && form.implementing.length">
          <div v-for="(office, index) in form.implementing" :key="index">
            <a-row type="flex" align="middle">
              <a-col :span="5" :offset="3">
                <label>{{ typeof office.acronym !== 'undefined' ? office.acronym : office.label }} </label>
              </a-col>
              <a-col :span="8">
                <a-tree-select
                  v-model:value="form.implementing[index].cascadeTo"
                  style="width: 100%"
                  :dropdown-style="{ maxHeight: '400px', overflow: 'auto' }"
                  :tree-data="programsWithFunction"
                  placeholder="Select a Program"
                  tree-default-expand-all
                />
              </a-col>
              <a-col :span="2" :offset="1">
                <DeleteFilled @click="deleteOfficeItem('implementing', index)"/>
              </a-col>
            </a-row>
            <br />
          </div>
        </div>

        <a-form-item label="Supporting Office" v-bind="validateInfos.supporting">
          <a-row :gutter="0">
            <a-col :span="22">
              <a-tree-select
                v-model:value="form.options.supporting"
                style="width: 100%" placeholder="Select an office/s" tree-node-filter-prop="title"
                :dropdown-style="{ maxHeight: '400px', overflow: 'auto' }" :tree-data="officesList"
                :show-checked-strategy="SHOW_PARENT" :max-tag-count="6" :disabled="form.supporting ? form.supporting.length > 0 : false"
                allow-clear tree-checkable label-in-value
                @change="(value, label, extra) => { onOfficeChange(value, label, extra, 'supporting') }" />
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
          <div v-for="(office, index) in form.supporting" :key="index">
            <a-row type="flex" align="middle">
              <a-col :span="5" :offset="3">
                <label>{{ typeof office.acronym !== 'undefined' ? office.acronym : office.label }} </label>
              </a-col>
              <a-col :span="8">
                <a-tree-select
                  v-model:value="form.supporting[index].cascadeTo"
                  style="width: 100%"
                  :dropdown-style="{ maxHeight: '400px', overflow: 'auto' }"
                  :tree-data="programsWithFunction"
                  placeholder="Select a Program"
                  tree-default-expand-all
                />
              </a-col>
              <a-col :span="2" :offset="1">
                <DeleteFilled @click="deleteOfficeItem('supporting', index)"/>
              </a-col>
            </a-row>
            <br />
          </div>
        </div>

        <a-form-item label="Remarks" v-bind="validateInfos.remarks">
          <a-textarea v-model:value="form.remarks" auto-size />
        </a-form-item>
      </template>
    </a-form>

    <div class="drawer-form-footer">
      <a-button  v-if="config.type === 'pi' || (config.type !== 'pi' && config.updateId !== null)"
                 :loading="isSubmmitting" style="margin-right: 8px" @click="resetFormData(0)">
        Cancel
      </a-button>
      <template v-else>
        <a-popconfirm
          title="Create a new parent PI?" placement="top" ok-text="Yes" cancel-text="No"
          @confirm="resetFormData(1)" @cancel="resetFormData(0)" >
          <a-button :style="{ marginRight: '8px' }" :loading="isSubmmitting" >
            Cancel
          </a-button>
        </a-popconfirm>
      </template>
      <a-button type="primary" @click.prevent="validateFields" :loading="isSubmmitting">{{ config.okText }}</a-button>
    </div>
  </a-drawer>
</template>
<script>
import { defineComponent, ref, watch, onMounted, inject, computed } from 'vue'
import { useStore } from 'vuex'
import { TreeSelect } from "ant-design-vue"
import { CheckOutlined, DeleteFilled, EditOutlined, InfoCircleFilled } from "@ant-design/icons-vue"
import { useFormFields } from '@/services/functions/form/main'

export default defineComponent({
  name: "OpcrVpFormDrawer",
  components: { CheckOutlined, EditOutlined, DeleteFilled, InfoCircleFilled },
  props: {
    drawerConfig: { type: Object, default: () => { return {} }},
    formObject: { type: Object, default: () => { return {} }},
    drawerId: { type: Number, default: null },
    targetsBasisList: { type: Array, default: () => { return [] }},
    categories: { type: Array, default: () => { return [] }},
    currentYear: { type: Number, default: new Date().getFullYear() },
    validate: { type: Function, default: () => {} },
    validateInfos: { type: Object, default: () => { return {} }},
  },
  emits: ['close-drawer', 'toggle-is-header', 'add-table-item', 'update-table-item'],
  setup(props, { emit }) {
    const store = useStore()

    const _message = inject('a-message')

    // DATA
    const config = ref({})
    const isSubmmitting = ref(false)
    const form = ref({})

    // STATIC DATA
    const SHOW_PARENT = TreeSelect.SHOW_PARENT

    // COMPUTED
    const programsByFunction = computed( () => {
      const list = store.getters['formManager/manager'].programs
      return list.filter(i => i.category_id === props.drawerId).map(v => ({ value: v.id, label: v.name }))
    })

    const programsWithFunction = computed( () => {
      const functions = store.getters['formManager/manager'].functions
      return functions.map(function(functionValue){
        const programs = store.getters['formManager/manager'].programs
        return {'children' : programs.filter(programValue => programValue.category_id === functionValue.id).map(function(mapValue){
            mapValue.key = functionValue.id + "-" + mapValue.id
            mapValue.title = mapValue.name
            mapValue.value = mapValue.id
            mapValue.category = {}
            return mapValue
          }), value: "p-"+ functionValue.id, key: functionValue.id.toString(), title: functionValue.name,selectable: false}
      })
    })

    const subCategories = computed(() => {
      const subs = store.getters['formManager/subCategories']
      return subs.filter(i => { return i.category_id === props.drawerId && i.parent_id === null})
    })

    const measuresList  = computed(() => {
      const list = store.state.formManager.measures
      return list.map(i => ({ value: i.key, label: i.name, items: i.items }))
    })

    const officesList  = computed(() => store.getters['external/external'].officesAccountable)

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
      // METHOD
      changeNullValue, filterBasisOption, onOfficeChange, saveOfficeListVp, updateOfficeList, deleteOfficeItem,
    } = useFormFields(form)

    const onLoad = () => {
      // let params = {
      //   checkable: { allColleges: true, mains: false },
      //   isAcronym: true,
      //   currentYear: props.currentYear,
      // }
      //
      // store.dispatch('external/FETCH_OFFICES_ACCOUNTABLE', { payload: params })
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
      isSubmmitting.value = true

      await props.validate()
        .then(() => {
          saveForm()
        })
        .catch(err => {
          isSubmmitting.value = !isSubmmitting.value
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
      await _message.success(msgContent, 2)
      isSubmmitting.value = !isSubmmitting.value
    }

    return {
      SHOW_PARENT,
      config,
      isSubmmitting,
      form,

      programsByFunction,
      programsWithFunction,

      subCategories,
      measuresList,
      officesList,

      toggleIsHeader,
      resetFormData,
      validateFields,

      // useFormFields
      typeOptions,
      formItemLayout,
      tooltipHeaderText,

      changeNullValue,
      filterBasisOption,
      onOfficeChange,
      saveOfficeListVp,
      updateOfficeList,
      deleteOfficeItem,
    }
  },
})
</script>
<style lang="scss">
@import "@/components/Drawer/style.module.scss";
</style>
