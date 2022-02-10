<template>
  <div>
    <a-spin :spinning="loading || isCheckingForm" :tip="spinningTip">
      <a-select v-model:value="year" placeholder="Select year" style="width: 200px" @change="checkFormAvailability">
        <template v-for="(y, i) in years" :key="i">
          <a-select-option :value="y"> {{ y }} </a-select-option>
        </template>
      </a-select>

      <div class="mt-4">
        <a-collapse v-model:activeKey="activeKey" accordion>
          <a-collapse-panel v-for="(category, key) in categories" :key="`${key}`" :header="category.name">
            <indicator-component
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
import { useRouter, useRoute } from 'vue-router'
import { Modal } from 'ant-design-vue'
import { ExclamationCircleOutlined } from '@ant-design/icons-vue'
import IndicatorComponent from './partials/items'
import { useFormOperations } from '@/services/functions/indicator'
import { checkSavedForm, fetchFormDetails } from '@/services/api/mainForms/aapcr'

export default defineComponent({
  name: "AAPCRForm",
  components: { IndicatorComponent },
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
    const year = ref(new Date().getFullYear())
    const cachedYear = ref(null)
    const activeKey = ref('0')
    const editMode = ref(false)
    const isFinalized = ref(false)
    const allowEdit = ref(false)
    const aapcrId = ref(null)

    const isCheckingForm = ref(false)

    const {
      // DATA
      dataSource, targetsBasisList, counter, deletedItems,
      // METHODS
      updateDataSource, addTargetsBasisItem, updateSourceCount, deleteSourceItem, updateSourceItem, addDeletedItem,
    } = useFormOperations()

    // COMPUTED
    const years = computed(() => {
      const now = new Date().getFullYear()
      const min = 10
      const lists = []
      for (let i = now; i >= (now - min); i--) {
        lists.push(i)
      }
      return lists
    })

    const categories = computed(() => store.getters['formManager/functions'])
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

    // EVENTS
    onMounted(() => {
      store.commit('SET_DYNAMIC_PAGE_TITLE', { pageTitle: PAGE_TITLE })
      aapcrId.value = typeof route.params.aapcrId !== 'undefined' ? route.params.aapcrId : null
      if(aapcrId.value) {
        getFormDetails()
      } else {
        checkFormAvailability()
      }
    })

    // METHODS
    const checkFormAvailability = () => {
      if(year.value !== cachedYear.value) {
        isCheckingForm.value = true
        checkSavedForm(year.value).then(response => {
          if(response) {
            const { hasSaved } = response
            isCheckingForm.value = false
            if(hasSaved) {
              Modal.error({
                title: () => 'Unable to create an AAPCR for the year ' + year.value,
                content: () => 'Please check the AAPCR list or select a different year to create a new AAPCR',
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
      await store.dispatch('formManager/FETCH_FUNCTIONS')
      await store.dispatch('formManager/FETCH_SUB_CATEGORIES')
      await store.dispatch('formManager/FETCH_MEASURES')
      await store.dispatch('formManager/FETCH_CASCADING_LEVELS')
      await store.dispatch('formManager/FETCH_PROGRAMS')
    }

    const getFormDetails = () => {
      store.commit('aapcr/SET_STATE', {
        loading: true,
      })
      fetchFormDetails(aapcrId.value).then(response => {
        if(response) {
          allowEdit.value = true
          initializeFormFields()

          year.value = response.year
          cachedYear.value = response.year
          dataSource.value = response.dataSource
          targetsBasisList.value = response.targetsBasisList
          isFinalized.value = response.isFinalized
          editMode.value = true
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
