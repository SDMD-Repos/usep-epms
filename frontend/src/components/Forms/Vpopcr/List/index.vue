<template>
  <div  v-if="hasVpopcrAccess || opcrvpFormPermission">
    <form-list-table
      :columns="columns" :data-list="list" :form="formId" :loading="loading"
      @update-form="updateForm" @publish="publish" @view-pdf="viewPdf" @unpublish="openUnpublishRemarks"
      @view-uploaded-list="viewUploadedList" @view-unpublished-forms="viewUnpublishedForms" @cancel-unpublish-request="onUnpublishCancel"/>

    <unpublished-forms-modal
      :modal-state="isUploadedViewed" :form-details="viewedForm"
      @close-list-modal="onCloseList" @view-file="viewPdf" />

    <unpublish-remarks-modal
      :is-unpublish="isUnpublish" :form-id="formId"
      @unpublish="unpublish" @close-remarks-modal="changeRemarksState" />
  </div>
  <div v-else><error403 /></div>
</template>

<script>
import { defineComponent, ref, onMounted, onBeforeMount, inject, computed } from "vue"
import { useStore } from "vuex"
import { useRouter } from "vue-router"
import { listTableColumns } from '@/services/columns'
import { useViewPublishedFiles } from '@/services/functions/formListActions'
import { renderPdf } from '@/services/api/mainForms/vpopcr'
import FormListTable from '@/components/Tables/Forms/List'
import { useUnpublish } from '@/services/functions/formListActions'
import { usePermission } from '@/services/functions/permission'
import UnpublishedFormsModal from '@/components/Modals/UnpublishedForms'
import UnpublishRemarksModal from '@/components/Modals/Remarks'
import { getUnpublishedFormData } from '@/services/api/system/requests'
import Error403 from '@/components/Errors/403'

export default defineComponent({
  name: "VpOpcrList",
  components: { FormListTable, UnpublishedFormsModal, UnpublishRemarksModal, Error403 },
  props: {
    formId: { type: String, default: '' },
  },
  setup(props) {
    const PAGE_TITLE = 'OPCR (VP) List'

    const store = useStore()
    const router = useRouter()

    const _message = inject('a-message')

    // DATA
    let columns = ref([])

    const { unpublishedData, isUnpublish,
      openUnpublishRemarks, changeRemarksState, unpublish, onUnpublishCancel,
    } = useUnpublish()

    const { isUploadedViewed, viewedForm, viewUploadedList, onCloseList, viewUnpublishedForms } = useViewPublishedFiles()

    // COMPUTED
    const list = computed(() => store.getters['vpopcr/form'].list)
    const loading = computed(() => store.getters['vpopcr/form'].loading)
    const hasVpopcrAccess = computed(() => store.getters['vpopcr/form'].hasVpopcrAccess)

    const permission = { listOpcrvp: [ "form", "f-opcrvp" ] }

    const { opcrvpFormPermission } = usePermission(permission)

    // EVENTS
    onBeforeMount(() => { renderColumns() })

    onMounted(() => {
      store.commit('SET_DYNAMIC_PAGE_TITLE', { pageTitle: PAGE_TITLE })
      store.dispatch('vpopcr/CHECK_VPOPCR_PERMISSION', { payload: { pmapsId: store.state.user.pmapsId, formId:'vpopcr' }})
      store.dispatch('vpopcr/FETCH_LIST')
    })

    // METHODS
    const updateForm = id => {
      router.push({
        name: 'main.form',
        params: { formId: props.formId, vpOpcrId: id },
      })
    }

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

    const publish = data => {
      const payload = {
        id: data.id,
        year: data.year,
        officeId: data.office_id,
      }
      store.dispatch('vpopcr/PUBLISH', { payload: payload })
    }

    const viewPdf = params => {
      const { data } =  params
      const fromUnpublished = typeof params.fromUnpublished !== 'undefined' ? params.fromUnpublished : false

      let renderer = null
      const documentName = data.office_name || data.file_name

      if(!fromUnpublished) {
        store.commit('vpopcr/SET_STATE', { loading: true })

        renderer = renderPdf
      }else {
        _message.loading('Loading...')

        renderer = getUnpublishedFormData
      }

      renderer(data.id).then(response => {
        if (response) {
          const blob = new Blob([response], { type: 'application/pdf' })
          const fileUrl = window.URL.createObjectURL(blob)

          localStorage.setItem('pdf.document.url', fileUrl)
          localStorage.setItem('pdf.document.name', documentName)

          const route = router.resolve({ name: "viewerPdf" })
          window.open(route.href, "_blank")
        }
        if(!fromUnpublished) {
          store.commit('vpopcr/SET_STATE', { loading: false })
        }else {
          _message.destroy()
        }
      })
    }

    return {
      columns,

      list,
      loading,

      isUploadedViewed,
      viewUnpublishedForms,
      viewedForm,
      hasVpopcrAccess,
      opcrvpFormPermission,

      renderPdf,
      updateForm,
      publish,
      viewPdf,

      // useUnpublish
      unpublishedData, isUnpublish,

      openUnpublishRemarks, changeRemarksState, unpublish, onUnpublishCancel,

      viewUploadedList,
      onCloseList,
    }
  },
})
</script>
