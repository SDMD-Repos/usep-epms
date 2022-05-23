<template>
  <div  v-if="hasVpopcrAccess || opcrvpFormPermission">
    <form-list-table
      :columns="columns" :data-list="list" :form="formId" :loading="loading"
      @update-form="updateForm" @publish="publish" @view-pdf="viewPdf" @unpublish="unpublish" @view-uploaded-list="viewUploadedList" />

<!--    <upload-publish-modal
      :is-upload-open="isUploadOpen" :ok-publish-text="okPublishText"
      :modal-note="noteInModal" :list="fileList" :is-uploading="loading"
      @add-to-list="addUploadItem" @remove-file="removeFile" @upload="uploadFile" @cancel-upload="handleCancelUpload"/>-->

    <unpublished-forms-modal
      :modal-state="isUploadedViewed" :form-details="viewedForm"
      @close-list-modal="closeListModal" @view-file="viewPdf" />
  </div>
   <div v-else><span>You have no permission to access this page.</span></div>
</template>

<script>
import { computed, defineComponent, ref, onMounted } from "vue"
import { useStore } from "vuex"
import { useRouter } from "vue-router"
import { listTableColumns } from '@/services/columns'
// import { useUploadFile } from '@/services/functions/upload'
import { useViewPublishedFiles } from '@/services/functions/formListActions'
import { renderPdf, viewUploadedFile, updateFile } from '@/services/api/mainForms/opcrvp'
import FormListTable from '@/components/Tables/Forms/List'
// import UploadPublishModal from '@/components/Modals/UploadPublish'
import UnpublishedFormsModal from '@/components/Modals/UnpublishedForms'
import { message, notification } from "ant-design-vue"
import { usePermission } from '@/services/functions/permission'

export default defineComponent({
  components: {
    FormListTable,
    // UploadPublishModal,
    UnpublishedFormsModal,
  },
  props: {
    formId: { type: String, default: '' },
  },
  setup(props) {
    const PAGE_TITLE = 'OPCR (VP) List'

    const store = useStore()
    const router = useRouter()

    // DATA
    let columns = ref([])

    /*const {
      // DATA
      isUploadOpen, cachedId, okPublishText, noteInModal, fileList, isConfirmDeleteFile,
      // METHODS
      unpublish, addUploadItem, removeFile, cancelUpload, openUploadOnDelete,
    } = useUploadFile()*/

    const { isUploadedViewed, viewedForm,
      viewUploadedList, onCloseList, uploadedListState } = useViewPublishedFiles()

    // COMPUTED
    const list = computed(() => store.getters['opcrvp/form'].list)
    const loading = computed(() => store.getters['opcrvp/form'].loading)
    const hasVpopcrAccess = computed(() => store.getters['opcrvp/form'].hasVpopcrAccess)

    const permission = { listOpcrvp: [ "form", "f-opcrvp" ] }

    const { opcrvpFormPermission } = usePermission(permission)

    onMounted(() => {
      renderColumns()
      store.commit('SET_DYNAMIC_PAGE_TITLE', { pageTitle: PAGE_TITLE })
      store.dispatch('opcrvp/CHECK_VPOPCR_PERMISSION', { payload: { pmaps_id: store.state.user.pmapsId, form_id:'vpopcr' }})
      store.dispatch('opcrvp/FETCH_LIST')
    })

    // METHODS
    const updateForm = id => {
      router.push({
        name: 'main.form',
        params: { formId: props.formId, vpOpcrId: id },
      })
    }

    const renderColumns = () => {
      const index = listTableColumns.findIndex(i => i.key === 'documentName')
      let copyColumns = []
      copyColumns = [...listTableColumns]
      copyColumns.splice(index, 0, {
        title: 'Office Name',
        key: 'officeName',
        dataIndex: 'office_name',
        className: 'column-document-name',
        width: 250,
      })
      columns.value = [...copyColumns.filter(i => i.key !== 'documentName')]
    }

    const publish = data => {
      const payload = {
        id: data.id,
        year: data.year,
        officeId: data.office_id,
      }
      store.dispatch('opcrvp/PUBLISH', { payload: payload })
    }

    const unpublish = () => {

    }

    const viewPdf = data => {
      let renderer = null
      const documentName = data.office_name || data.file_name

      if(!isUploadedViewed.value) {
        store.commit('opcrvp/SET_STATE', { loading: true })

        renderer = renderPdf
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

          const route = router.resolve({ name: "viewerPdf" })
          window.open(route.href, "_blank")
        }
        if(!isUploadedViewed.value) {
          store.commit('opcrvp/SET_STATE', { loading: false })
        }else {
          message.destroy()
        }
      })
    }

    const uploadFile = async () => {
      const formData = new FormData()
      fileList.value.forEach(file => {
        formData.append('files[]', file)
      })
      formData.append('id', cachedId.value)
      if(!isConfirmDeleteFile.value) {
        await store.dispatch('opcrvp/UNPUBLISH', { payload: formData })
        await cancelUpload()
      }else {
        store.commit('opcrvp/SET_STATE', { loading: true })

        await cancelUpload()
        await onCloseList()

        await updateFile(formData).then(response => {
          if(response) {
            store.dispatch('opcrvp/FETCH_LIST').then(() => {
              const { data } = response
              viewUploadedList(data) // open List of Uploaded Files Modal
            })
            notification.success({
              message: 'Success',
              description: 'File was deleted successfully',
              })
            store.commit('opcrvp/SET_STATE', { loading: false })
          }
        })
      }
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
      columns,

      list,
      loading,

      /*isUploadOpen,
      okPublishText,
      noteInModal,
      cachedId,
      fileList,*/

      isUploadedViewed,
      viewedForm,
      hasVpopcrAccess,
      opcrvpFormPermission,

      updateForm,
      publish,
      unpublish,
      viewPdf,
      uploadFile,
      handleCancelUpload,
      closeListModal,

      /*unpublish,
      addUploadItem,
      removeFile,
      cancelUpload,
      openUploadOnDelete,*/

      viewUploadedList,
      onCloseList,
    }
  },
})
</script>
