import { computed, ref } from 'vue'
import { useStore } from 'vuex'
import moment from 'moment'

export const useViewPublishedFiles = () => {
  const store = useStore()

  const data = ref({
    isViewed: false,
    viewedForm: {},
  })

  const isUploadedViewed = computed(() => data.value.isViewed)

  const viewedForm = computed(() => data.value.viewedForm)

  const dateFormat = computed(() => store.getters.mainStore.dateFormat)

  const viewUploadedList = details => {
    const files = [...details.files]
    data.value.isViewed = true
    files.forEach(item => {
      item.created_at_disp = moment(item.created_at).format(dateFormat.value)
    })
    details.files = files
    data.value.viewedForm = details
  }

  const uploadedListState = details => {
    data.value.isViewed = details
  }

  const onCloseList = () => {
    data.value = {
      isViewed: false,
      viewedForm: {},
    }
  }

  return {
    isUploadedViewed,
    viewedForm,

    viewUploadedList,
    onCloseList,
    uploadedListState,
  }
}
