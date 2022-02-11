<template>
  <div>
    <a-row type="flex">
      <a-col :sm="{ span: 4 }" :md="{ span: 3 }" :lg="{ span: 2 }"><b>Fiscal Year:</b></a-col>
      <a-col :sm="{ span: 12, offset: 1 }" :md="{ span: 4, offset: 1 }" :lg="{ span: 3, offset: 1 }">
        <a-select v-model:value="year" placeholder="Select year" style="width: 200px" @change="checkFormDetails">
          <template v-for="(y, i) in years" :key="i">
            <a-select-option :value="y"> {{ y }} </a-select-option>
          </template>
        </a-select>
      </a-col>
    </a-row>
    <div class="w-100 mt-2"></div>
    <a-row type="flex">
      <a-col :sm="{ span: 4 }" :md="{ span: 3 }" :lg="{ span: 2 }"><b>VP Office :</b></a-col>
      <a-col :sm="{ span: 12, offset: 1 }" :md="{ span: 10, offset: 1 }" :lg="{ span: 10, offset: 1 }">
        <a-select v-model:value="vpOffice" placeholder="Select VP Office" style="width: 100%" :options="vpOfficesList"
                  option-label-prop="title" allow-clear label-in-value @change="checkFormDetails()">
          <template #option="{ title }">
            {{ title }}
          </template>
        </a-select>
      </a-col>
    </a-row>
    <div class="mt-5">
      <template v-for="(category, key) in categories" :key="`${key}`">
        <a-divider><b>{{ category.name }}</b></a-divider>
        <indicator-component :form-id="formId" :function-id="category.key" :item-source="dataSource"/>
      </template>
    </div>
  </div>
</template>
<script>
import { defineComponent, ref, computed, onMounted, createVNode } from 'vue'
import { useStore } from 'vuex'
import IndicatorComponent from './partials/items'
import { useFormOperations } from '@/services/functions/indicator'
import { checkSaved, getAapcrDetailsByOffice } from '@/services/api/mainForms/opcrvp'
import { Modal } from "ant-design-vue";
import { ExclamationCircleOutlined } from "@ant-design/icons-vue";

export default defineComponent({
  name: "VpOPCRForm",
  components: { IndicatorComponent },
  props: {
    formId: { type: String, default: '' },
  },
  setup(props) {
    const PAGE_TITLE = "OPCR (VP) Form"

    const store = useStore()

    const vpOffice = ref(undefined)
    const aapcrId = ref(null)
    const counter = ref(0)

    const { dataSource, targetsBasisList, year, cachedYear, years, allowEdit } = useFormOperations(props)

    const vpOfficesList = computed(() => store.getters['external/external'].vpOffices)
    const categories = computed(() => store.getters['formManager/functions'])

    onMounted(() => {
      store.commit('SET_DYNAMIC_PAGE_TITLE', { pageTitle: PAGE_TITLE })
      onLoad()
    })

    // METHODS
    const onLoad = async () => {
      await store.dispatch('external/FETCH_VP_OFFICES', { payload: { officesOnly: 1 } })
      await store.dispatch('formManager/FETCH_FUNCTIONS')
    }
    const checkFormDetails = () => {
      if(typeof vpOffice.value !== 'undefined') {
        allowEdit.value = false
        store.commit('opcrvp/SET_STATE', {
          loading: true,
        })
        checkSaved(vpOffice.value.key, year.value).then(response => {
          if(response) {
            const { hasSaved } = response
            if (hasSaved) {
              Modal.error({
                title: () => 'The selected office has an existing OPCR for the year ' + year.value,
                content: () => 'Please check the list or select a different year/office to create a new OPCR',
              })
            } else {
              if(counter.value) {
                Modal.confirm({
                  title: () => 'Changes were not yet saved',
                  content: () => 'Do you still want to continue?',
                  icon: () => createVNode(ExclamationCircleOutlined),
                  okText: 'Yes',
                  cancelText: 'No',
                  onOk() {
                    allowEdit.value = true
                    loadAapcrIndicators()
                  },
                  onCancel() {},
                })
              } else {
                allowEdit.value = true
                loadAapcrIndicators()
              }
            }
          }
        })
      }
    }

    const loadAapcrIndicators = () => {
      allowEdit.value = false
      store.commit('opcrvp/SET_STATE', {
        loading: true,
      })
      getAapcrDetailsByOffice(vpOffice.value.key, year.value).then(response => {
        if(response) {
          if(response.aapcrId) {
            store.commit('opcrvp/SET_STATE', {
              dataSource: response.dataSource,
            })
            allowEdit.value = true
            aapcrId.value = response.aapcrId
            targetsBasisList.value = response.targetsBasisList
          }else {
            Modal.warning({
              title: () => 'There is no published AAPCR for the year ' + year.value,
              content: () => '',
            })
          }
        }
        store.commit('opcrvp/SET_STATE', {
          loading: false,
        })
      })
    }

    return {
      vpOffice,

      vpOfficesList,
      categories,

      checkFormDetails,

      // useFormOperations
      dataSource,
      year,
      cachedYear,
      years,
    }
  },
})
</script>
