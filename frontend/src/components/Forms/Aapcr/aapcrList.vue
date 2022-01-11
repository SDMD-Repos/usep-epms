<template>
  <div>
    <form-list-layout
      :columns="columns" :data-list="list" :form="formId" :loading="loading"
      @publish="publish" @view-pdf="viewPdf" @unpublish="unpublish"/>

    <upload-publish
      :is-upload-open="isUploadOpen" :ok-publish-text="okPublishText"
      :modal-note="noteInModal" :list="fileList" :is-uploading="loading"
      @add-to-list="addUploadItem" @remove-file="removeFile" @upload="uploadFile" @cancel-upload="cancelUpload"/>

  </div>
</template>
<script>
import { computed, defineComponent, ref, onMounted } from "vue"
import { useRouter } from 'vue-router'
import { useStore } from 'vuex'
import moment from 'moment'
import { listTableColumns } from '@/services/columns'
import FormListLayout from '@/layouts/Forms/List'
import UploadPublish from '@/components/Modals/UploadPublish'
import { useUploadFile } from '@/services/functions/upload'

export default defineComponent({
  components: {
    FormListLayout,
    UploadPublish,
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

    const {
      // DATA
      isUploadOpen, cachedId, okPublishText, noteInModal, fileList,
      // METHODS
      unpublish, addUploadItem, removeFile, cancelUpload,
    } = useUploadFile()

    // COMPUTED
    const list = computed(() => store.getters['aapcr/form'].list)
    const loading = computed(() => store.getters['aapcr/form'].loading)

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
      await store.dispatch('aapcr/UNPUBLISH', { payload: formData })
      await cancelUpload()
    }

    const viewPdf = data => {
      const route = router.resolve({
        name: "viewerPdf",
        params: {
          formId: props.formId,
          id: data.id,
          documentName: data.document_name,
        },
      });
      window.open(route.href, "_blank");
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

      publish,
      viewPdf,
      uploadFile,

      unpublish,
      addUploadItem,
      removeFile,
      cancelUpload,
    }
  },
})
</script>
