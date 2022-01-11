import { computed, ref } from 'vue'

export const useUploadFile = () => {
  const dataUpload = ref({
    isUploadOpen: false,
    cachedId: null,
    okPublishText: '',
    noteInModal: '',
    isConfirmDeleteFile: false,
  })

  const fileList = ref([])

  const isUploadOpen = computed(() => dataUpload.value.isUploadOpen)

  const cachedId = computed(() => dataUpload.value.cachedId)

  const okPublishText = computed(() => dataUpload.value.okPublishText)

  const noteInModal = computed(() => dataUpload.value.noteInModal)

  const unpublish = id => {
    dataUpload.value = {
      isUploadOpen: true,
      cachedId: id,
      okPublishText: 'Unpublish',
      noteInModal: 'Unpublishing this requires you to upload the published PDF copy of the form',
      isConfirmDeleteFile: false,
    }
  }

  const addUploadItem = file => {
    fileList.value = [...fileList.value, file]
  }

  const removeFile = file => {
    const index = fileList.value.indexOf(file)
    const newFileList = fileList.value.slice()
    newFileList.splice(index, 1)
    fileList.value = newFileList
  }

  const cancelUpload = () => {
    dataUpload.value = {
      isUploadOpen: false,
      cachedId: null,
      okPublishText: '',
      noteInModal: '',
      isConfirmDeleteFile: false,
    }
  }

  return {
    isUploadOpen,
    cachedId,
    okPublishText,
    noteInModal,
    fileList,

    unpublish,
    addUploadItem,
    removeFile,
    cancelUpload,
  }
}
