<template>
  <div>
    <a-spin :spinning="loading" :tip="spinningTip">
      <a-row type="flex">
        <a-col :sm="{ span: 4 }" :md="{ span: 3 }" :lg="{ span: 2 }"><b>Fiscal Year:</b></a-col>
        <a-col :sm="{ span: 12, offset: 1 }" :md="{ span: 4, offset: 1 }" :lg="{ span: 3, offset: 1 }">
          <a-select v-model:value="year" placeholder="Select year" style="width: 200px" :disabled="editMode"
                    @change="checkFormDetails">
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
                    option-label-prop="title" allow-clear label-in-value :disabled="editMode" @change="checkFormDetails()">
            <template #option="{ title }">
              {{ title }}
            </template>
          </a-select>
        </a-col>
      </a-row>

      <div class="mt-5">
        <template v-for="(category, key) in categories" :key="`${key}`">
          <a-divider><b>{{ category.name }}</b></a-divider>
          <indicator-component
            :form-id="formId" :function-id="category.key" :item-source="dataSource" :allow-edit="allowEdit" :counter="counter"
            :categories="categories" :targets-basis-list="targetsBasisList"
            @add-targets-basis-item="addTargetsBasisItem" @update-data-source="updateDataSource" @update-source-item="updateSourceItem"
            @delete-source-item="deleteSourceItem" @add-deleted-item="addDeletedItem"/>
        </template>
      </div>

      <div class="mt-4" v-if="allowEdit">
        <a-row type="flex" justify="center" align="middle">
          <a-col :sm="{ span: 3 }" :md="{ span: 3 }" :lg="{ span: 2 }" >
            <a-button type="primary" ghost @click="validateForm(0)">{{ !editMode ? 'Save as draft' : 'Update' }}</a-button>
          </a-col>
          <a-col :sm="{ span: 4, offset: 1 }" :md="{ span: 4, offset: 1 }" :lg="{ span: 4, offset: 1 }" v-if="!isFinalized">
            <a-button type="primary" @click="validateForm(1)">Finalize</a-button>
          </a-col>
        </a-row>
      </div>
    </a-spin>
  </div>
</template>
<script>
import { defineComponent, ref, computed, onMounted, createVNode } from 'vue'
import { useStore } from 'vuex'
import { useRouter, useRoute } from "vue-router";
import { Modal } from "ant-design-vue";
import { ExclamationCircleOutlined } from "@ant-design/icons-vue";
import { useFormOperations } from '@/services/functions/indicator'
import { checkSaved, getAapcrDetailsByOffice, fetchFormDetails } from '@/services/api/mainForms/opcrvp'
import IndicatorComponent from './partials/items'

