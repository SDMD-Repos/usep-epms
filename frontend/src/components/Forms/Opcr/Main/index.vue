<template>
  <div v-if="hasOpcrAccess || opcrFormPermission">
    <a-spin :spinning="loading || isCheckingForm" :tip="spinningTip">
      <a-row type="flex">
        <a-col :sm="{ span: 3 }" :md="{ span: 3 }" :lg="{ span: 2 }"><b>Fiscal Year:</b></a-col>
        <a-col :sm="{ span: 12, offset: 1 }" :md="{ span: 4, offset: 1 }" :lg="{ span: 3, offset: 1 }">
          <a-select v-model:value="year" placeholder="Select year" style="width: 200px" @change="checkFormAvailability">
            <template v-for="(y, i) in years" :key="i">
              <a-select-option :value="y"> {{ y }} </a-select-option>
            </template>
          </a-select>
        </a-col>
      </a-row>

      <div class="w-100 mt-2"></div>

      <a-row type="flex">
        <a-col :sm="{ span: 4 }" :md="{ span: 3 }" :lg="{ span: 2 }"><b>Office name :</b></a-col>
        <a-col :sm="{ span: 12, offset: 1 }" :md="{ span: 10, offset: 1 }" :lg="{ span: 10, offset: 1 }">
          <a-tree-select
            v-if="userOffices.length && userOffices.length > 1"
            v-model:value="officeId"
            style="width: 100%" :dropdown-style="{ maxHeight: '400px', overflow: 'auto' }"
            :tree-data="userOffices"
            placeholder="Select office"
            show-search allow-clear label-in-value tree-default-expand-all
          />
          <span v-else-if="userOffices.length">{{ userOffices[0].label.toUpperCase() }}</span>
        </a-col>
      </a-row>

      <div class="mt-4">
        <a-collapse v-model:activeKey="activeKey" accordion>
          <a-collapse-panel v-for="(category, key) in categories" :key="`${key}`" >
            <template #header>
              {{ !category.form_category ? category.name : category.form_category.display_name }}
            </template>

            <indicator-component v-if="allowEdit"
                                 :function-id="category.id" :form-id="formId" :item-source="dataSource" :targets-basis-list="targetsBasisList"
                                 :categories="categories" :year="year" :counter="counter"
                                 @add-targets-basis-item="addTargetsBasisItem" @update-data-source="updateDataSource" @delete-source-item="deleteSourceItem"
                                 @add-deleted-item="addDeletedItem" @update-source-item="updateSourceItem" />
          </a-collapse-panel>
        </a-collapse>
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
  <div v-else><error403 /></div>
</template>
<script>
import {defineComponent, ref, onMounted, createVNode, watch, computed, onBeforeMount} from 'vue'
import { useStore } from 'vuex'
import { useRouter, useRoute } from 'vue-router'
import { Modal } from 'ant-design-vue'
import { ExclamationCircleOutlined } from '@ant-design/icons-vue'
import { useFormOperations } from '@/services/functions/indicator'
import { getRequest } from '@/services/api/mainForms/ocpcr'
import { usePermission } from '@/services/functions/permission'
import IndicatorComponent from './partials/items'
import Error403 from '@/components/Errors/403'

const permission = { listOpcr: [ "form", "f-opcr", "fo-formlist" ] }

