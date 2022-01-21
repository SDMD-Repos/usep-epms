<template>
  <div>
    <a-select v-model:value="mainCategory" placeholder="Select" style="width: 350px" label-in-value>
      <a-select-option v-for="(y, i) in programsByFunction" :value="y.id" :key="i">
        {{ y.name }}
      </a-select-option>
    </a-select>

    <div class="mt-4">
      <indicator-table :form-id="formId" @open-drawer="openDrawer"/>

      <indicator-form-drawer :drawer-config="drawerConfig" :form-object="formData" :drawer-id="functionId"
                             :target-basis-list="targetsBasisList"
                             @toggle-is-header="resetFormAsHeader" @close-drawer="closeDrawer"/>
    </div>
  </div>
</template>
<script>
import { computed, defineComponent, ref, onMounted } from "vue"
import { useStore } from 'vuex'
import IndicatorTable from '@/components/Tables/Forms/Main'
import IndicatorFormDrawer from '@/components/Drawer/Forms/Main'
import { useDrawerSettings, useDefaultFormData } from '@/services/functions/indicator'
import { Form } from 'ant-design-vue'

const useForm = Form.useForm

export default defineComponent({
  name: "AapcrItems",
  components: { IndicatorTable, IndicatorFormDrawer },
  props: {
    functionId: { type: String, default: "" },
    formId: { type: String, default: "" },
    itemSource: { type: Array, default: () => { return [] }},
    targetsBasisList: { type: Array, default: () => { return [] }},
  },
  setup(props) {
    const store = useStore()

    // DATA
    const mainCategory = ref(undefined)

    // COMPUTED
    const programs = computed(() => store.getters['formManager/manager'].programs)

    const programsByFunction = computed( () => { return programs.value.filter(i => i.category_id === props.functionId) })

    const {
      drawerConfig,
      openDrawer, resetDrawerSettings } = useDrawerSettings()

    const { formData, resetFormAsHeader } = useDefaultFormData(props.formId)

    // EVENTS
    onMounted(() => {
      store.dispatch('formManager/FETCH_PROGRAMS')
    })

    // METHODS

    const closeDrawer = () => {
      resetDrawerSettings()
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

      closeDrawer,
    }
  },
})
</script>
