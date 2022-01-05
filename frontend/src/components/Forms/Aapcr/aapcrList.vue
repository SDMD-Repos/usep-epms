<template>
  <div>
    <form-list-layout
      :columns="columns" :data-list="list" :form="formId" :loading="loading"
      @publish="publish" @view-pdf="viewPdf" @unpublish="unpublish"/>

    <upload-publish :is-upload-open="isUploadOpen"/>
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

    // COMPUTED
    const mainStore = computed(() => store.getters.mainStore)
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

    const { unpublish, isUploadOpen } = useUploadFile()

    return {
      moment,

      documentName,

      columns: listTableColumns,
      mainStore,
      list,
      loading,

      isUploadOpen,

      publish,
      viewPdf,
      unpublish,
    }
  },
})
</script>
