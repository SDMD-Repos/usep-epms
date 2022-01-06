import { computed, ref } from 'vue'

export const useUploadFile = () => {
  const dataUpload = ref({
    isUploadOpen: false,
    cachedId: null,
    okPublishText: '',
    noteInModal: '',
    isConfirmDeleteFile: false,
  })

  const isUploadOpen = computed(() => dataUpload.value.isUploadOpen)

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

  return {
    isUploadOpen,
    okPublishText,
    noteInModal,

    unpublish,
  }
}