export default defineComponent({
  name: "VpOPCRForm",
  components: { IndicatorComponent },
  props: {
    formId: { type: String, default: '' },
  },
  setup(props) {
    const PAGE_TITLE = "OPCR (VP) Form"

    const store = useStore()
    const router = useRouter()
    const route = useRoute()

    const vpOffice = ref(undefined)
    const aapcrId = ref(null)
    const vpOpcrId = ref(null)

    const {
      // DATA
      dataSource, targetsBasisList, counter, deletedItems, editMode, isFinalized, year, cachedYear, years, allowEdit,
      // METHOD
      updateDataSource, addTargetsBasisItem, updateSourceItem, deleteSourceItem, addDeletedItem,
    } = useFormOperations(props)

    const vpOfficesList = computed(() => store.getters['external/external'].vpOffices)
    const categories = computed(() => store.getters['formManager/functions'])
    const loading = computed(() => {
      return store.getters['formManager/manager'].loading || store.getters['opcrvp/form'].loading
    })

    const spinningTip = computed(() => {
      let tip = ''
      if (loading.value) {
        tip = 'Initializing form...'
      } /*else if(isCheckingForm.value) {
        tip = 'Checking form availability...'
      }*/
      return tip
    })

    onMounted(() => {
      store.commit('SET_DYNAMIC_PAGE_TITLE', { pageTitle: PAGE_TITLE })

      vpOpcrId.value = typeof route.params.vpOpcrId !== 'undefined' ? route.params.vpOpcrId : null

      if(vpOpcrId.value) {
        getVpOpcrDetails()
      } else {
        onLoad()
      }
    })

    // METHODS
    const onLoad = async () => {
      await store.commit('opcrvp/SET_STATE', { dataSource: [] })
      await store.dispatch('external/FETCH_VP_OFFICES', { payload: { officesOnly: 1 } })
    }

    const checkFormDetails = () => {
      allowEdit.value = false
      if(typeof vpOffice.value !== 'undefined') {
        store.commit('opcrvp/SET_STATE', {
          loading: true,
          dataSource: [],
        })
        checkSaved(vpOffice.value.key, year.value).then(response => {
          if(response) {
            const { hasSaved } = response
            if (hasSaved) {
              Modal.error({
                title: () => 'The selected office has an existing OPCR for the year ' + year.value,
                content: () => 'Please check the list or select a different year/office to create a new OPCR',
              })
              store.commit('opcrvp/SET_STATE', { loading: false })
            } else {
              /*if(counter.value) {
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
              } else {*/
                allowEdit.value = true
                loadAapcrIndicators()
              //}
            }
          }
        })
      }else {
        store.commit('opcrvp/SET_STATE', { dataSource: [] })
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
            initializeVPForm()
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

    const initializeVPForm = async () => {
      await store.dispatch('formManager/FETCH_FUNCTIONS', { payload : { year: year.value }})
      await store.dispatch('formManager/FETCH_SUB_CATEGORIES', { payload : { year: year.value }})
      await store.dispatch('formManager/FETCH_MEASURES', { payload : { year: year.value }})
      await store.dispatch('formManager/FETCH_CASCADING_LEVELS')
      await store.dispatch('formManager/FETCH_PROGRAMS', { payload : { year: year.value }})
      await store.dispatch('formManager/FETCH_FORM_FIELDS', { payload: { year: year.value }})
    }

    const getVpOpcrDetails = () => {
      store.commit('opcrvp/SET_STATE', { loading: true })
      fetchFormDetails(vpOpcrId.value).then(response => {
        if(response.aapcrId) {
          allowEdit.value = true

          onLoad()
          initializeVPForm()

          store.commit('opcrvp/SET_STATE', { dataSource: response.dataSource })

          year.value = response.year
          vpOffice.value = response.vpOffice
          aapcrId.value = response.aapcrId
          targetsBasisList.value = response.targetsBasisList
          isFinalized.value = response.isFinalized
          editMode.value = response.editMode
        }
        store.commit('opcrvp/SET_STATE', { loading: false })
      })
    }

    const validateForm = isFinal => {
      if(!dataSource.value.length) {
        Modal.error({
          title: () => 'Unable to save the form',
          content: () => 'No Performance Indicators were added to the list',
        })
      }else {
        let title = ''
        if (isFinal) {
          title = 'This will finalize and save your form'
        } else if (!editMode.value) {
          title = 'This will save your form as draft'
        } else {
          title = 'This will update your form'
        }
        Modal.confirm({
          title: () => title,
          icon: () => createVNode(ExclamationCircleOutlined),
          content: () => 'Are you sure you want to proceed?',
          okText: 'Yes',
          cancelText: 'No',
          onOk() {
            submitForm(isFinal)
          },
          onCancel() {},
        })
      }
    }

    const submitForm = isFinal => {
      if(!editMode.value) {
        const details = {
          dataSource: dataSource.value,
          fiscalYear: year.value,
          vpOffice: vpOffice.value,
          isFinalized: isFinal,
          aapcrId: aapcrId.value,
        }
        store.dispatch('opcrvp/SAVE', { payload: details })
          .then(() => {
            router.push({ name: 'form.list', params: { formId: props.formId } })
          })
      }else {
        const details = {
          dataSource: dataSource.value,
          isFinalized: isFinal || isFinalized.value,
          deletedIds: deletedItems.value,
          vpOpcrId: vpOpcrId.value,
        }
        store.dispatch('opcrvp/UPDATE', { payload: details })
          .then(() => {
            router.push({ name: 'form.list', params: { formId: props.formId } })
          })
      }
    }

    return {
      vpOffice,
      allowEdit,

      vpOfficesList,
      categories,
      loading,
      spinningTip,

      checkFormDetails,
      validateForm,

      // useFormOperations
      dataSource,
      targetsBasisList,
      counter,
      deletedItems,
      editMode,
      isFinalized,
      year,
      cachedYear,
      years,

      updateDataSource,
      addTargetsBasisItem,
      updateSourceItem,
      deleteSourceItem,
      addDeletedItem,
    }
  },
})
</script>
<style lang="scss">
@import "@/components/Forms/style.module.scss";
</style>
