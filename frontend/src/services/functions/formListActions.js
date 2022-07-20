import { ref, computed } from 'vue'
import { useStore } from "vuex";
import dayjs from "dayjs";

export const useUnpublish = () => {
  const store = useStore()

  const isUnpublish = ref(false)

  const unpublishedData = ref(null)

  const openUnpublishRemarks = data => {
    changeRemarksState()
    unpublishedData.value = {
      id: data.id,
      documentName: data.document_name,
      fileName: data.published_file,
      officeName: data.office_name,
    }
  }

  const changeRemarksState = () => {
    isUnpublish.value = !isUnpublish.value
  }

  const unpublish = params => {
    const { form, remarks } = params
    let data = {
      id: unpublishedData.value.id,
      remarks: remarks,
      fileName: unpublishedData.value.fileName,
      documentName: unpublishedData.value.documentName,
      officeName: unpublishedData.value.officeName,
    }
    store.dispatch(form + '/UNPUBLISH', { payload: data }).then(() => {
      isUnpublish.value = !isUnpublish.value
      unpublishedData.value = null
    })
  }

  const onUnpublishCancel = params => {
    const { data, form } = params

    const record  = data.status.filter(i => i.status === 'pending')[0]

    store.dispatch('requests/UPDATE_REQUEST_STATUS', {
      payload: {
        id: record.id, status: 'cancelled',
        callback: { dispatch: form + '/FETCH_LIST', payload: null },
      },
    })
  }

  return {
    isUnpublish, unpublishedData,

    openUnpublishRemarks, changeRemarksState, unpublish, onUnpublishCancel,
  }
}

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
      item.changed_date_disp = dayjs(item.changed_date).format(dateFormat.value)
    })
    details.status = statuses
    data.value.viewedForm = details
  }

  const onCloseList = () => {
    data.value.isViewed = false
    data.value.viewedForm = {}
  }

  return {
    isUploadedViewed,
    viewedForm,

    viewUnpublishedForms,
    onCloseList,
  }
}
