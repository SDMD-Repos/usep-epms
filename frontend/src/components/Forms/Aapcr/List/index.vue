<template>
  <div v-if="hasAapcrAccess || aapcrPermission">
    <form-list-table
      :columns="columns" :data-list="list" :form="formId" :loading="loading"
      @update-form="updateForm" @publish="publish" @view-pdf="viewPdf" @unpublish="openUnpublishRemarks" @view-unpublished-forms="viewUnpublishedForms" />

    <unpublished-forms-modal
      :modal-state="isUploadedViewed" :form-details="viewedForm"
      @close-list-modal="closeListModal" @view-file="viewPdf" />

    <unpublish-remarks-modal :is-unpublish="isUnpublish" @unpublish="unpublish" @close-remarks-modal="changeRemarksState"/>
  </div>
   <div v-else><span>You have no permission to access this page.</span></div>
</template>
<script>
import { defineComponent, ref, onMounted, computed } from "vue"
import { useRouter } from 'vue-router'
import { useStore } from 'vuex'
import { message } from 'ant-design-vue'
import moment from 'moment'
import { listTableColumns } from '@/services/columns'
import { useViewPublishedFiles } from '@/services/functions/published'
import { viewUnpublishedForm } from '@/services/api/system/requests'
import FormListTable from '@/components/Tables/Forms/List'
import UnpublishedFormsModal from '@/components/Modals/UnpublishedForms'
import UnpublishRemarksModal from '@/components/Modals/Remarks'

export default defineComponent({
  name: "AapcrList",
  components: {
    FormListTable,
    UnpublishedFormsModal,
    UnpublishRemarksModal,
  },
  props: {
    formId: { type: String, default: '' },
  },
  setup(props) {
    const PAGE_TITLE = 'AAPCR List'

    const store = useStore()
    const router = useRouter()

    // DATA
    const documentName = ref(null)
    const isUnpublish = ref(false)
    const unpublishedData = ref(null)

    // COMPUTED
    const list = computed(() => store.getters['aapcr/form'].list)
    const loading = computed(() => store.getters['aapcr/form'].loading)
    const hasAapcrAccess = computed(() => store.getters['aapcr/form'].hasAapcrAccess)
    const aapcrPermission = computed(() => store.getters['system/permission'].aapcrPermission)

    const { isUploadedViewed, viewedForm,
      viewUnpublishedForms, onCloseList, uploadedListState } = useViewPublishedFiles()

    // EVENTS
    onMounted(() => {
      store.commit('SET_DYNAMIC_PAGE_TITLE', { pageTitle: PAGE_TITLE })

      const aapcrPermissions = ["form", "f-aapcr"]

      store.dispatch('aapcr/CHECK_AAPCR_PERMISSION', { payload: { pmaps_id: store.state.user.pmapsId,  form_id:'aapcr' }})
      store.dispatch('system/CHECK_PERMISSION', { payload: {permission: aapcrPermissions, name:'aapcrPermission'} })

      store.dispatch('aapcr/FETCH_LIST')
    })

    // METHODS
    const updateForm = id => {
      router.push({
        name: 'main.form',
        params: { formId: props.formId, aapcrId: id },
      })
    }

    const publish = data => {
      const payload = {
        id: data.id,
        year: data.year,
      }
      store.dispatch('aapcr/PUBLISH', { payload: payload })
    }

    const openUnpublishRemarks = data => {
      changeRemarksState()
      unpublishedData.value = {
        id: data.id,
        documentName: data.document_name,
      }
    }

    const changeRemarksState = () => {
      isUnpublish.value = !isUnpublish.value
    }

    const unpublish = remarks => {
      const data = {
        id: unpublishedData.value.id,
        remarks: remarks.value,
        documentName: unpublishedData.value.documentName,
      }

      store.dispatch('aapcr/UNPUBLISH', { payload: data }).then(() => {
        isUnpublish.value = !isUnpublish.value
        unpublishedData.value = null
      })
    }

    const viewPdf = data => {
      message.loading('Loading...')

      const documentName = data.document_name || data.file_name

      viewUnpublishedForm(data.id).then(response => {
        if (response) {
          const blob = new Blob([response], { type: 'application/pdf' })
          const fileUrl = window.URL.createObjectURL(blob)

          localStorage.setItem('pdf.document.url', fileUrl)
          localStorage.setItem('pdf.document.name', documentName)

          const route = router.resolve({ name: "viewerPdf" });
          window.open(route.href, "_blank")

          message.destroy()
        }
      })
    }

    const closeListModal = () => {
      uploadedListState(false)
    }

    return {
      moment,
      unpublishedData,

      documentName,
      isUnpublish,

      columns: listTableColumns,
      list,
      loading,
      hasAapcrAccess,
      aapcrPermission,

      isUploadedViewed,
      viewedForm,

      updateForm,
      publish,
      openUnpublishRemarks,
      changeRemarksState,
      unpublish,
      viewPdf,

      closeListModal,

      viewUnpublishedForms,
      onCloseList,
    }
  },
})
</script>
