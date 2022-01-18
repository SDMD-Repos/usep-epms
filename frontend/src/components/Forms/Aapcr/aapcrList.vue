<template>
  <div>
    <form-list-table
      :columns="columns" :data-list="list" :form="formId" :loading="loading"
      @publish="publish" @view-pdf="viewPdf" @unpublish="unpublish" @view-uploaded-list="viewUploadedList" />

    <upload-publish-modal
      :is-upload-open="isUploadOpen" :ok-publish-text="okPublishText"
      :modal-note="noteInModal" :list="fileList" :is-uploading="loading"
      @add-to-list="addUploadItem" @remove-file="removeFile" @upload="uploadFile" @cancel-upload="handleCancelUpload" />

    <uploaded-list-modal
      :modal-state="isUploadedViewed" :form-details="viewedForm"
      @close-list-modal="closeListModal" @view-file="viewPdf" @delete-file="openUploadOnDelete" />
  </div>
</template>
<script>
import { computed, defineComponent, ref, onMounted } from "vue"
import { useRouter } from 'vue-router'
import { useStore } from 'vuex'
import { notification, message } from 'ant-design-vue'
import { listTableColumns } from '@/services/columns'
import { useUploadFile } from '@/services/functions/upload'
import { useViewPublishedFiles } from '@/services/functions/published'
import { updateFile, fetchPdfData, viewUploadedFile } from '@/services/api/mainForms/aapcr'
import FormListTable from '@/components/Tables/Forms/List'
import UploadPublishModal from '@/components/Modals/UploadPublish'
import UploadedListModal from '@/components/Modals/UploadedList'
import moment from 'moment'

export default defineComponent({
  components: {
    FormListTable,
    UploadPublishModal,
    UploadedListModal,
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

    // COMPUTED
    const list = computed(() => store.getters['aapcr/form'].list)
    const loading = computed(() => store.getters['aapcr/form'].loading)

    const {
      // DATA
      isUploadOpen, cachedId, okPublishText, noteInModal, fileList, isConfirmDeleteFile,
      // METHODS
      unpublish, addUploadItem, removeFile, cancelUpload, openUploadOnDelete,
    } = useUploadFile()

    const { isUploadedViewed, viewedForm,
      viewUploadedList, onCloseList, uploadedListState } = useViewPublishedFiles()

    // EVENTS
    onMounted(() => {
      store.commit('SET_DYNAMIC_PAGE_TITLE', { pageTitle: PAGE_TITLE })
      store.dispatch('aapcr/FETCH_LIST')
    })

    // METHODS
    const publish = data => {
      const payload = {
        id: data.id,
        year: data.year,
      }
      store.dispatch('aapcr/PUBLISH', { payload: payload })
    }

    const uploadFile = async () => {
      const formData = new FormData()
      fileList.value.forEach(file => {
        formData.append('files[]', file)
      })
      formData.append('id', cachedId.value)
      if(!isConfirmDeleteFile.value) {
        await store.dispatch('aapcr/UNPUBLISH', { payload: formData })
        await cancelUpload()
      } else {
        store.commit('aapcr/SET_STATE', { loading: true })

        await cancelUpload()
        await onCloseList()

        await updateFile(formData).then(response => {
          if (response) {
            store.dispatch('aapcr/FETCH_LIST').then(() => {
              const { data } = response
              viewUploadedList(data) // open List of Uploaded Files Modal
            })
            notification.success({
              message: 'Success',
              description: 'File was deleted successfully',
            })
            store.commit('aapcr/SET_STATE', {loading: false })
          }
        })
      }
    }

    const viewPdf = data => {
      let renderer = null
      const documentName = data.document_name || data.file_name

      if(!isUploadedViewed.value) {
        store.commit('aapcr/SET_STATE', { loading: true })

        renderer = fetchPdfData
      }else {
        message.loading('Loading...')

        renderer = viewUploadedFile
      }

      renderer(data.id).then(response => {
        if (response) {
          const blob = new Blob([response], { type: 'application/pdf' })
          const fileUrl = window.URL.createObjectURL(blob)

          localStorage.setItem('pdf.document.url', fileUrl)
          localStorage.setItem('pdf.document.name', documentName)

          const route = router.resolve({ name: "viewerPdf" });
          window.open(route.href, "_blank")
        }
        if(!isUploadedViewed.value) {
          store.commit('aapcr/SET_STATE', { loading: false })
        }else {
          message.destroy()
        }
      })
    }

    const handleCancelUpload = () => {
      if(isConfirmDeleteFile.value) {
        uploadedListState(true)
      }
      cancelUpload()
    }

    const closeListModal = () => {
      if(!isConfirmDeleteFile.value) {
        onCloseList()
      } else {
        uploadedListState(false)
      }
    }

    return {
      moment,

      documentName,

      columns: listTableColumns,
      list,
      loading,

      isUploadOpen,
      cachedId,
      okPublishText,
      noteInModal,
      fileList,
      isConfirmDeleteFile,

      isUploadedViewed,
      viewedForm,

      publish,
      viewPdf,
      uploadFile,
      handleCancelUpload,
      closeListModal,

      unpublish,
      addUploadItem,
      removeFile,
      cancelUpload,
      openUploadOnDelete,

      viewUploadedList,
      onCloseList,
    }
  },
})
</script>
