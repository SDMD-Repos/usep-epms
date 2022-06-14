<template>
  <div>
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
</template>
<script>
import { defineComponent, ref, computed, onMounted, createVNode } from 'vue'
import { useStore } from 'vuex'
import { useRouter, useRoute } from 'vue-router'
import { Modal } from 'ant-design-vue'
import { ExclamationCircleOutlined } from '@ant-design/icons-vue'
import { useFormOperations } from '@/services/functions/indicator'
import { getRequest } from '@/services/api/mainForms/ocpcr'
import IndicatorComponent from './partials/items'

export default defineComponent({
  name: "OpcrForm",
  components: { IndicatorComponent },
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
    const opcrTemplateId = ref(null)
    const formId = 'opcr'
    const isCheckingForm = ref(false)

    const {
      // DATA
      dataSource, targetsBasisList, counter, deletedItems, editMode, isFinalized, allowEdit, year, cachedYear, years,
      // METHODS
      updateDataSource, addTargetsBasisItem, updateSourceCount, deleteSourceItem, updateSourceItem, addDeletedItem,
    } = useFormOperations(props)

    // COMPUTED
    const categories = computed(() => store.getters['formManager/functions'])
    const loading = computed(() => {
      return store.getters['formManager/manager'].loading || store.getters['opcr/form'].loading
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

    // EVENTS
    onMounted(() => {
      store.commit('SET_DYNAMIC_PAGE_TITLE', { pageTitle: PAGE_TITLE })
      store.commit('opcr/SET_STATE', { dataSource: [] })
      resetFormFields()

      opcrTemplateId.value = typeof route.params.opcrTemplateId !== 'undefined' ? route.params.opcrTemplateId : null

      if(opcrTemplateId.value) {
        getFormDetails()
      } else {
        checkFormAvailability()
      }
    })

    // METHODS
    const checkFormAvailability = () => {
      resetFormFields()

      if(year.value !== cachedYear.value) {
        isCheckingForm.value = true

        getRequest('/forms/ocpcr/check-saved/' + year.value).then(response => {
          if(response) {
            const { hasSaved } = response
            isCheckingForm.value = false
            if(hasSaved) {
              Modal.error({
                title: () => 'Unable to create an OPCR for the year ' + year.value,
                content: () => 'Please check the OPCR list or select a different year to create a new OPCR Template',
              })
              if (editMode.value) {
                year.value = cachedYear.value
              } else {
                allowEdit.value = false
              }
            } else {
              if (!editMode.value) {
                allowEdit.value = true
                initializeFormFields()
              }
            }
          }
        })
      }
    }

    const initializeFormFields = async () => {
      await store.dispatch('formManager/FETCH_FUNCTIONS', { payload : { year: year.value, formId: props.formId }})
      await store.dispatch('formManager/FETCH_SUB_CATEGORIES', { payload : { year: year.value }})
      await store.dispatch('formManager/FETCH_MEASURES', { payload : { year: year.value }})
      await store.dispatch('formManager/FETCH_CASCADING_LEVELS')
      await store.dispatch('formManager/FETCH_PROGRAMS', { payload : { year: year.value }})
      await store.dispatch('formManager/FETCH_OTHER_PROGRAMS', { payload : { year: year.value, formId: formId }})
      await store.dispatch('formManager/FETCH_FORM_FIELDS', { payload: { year: year.value }})
    }

    const resetFormFields = () => {
      store.commit('formManager/SET_STATE', {
        functions: [],
        subCategories: [],
        measures: [],
        cascadingLevels: [],
        programs: [],
        formFields: [],
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

      years,
      categories,
      loading,
      spinningTip,

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
