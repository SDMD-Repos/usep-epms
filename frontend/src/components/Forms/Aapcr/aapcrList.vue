<template>
  <div>
    <form-list-layout :columns="columns" :data-list="list" :form="formId"
                      @publish="publish" @view-pdf="viewPdf" :loading="loading"/>


  </div>
</template>
<script>
import { computed, defineComponent, ref, onMounted } from "vue"
import { useRouter } from 'vue-router'
import { useStore } from 'vuex'
import moment from 'moment'
import ListMixin from '@/services/formMixins/list'
import FormListLayout from '@/layouts/Forms/list'

export default defineComponent({
  components: {
    FormListLayout,
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
    const { listTableColumns } = ListMixin()
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

    return {
      moment,

      documentName,

      columns: listTableColumns,
      mainStore,
      list,
      loading,

      publish,
      viewPdf,
    }
  },
})
</script>
