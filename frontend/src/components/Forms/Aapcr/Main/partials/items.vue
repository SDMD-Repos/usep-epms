<template>
  <div>
    <a-select v-model:value="mainCategory" placeholder="Select" style="width: 350px" label-in-value @change="loadPIs">
      <a-select-option v-for="(y, i) in programsByFunction" :value="y.id" :key="i">
        {{ y.name }}
      </a-select-option>
    </a-select>

    <div class="mt-4">
      <indicator-table :form-id="formId" @open-drawer="openDrawer"/>

      <aapcr-form-drawer :drawer-config="drawerConfig" :form-object="formData" :drawer-id="functionId"
                         :target-basis-list="targetsBasisList" :categories="categories" :current-year="year"
                         :validate="validate" :validate-infos="validateInfos"
                         @toggle-is-header="resetFormAsHeader" @close-drawer="closeDrawer"/>
    </div>
  </div>
</template>
<script>
import {computed, defineComponent, ref, reactive, onMounted} from "vue"
import { useStore } from 'vuex'
import IndicatorTable from '@/components/Tables/Forms/Main'
import AapcrFormDrawer from '@/components/Drawer/Forms/Aapcr'
import { useDrawerSettings, useDefaultFormData } from '@/services/functions/indicator'
import { Form } from 'ant-design-vue'

const useForm = Form.useForm

export default defineComponent({
  name: "AapcrItems",
  components: { IndicatorTable, AapcrFormDrawer },
  props: {
    functionId: { type: String, default: "" },
    formId: { type: String, default: "" },
    itemSource: { type: Array, default: () => { return [] }},
    targetsBasisList: { type: Array, default: () => { return [] }},
    categories: { type: Array, default: () => { return [] }},
    year: { type: Number, default: new Date().getFullYear() },
  },
  setup(props) {
    const store = useStore()

    // DATA
    const mainCategory = ref(undefined)
    const displayIndicatorList = ref(0)

    // COMPUTED
    const programs = computed(() => store.getters['formManager/manager'].programs)

    const programsByFunction = computed( () => { return programs.value.filter(i => i.category_id === props.functionId) })

    const {
      drawerConfig,
      openDrawer, resetDrawerSettings } = useDrawerSettings()

    const { formData, rules, resetFormAsHeader } = useDefaultFormData(props)

    // EVENTS
    onMounted(() => {
      store.dispatch('formManager/FETCH_PROGRAMS')
    })

    const { resetFields, validate, validateInfos } = useForm(formData, rules)

    // METHODS
    const loadPIs = e => {
      if (e !== '' && typeof e !== 'undefined') {
        displayIndicatorList.value = 1
      }
    }

    const closeDrawer = async () => {
      await resetDrawerSettings()
      await resetFields()
    }

    return {
      mainCategory,

      programsByFunction,

      // useDrawerSettings
      drawerConfig,
      openDrawer,

      // useDefaultFormData
      formData,
      resetFormAsHeader,

      validateInfos,
      validate,

      loadPIs,
      closeDrawer,
    }
  },
})
</script>
