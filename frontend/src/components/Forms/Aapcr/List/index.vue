<template>
  <div v-if="hasAapcrAccess || aapcrFormPermission">
    <form-list-table
      :columns="columns" :data-list="list" :form="formId" :loading="loading"
      @update-form="updateForm" @publish="publish" @view-pdf="viewPdf" @unpublish="openUnpublishRemarks"
      @view-unpublished-forms="viewUnpublishedForms" @cancel-unpublish-request="onUnpublishCancel" />

    <unpublished-forms-modal
      :modal-state="isUploadedViewed" :form-details="viewedForm"
      @close-list-modal="onCloseList" @view-file="viewPdf" />

    <unpublish-remarks-modal
      :is-unpublish="isUnpublish" :form-id="formId"
      @unpublish="unpublish" @close-remarks-modal="changeRemarksState" />
  </div>
   <div v-else><span>You have no permission to access this page.</span></div>
</template>
<script>
import { defineComponent, ref, onMounted, inject, computed } from "vue"
import { useRouter } from 'vue-router'
import { useStore } from 'vuex'
import { listTableColumns } from '@/services/columns'
import { useUnpublish, useViewPublishedFiles } from '@/services/functions/formListActions'
import { getUnpublishedFormData } from '@/services/api/system/requests'
import { fetchPdfData, viewSavedPdf } from '@/services/api/mainForms/aapcr'
import { usePermission } from '@/services/functions/permission'
import FormListTable from '@/components/Tables/Forms/List'
import UnpublishedFormsModal from '@/components/Modals/UnpublishedForms'
import UnpublishRemarksModal from '@/components/Modals/Remarks'

export default defineComponent({
  name: "AapcrList",
  components: { FormListTable, UnpublishedFormsModal, UnpublishRemarksModal },
  props: {
    formId: { type: String, default: '' },
  },
  setup(props) {
    const PAGE_TITLE = 'AAPCR List'

    const store = useStore()
    const router = useRouter()

    const _message = inject('a-message')

    // DATA
    const documentName = ref(null)

    // COMPUTED
    const list = computed(() => store.getters['aapcr/form'].list)
    const loading = computed(() => store.getters['aapcr/form'].loading)
    const hasAapcrAccess = computed(() => store.getters['aapcr/form'].hasAapcrAccess)

    const { isUploadedViewed, viewedForm,
      viewUnpublishedForms, onCloseList } = useViewPublishedFiles()

    const { unpublishedData, isUnpublish,
      openUnpublishRemarks, changeRemarksState, unpublish, onUnpublishCancel,
    } = useUnpublish()

    const permission = { listAapcr: [ "form", "f-aapcr" ] }

    const { aapcrFormPermission } = usePermission(permission)

    // EVENTS
    onMounted(() => {
      store.commit('SET_DYNAMIC_PAGE_TITLE', { pageTitle: PAGE_TITLE })
      store.dispatch('aapcr/CHECK_AAPCR_PERMISSION', { payload: { pmaps_id: store.state.user.pmapsId,  form_id:'aapcr' }})
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

    const viewPdf = params => {
      const { data } = params
      const fromUnpublished = typeof params.fromUnpublished !== 'undefined' ? params.fromUnpublished : false

      let renderer = null
      const documentName = data.document_name || data.file_name

      if(!fromUnpublished && !data.published_date) {
        store.commit('aapcr/SET_STATE', { loading: true })

        renderer = fetchPdfData(data.id)
      }else if(data.published_date){
        renderer = viewSavedPdf(data.published_file)
      }else {
        _message.loading('Loading...')

        renderer = getUnpublishedFormData(data.id)
      }

      renderer.then(response => {
        if (response) {
          const blob = new Blob([response], { type: 'application/pdf' })
          const fileUrl = window.URL.createObjectURL(blob)

          localStorage.setItem('pdf.document.url', fileUrl)
          localStorage.setItem('pdf.document.name', documentName)

          const route = router.resolve({ name: "viewerPdf" });
          window.open(route.href, "_blank")
        }
        if(!fromUnpublished) {
          store.commit('aapcr/SET_STATE', { loading: false })
        }else {
          _message.destroy()
        }
      })
    }

    return {
      documentName,

      columns: listTableColumns, list, loading, hasAapcrAccess, aapcrFormPermission,

      isUploadedViewed, viewedForm,

      updateForm, publish, viewPdf,

      // useUnpublish
      unpublishedData, isUnpublish,

      openUnpublishRemarks, changeRemarksState, unpublish, onUnpublishCancel,

      // useViewPublishedFiles
      viewUnpublishedForms, onCloseList,
    }
  },
})
</script>
