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
import { useStore } from "vuex"
import { useRouter } from "vue-router"
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
    const PAGE_TITLE = 'OPCR (VP) List'

    const store = useStore()
    const router = useRouter()

    // DATA
    let columns = ref([])


    const {
      // DATA
      isUploadOpen, cachedId, okPublishText, noteInModal, fileList,
      // METHODS
      unpublish, addUploadItem, removeFile, cancelUpload,
    } = useUploadFile()

    // COMPUTED
    const list = computed(() => store.getters['opcrvp/form'].list)
    const loading = computed(() => store.getters['opcrvp/form'].loading)

    onMounted(() => {
      renderColumns()
      store.commit('SET_DYNAMIC_PAGE_TITLE', { pageTitle: PAGE_TITLE })
      store.dispatch('opcrvp/FETCH_LIST')
    })

    // METHODS
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

    const uploadFile = async () => {
      const formData = new FormData()
      fileList.value.forEach(file => {
        formData.append('files[]', file)
      })
      formData.append('id', cachedId.value)
      await store.dispatch('opcrvp/UNPUBLISH', { payload: formData })
      await cancelUpload()
    }

    const publish = data => {
      const payload = {
        id: data.id,
        year: data.year,
        officeId: data.office_id,
      }
      store.dispatch('opcrvp/PUBLISH', { payload: payload })
    }

    const viewPdf = data => {
      const route = router.resolve({
        name: "viewerPdf",
        params: {
          formId: props.formId,
          id: data.id,
          documentName: data.office_name,
        },
      });
      window.open(route.href, "_blank");
    }

    return {
      columns,

      isUploadOpen,
      okPublishText,
      noteInModal,
      cachedId,
      list,
      loading,
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
