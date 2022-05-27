<template>
  <div v-if="hasAapcrAccess || aapcrFormPermission">
    <a-spin :spinning="loading || isCheckingForm" :tip="spinningTip">
      <a-row type="flex">
        <a-col :sm="{ span: 3 }" :md="{ span: 3 }" :lg="{ span: 2 }"><b>Fiscal Year:</b></a-col>
        <a-col :sm="{ span: 12, offset: 1 }" :md="{ span: 4, offset: 1 }" :lg="{ span: 3, offset: 1 }">
          <a-select v-model:value="year" placeholder="Select year" style="width: 200px" @change="checkIfDataSourceEmpty" :disabled="editMode">
            <template v-for="(y, i) in years" :key="i">
              <a-select-option :value="y"> {{ y }} </a-select-option>
            </template>
          </a-select>
        </a-col>
      </a-row>

      <div class="mt-4">
        <a-collapse v-model:activeKey="activeKey" accordion>
          <a-collapse-panel v-for="(category, key) in categories" :key="`${key}`" :header="category.name">
            <indicator-component v-if="allowEdit"
              :function-id="category.id" :form-id="formId" :item-source="dataSource" :targets-basis-list="targetsBasisList"
              :categories="categories" :year="year" :counter="counter" :budget-list="budgetList"
              @add-targets-basis-item="addTargetsBasisItem" @update-data-source="updateDataSource" @delete-source-item="deleteSourceItem"
              @add-deleted-item="addDeletedItem" @update-source-item="updateSourceItem" @add-budget-list-item="addBudgetListItem"/>
          </a-collapse-panel>
        </a-collapse>
      </div>

      <div class="mt-4" v-if="budgetList.length">
        <budget-list-component :budget-list="budgetList" @delete-budget-item="deleteBudgetItem"/>
      </div>

      <div class="mt-4" v-if="allowEdit">
        <a-row type="flex" justify="center" align="middle">
          <a-col :sm="{ span: 3 }" :md="{ span: 3 }" :lg="{ span: 2 }" >
            <a-button ghost @click="validateForm(0)">{{ !editMode ? 'Save as draft' : 'Update' }}</a-button>
          </a-col>
          <a-col :sm="{ span: 4, offset: 1 }" :md="{ span: 4, offset: 1 }" :lg="{ span: 4, offset: 1 }" v-if="!isFinalized">
            <a-button type="primary" @click="validateForm(1)">Finalize</a-button>
          </a-col>
        </a-row>
      </div>
    </a-spin>
  </div>
  <div v-else><span>You have no permission to access this page.</span></div>
</template>
<script>
import { defineComponent, ref, onMounted, onBeforeUnmount, onBeforeMount, createVNode, computed } from 'vue'
import { useStore } from 'vuex'
import { useRouter, useRoute } from 'vue-router'
import { Modal } from 'ant-design-vue'
import { ExclamationCircleOutlined } from '@ant-design/icons-vue'
import { useFormOperations } from '@/services/functions/indicator'
import { useProgramBudget } from '@/services/functions/form/main'
import { checkSavedForm, fetchFormDetails } from '@/services/api/mainForms/aapcr'
import { usePermission } from '@/services/functions/permission'
import IndicatorComponent from './partials/items'
import BudgetListComponent from './partials/budget'

