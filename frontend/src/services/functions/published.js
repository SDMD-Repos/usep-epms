import { computed, ref } from 'vue'
import moment from 'moment'

export const useViewPublishedFiles = args => {
  const data = ref({
    isViewed: false,
    viewedForm: {},
  })

  const isUploadedViewed = computed(() => data.value.isViewed)

  const viewedForm = computed(() => data.value.viewedForm)

  const viewUploadedList = details => {
    const files = [...details.files]
    data.value.isViewed = true
    files.forEach(item => {
      item.created_at_disp = moment(item.created_at).format(args.dateFormat.value)
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
