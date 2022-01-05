import { computed, ref } from 'vue'

export const useUploadFile = () => {
  const dataUpload = ref({
    isUploadOpen: false,
    cachedId: null,
    okTextUploadModal: '',
    noteInModal: '',
    isConfirmDeleteFile: false,
  })

  const isUploadOpen = computed(() => dataUpload.value.isUploadOpen)

  const unpublish = id => {
    dataUpload.value = {
      isUploadOpen: true,
      cachedId: id,
      okTextUploadModal: 'Unpublish',
      noteInModal: 'Unpublishing this requires you to upload the published PDF copy of the form',
      isConfirmDeleteFile: false,
    }
  }

  return {
    isUploadOpen,
    unpublish,
  }
}
