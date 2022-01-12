<template>
  <div>
    <form-list-layout
      :columns="columns" :data-list="list" :form="formId" :loading="loading"
      @publish="publish" @view-pdf="viewPdf" @unpublish="unpublish" @view-uploaded-list="viewUploadedList"/>

    <upload-publish
      :is-upload-open="isUploadOpen" :ok-publish-text="okPublishText"
      :modal-note="noteInModal" :list="fileList" :is-uploading="loading" :is-delete-uploaded="isConfirmDeleteFile"
      @add-to-list="addUploadItem" @remove-file="removeFile" @upload="uploadFile" @cancel-upload="cancelUpload"
      @view-uploaded-list="uploadedListState"/>

    <uploaded-list :modal-state="isUploadedViewed" :form-details="viewedForm"
                   @close-list-modal="onCloseList" @view-file="viewPdf" @delete-file="openUploadOnDelete" />
  </div>
</template>
<script>
import { computed, defineComponent, ref, onMounted } from "vue"
import { useRouter } from 'vue-router'
import { useStore } from 'vuex'
import { notification } from 'ant-design-vue'
import { listTableColumns } from '@/services/columns'
import { useUploadFile } from '@/services/functions/upload'
import { useViewPublishedFiles } from '@/services/functions/published'
import { updateFile } from '@/services/api/mainForms/aapcr'
import FormListLayout from '@/layouts/Forms/List'
import UploadPublish from '@/components/Modals/UploadPublish'
import UploadedList from '@/components/Modals/UploadedList'
import moment from 'moment'

export default defineComponent({
  components: {
    FormListLayout,
    UploadPublish,
    UploadedList,
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
    const dateFormat = computed(() => store.getters.mainStore.dateFormat)

    const {
      // DATA
      isUploadOpen, cachedId, okPublishText, noteInModal, fileList, isConfirmDeleteFile,
      // METHODS
      unpublish, addUploadItem, removeFile, cancelUpload, openUploadOnDelete,
    } = useUploadFile()

    const args = {
      dateFormat: dateFormat,
    }

    const { isUploadedViewed, viewedForm,
      viewUploadedList, onCloseList, uploadedListState } = useViewPublishedFiles(args)

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
        store.commit('aapcr/SET_STATE', {
          loading: true,
        })
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
          }
          store.commit('aapcr/SET_STATE', {
            loading: false,
          })
        })
      }

    }

    const viewPdf = data => {
      const route = router.resolve({
        name: "viewerPdf",
        params: {
          fromUploaded: !!data.file_name,
          formId: props.formId,
          id: data.id,
          documentName: data.document_name || data.file_name,
        },
      });
      window.open(route.href, "_blank")
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

      unpublish,
      addUploadItem,
      removeFile,
      cancelUpload,
      openUploadOnDelete,

      viewUploadedList,
      onCloseList,
      uploadedListState,
    }
  },
})
</script>