export default defineComponent({
  name: "AAPCRForm",
  components: { IndicatorComponent, BudgetListComponent },
  props: {
    formId: { type: String, default: '' },
    aapcrId: { type: Number, default: 0 },
  },
  setup(props) {
    const PAGE_TITLE = "AAPCR Form"

    const store = useStore()
    const router = useRouter()
    const route = useRoute()

    // DATA
    const activeKey = ref('0')
    const aapcrId = ref(null)

    const isCheckingForm = ref(false)

    const {
      // DATA
      dataSource, targetsBasisList, counter, deletedItems, editMode, isFinalized, allowEdit, year, cachedYear, years,
      // METHODS
      updateDataSource, addTargetsBasisItem, updateSourceCount, deleteSourceItem, updateSourceItem, addDeletedItem,
      resetFormFields,
    } = useFormOperations(props)

    const { budgetList, addBudgetListItem, deleteBudgetItem } = useProgramBudget()

    // COMPUTED
    const categories = computed(() => store.getters['formManager/functions'])
    const hasAapcrAccess = computed(() => store.getters['aapcr/form'].hasAapcrAccess)

    const loading = computed(() => {
      return store.getters['formManager/manager'].loading || store.getters['aapcr/form'].loading
    })

    const spinningTip = computed(() => {
      let tip = ''
      if (loading.value) {
        tip = 'Initializing form...'
      } else if(isCheckingForm.value) {
        tip = 'Checking form availability...'
      }
      return tip
    })

    const permission = { listAapcr: [ "form", "f-aapcr" ] }
    const { aapcrFormPermission } = usePermission(permission)

    // EVENTS
    onBeforeMount(() => {
      checkUserPermission()
    })

    onMounted(() => {
      store.commit('SET_DYNAMIC_PAGE_TITLE', { pageTitle: PAGE_TITLE })
      store.commit('aapcr/SET_STATE', { dataSource: [] })
      resetFormFields()

      aapcrId.value = typeof route.params.aapcrId !== 'undefined' ? route.params.aapcrId : null
      if(hasAapcrAccess.value || aapcrFormPermission.value){
        if(aapcrId.value) {
          getFormDetails()
        } else {
          checkFormAvailability()
        }
      }
    })

    onBeforeUnmount(() => {
      resetFormFields()
    });

    // METHODS
    const checkIfDataSourceEmpty = () => {
      if(year.value !== cachedYear.value) {
        if(dataSource.value.length > 0) {
          Modal.confirm({
            title: () => 'Your data will be lost if you proceed',
            content: () => 'Are you sure you want to continue?',
            icon: () => createVNode(ExclamationCircleOutlined),
            okText: 'Yes',
            cancelText: 'No',
            onOk() {
              checkFormAvailability()
            },
            onCancel() {
              year.value = cachedYear.value
            },
          })
        } else {
          checkFormAvailability()
        }
      }
    }

    const checkFormAvailability = () => {
      cachedYear.value = year.value
      isCheckingForm.value = true
      checkSavedForm(year.value).then( response => {
        if(response) {
          const { hasSaved } = response
          isCheckingForm.value = false
          if(hasSaved) {
            Modal.error({
              title: () => 'Unable to create an AAPCR for the year ' + year.value,
              content: () => 'Please check the AAPCR list or select a different year to create a new AAPCR',
            })

            allowEdit.value = false
            resetFormFields()
          } else {
            if (!editMode.value) {
              processForm()
            }
          }
        }
      })
    }

    const processForm = async () => {
      allowEdit.value = true
      await resetFormFields()
      await initializeFormFields()
    }

    const checkUserPermission = () => {
      store.dispatch('aapcr/CHECK_AAPCR_PERMISSION', { payload: { pmaps_id: store.state.user.pmapsId, form_id:'aapcr' }})
    }

    const initializeFormFields = async () => {
      await store.dispatch('formManager/FETCH_FUNCTIONS', { payload : { year: year.value }})
      await store.dispatch('formManager/FETCH_SUB_CATEGORIES', { payload : { year: year.value }})
      await store.dispatch('formManager/FETCH_MEASURES', { payload : { year: year.value }})
      await store.dispatch('formManager/FETCH_CASCADING_LEVELS')
      await store.dispatch('formManager/FETCH_PROGRAMS', { payload : { year: year.value }})
      await store.dispatch('formManager/FETCH_FORM_FIELDS', { payload: { year: year.value, formId: 'aapcr' }})
    }

    const getFormDetails = () => {
      store.commit('aapcr/SET_STATE', {
        loading: true,
      })
      fetchFormDetails(aapcrId.value).then(async response => {
        if(response) {
          allowEdit.value = true
          store.commit('aapcr/SET_STATE', {
            dataSource: response.dataSource,
          })

          year.value = response.year
          cachedYear.value = response.year
          budgetList.value = response.budgetList
          targetsBasisList.value = response.targetsBasisList
          isFinalized.value = response.isFinalized
          editMode.value = true

          await initializeFormFields()
        }
        store.commit('aapcr/SET_STATE', {
          loading: false,
        })
      })
    }

    const validateForm = isFinal => {
      if(!dataSource.value.length) {
        Modal.error({
          title: () => 'Unable to save the form',
          content: () => 'No Performance Indicators were added to the list',
        })
      } else if (!budgetList.value.length) {
        Modal.confirm({
          title: () => 'Budget for each program was not indicated. Do you want to proceed?',
          icon: () => createVNode(ExclamationCircleOutlined),
          content: () => '',
          okText: 'Yes',
          cancelText: 'No',
          onOk() {
            submitForm(isFinal)
          },
          onCancel() {},
        })
      } else {
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
      const documentName = props.formId.toUpperCase() + '_' + year.value

      const details = {
        dataSource: dataSource.value,
        fiscalYear: year.value,
        documentName: documentName,
        programBudgets: budgetList.value,
      }
      if (!editMode.value) {
        details.isFinalized = isFinal
        store.dispatch('aapcr/SAVE', { payload: details })
          .then(() => {
            router.push({ name: 'form.list', params: { formId: props.formId } })
          })
      }else {
        details.isFinalized = isFinal || isFinalized.value
        details.deletedIds = deletedItems.value
        details.aapcrId = aapcrId.value
        store.dispatch('aapcr/UPDATE', { payload: details })
          .then(() => {
            router.push({ name: 'form.list', params: { formId: props.formId } })
          })
      }
    }

    return {
      year,
      activeKey,
      editMode,
      isFinalized,
      allowEdit,
      isCheckingForm,
      hasAapcrAccess,
      // aapcrPermission,
      aapcrFormPermission,

      years,
      categories,
      loading,
      spinningTip,

      validateForm,
      checkIfDataSourceEmpty,

      // useFormOperations
      dataSource,
      targetsBasisList,
      counter,
      deletedItems,
      cachedYear,

      updateDataSource,
      addTargetsBasisItem,
      updateSourceCount,
      deleteSourceItem,
      updateSourceItem,
      addDeletedItem,

      // useProgramBudget
      budgetList,
      addBudgetListItem,
      deleteBudgetItem,

    }
  },
})
</script>
<style lang="scss">
@import "@/components/Forms/style.module.scss";
</style>
