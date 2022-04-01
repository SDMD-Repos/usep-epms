import { ref, computed } from 'vue'
import moment from 'moment'
import { useStore } from 'vuex'

export const useViewPublishedFiles = () => {
  const store = useStore()

  const data = ref({
    isViewed: false,
    viewedForm: {},
  })

  const isUploadedViewed = computed(() => data.value.isViewed)

  const viewedForm = computed(() => data.value.viewedForm)

  const dateFormat = computed(() => store.getters.mainStore.dateFormat)

  const viewUnpublishedForms = details => {
    const statuses = [...details.status]
    data.value.isViewed = true
    statuses.forEach(item => {
      item.changed_date_disp = moment(item.changed_date).format(dateFormat.value)
    })
    details.status = statuses
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

    viewUnpublishedForms,
    onCloseList,
    uploadedListState,
  }
}