export default defineComponent({
  name: "OpcrForm",
  components: { IndicatorComponent, Error403 },
  props: {
    formId: { type: String, default: '' },
    opcrTemplateId: { type: Number, default: 0 },
  },
  setup(props) {
    const PAGE_TITLE = "OPCR Form"

    const store = useStore()
    const router = useRouter()
    const route = useRoute()

    // DATA
    const activeKey = ref('0')
    const opcrId = ref(null)
    const isCheckingForm = ref(false)
    const officeId = ref(undefined)

    const {
      // DATA
      dataSource, targetsBasisList, counter, deletedItems, editMode, isFinalized, allowEdit, year, cachedYear, years,
      // METHODS
      updateDataSource, addTargetsBasisItem, updateSourceCount, deleteSourceItem, updateSourceItem, addDeletedItem, resetFormFields,
    } = useFormOperations(props)

    // COMPUTED
    const categories = computed(() => store.getters['formManager/functions'])
    const hasOpcrAccess = computed(() => store.getters['opcr/form'].hasOpcrAccess)
    const officeAccess = computed(() => store.getters['opcr/form'].officeAccess)

    const loading = computed(() => {
      return store.getters['formManager/manager'].loading || store.getters['opcr/form'].loading || store.getters['external/external'].loading
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

    const userOffices = computed(() => {
      let offices
      if(hasOpcrAccess.value) {
        offices =  officeAccess.value;
        offices = offices.map(i => ({ value: i.office_id, label: i.office_name }))
      } else {
        offices = store.getters['external/external'].mainOfficesChildren
        offices = offices.map(i => (
            { value: i.value, label: i.title, children: i.children, selectable: i.selectable,
          }))
      }

      return offices
    })

    const { opcrFormPermission } = usePermission(permission)

    // EVENTS
    onBeforeMount(() => {
      store.dispatch('opcr/CHECK_OPCR_PERMISSION', { payload: { pmapsId: store.state.user.pmapsId, formId: props.formId }})
    })

    onMounted( () => {
      store.commit('SET_DYNAMIC_PAGE_TITLE', { pageTitle: PAGE_TITLE })

      if(opcrFormPermission) {
        let params = {
          selectable: { allColleges: false, mains: false },
          isAcronym: false,
          isOfficesOnly: true,
        }
        store.dispatch('external/FETCH_MAIN_OFFICES_CHILDREN', { payload: params })
      }

      resetFormFields()

      opcrId.value = typeof route.params.opcrId !== 'undefined' ? route.params.opcrId : null
      if(opcrId.value) {
        getFormDetails()
      } else {
        checkFormAvailability()
      }
    })

    watch( userOffices,  userOffices => {
      if(userOffices.length === 1) {
        const { value, label } = userOffices[0]
        officeId.value = { value: value, label: label }
      }
    })

    watch(officeId, officeId => {
      if(typeof officeId.value !== 'undefined'){ checkFormAvailability() }
    })

    // METHODS
    const checkFormAvailability = () => {
      allowEdit.value = false
      if(typeof officeId.value !== 'undefined') {
        isCheckingForm.value = true
        store.commit('opcr/SET_STATE', {
          dataSource: [],
        })
        console.log('checkFormAvailability')

        getRequest('/forms/ocpcr/check-saved/' + officeId.value + '/' + year.value).then(response => {
          if(response) {
            const { hasSaved } = response
            isCheckingForm.value = false
            if(hasSaved) {
              Modal.error({
                title: () => 'The selected office has an existing OPCR for the year ' + year.value,
                content: () => 'Please check the list or select a different year/office to create a new OPCR',
              })
              resetFormFields()
            }else {
              allowEdit.value = true
              loadCascadedIndicators()
            }
          }
        })
      }
    }

    const initializeFormFields = async () => {
      await store.dispatch('formManager/FETCH_FUNCTIONS', { payload : { year: year.value, formId: props.formId }})
      await store.dispatch('formManager/FETCH_SUB_CATEGORIES', { payload : { year: year.value }})
      await store.dispatch('formManager/FETCH_MEASURES', { payload : { year: year.value }})
      await store.dispatch('formManager/FETCH_PROGRAMS', { payload : { year: year.value }})
      await store.dispatch('formManager/FETCH_OTHER_PROGRAMS', { payload : { year: year.value, formId: props.formId }})
      await store.dispatch('formManager/FETCH_FORM_FIELDS', { payload: { year: year.value }})
    }

    const loadCascadedIndicators = () => {
      allowEdit.value = false
      store.commit('opcr/SET_STATE', {
        loading: true,
      })
      const url = 'forms/ocpcr/get-vp-opcr-details/' + officeId.value.value + '/' + year.value + '/' + props.formId
      getRequest(url).then(response => {
        if(response && typeof response.error === 'undefined') {
          store.commit('opcr/SET_STATE', { dataSource: response.dataSource })

          allowEdit.value = true
          targetsBasisList.value = response.targetsBasisList

          initializeFormFields()
        }else {
          console.log(response)
          Modal.warning({
            title: () => 'Error',
            content: () => response.error,
          })
        }
        store.commit('opcr/SET_STATE', {
          loading: false,
        })
      })
    }

    const getFormDetails = () => {
      store.commit('opcr/SET_STATE', {
        loading: true,
      })
      getRequest('/forms/ocpcr/view-template/' + opcrTemplateId.value).then(response => {
        if(response) {
          allowEdit.value = true
          store.commit('opcr/SET_STATE', {
            dataSource: response.dataSource,
          })

          year.value = response.year
          cachedYear.value = response.year
          targetsBasisList.value = response.targetsBasisList
          isFinalized.value = response.isFinalized
          editMode.value = true
          initializeFormFields()
        }
        store.commit('opcr/SET_STATE', {
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
      }
      if (!editMode.value) {
        details.isFinalized = isFinal
        store.dispatch('opcr/SAVE', { payload: details })
          .then(() => {
            router.push({ name: 'form.list', params: { formId: props.formId } })
          })
      }else {
        details.isFinalized = isFinal || isFinalized.value
        details.deletedIds = deletedItems.value
        details.opcrTemplateId = opcrTemplateId.value
        store.dispatch('opcr/UPDATE', { payload: details })
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
      officeId,

      years,
      categories,
      hasOpcrAccess,
      loading,
      spinningTip,
      userOffices,

      opcrFormPermission,

      validateForm,
      checkFormAvailability,

      // useFormOperations
      dataSource,
      targetsBasisList,
      counter,
      deletedItems,

      updateDataSource,
      addTargetsBasisItem,
      updateSourceCount,
      deleteSourceItem,
      updateSourceItem,
      addDeletedItem,

    }
  },
})
</script>
<style lang="scss">
@import "@/components/Forms/style.module.scss";
</style>
