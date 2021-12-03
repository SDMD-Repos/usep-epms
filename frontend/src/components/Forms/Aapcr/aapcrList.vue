<template>
  <div>
    <form-list-layout :columns="columns" :data-list="list" :form="formId"
                      @publish="publish" @view-pdf="viewPdf"/>
  </div>
</template>
<script>
import { computed, defineComponent, ref, onMounted } from "vue"
import { useStore } from 'vuex'
import moment from 'moment'
import * as apiForm from '@/services/mainForms/aapcr'
import ListMixin from '@/services/formMixins/list'
import FormListLayout from '@/layouts/Forms/list'

export default defineComponent({
  components: {
    FormListLayout,
  },
  props: {
    formId: { type: String, default: '' },
  },
  setup() {
    const PAGE_TITLE = 'AAPCR List'

    const store = useStore()

    // DATA
    const documentName = ref(null)

    // COMPUTED
    const { listTableColumns } = ListMixin()
    const mainStore = computed(() => store.getters.mainStore)
    const list = computed(() => store.getters['aapcr/form'].list)

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
      const id = data.id
      const name = data.document_name
      const fetchPdfData = apiForm.fetchPdfData
      fetchPdfData(id, name).then(response => {
        if (response) {
          // self.visible = true
          const blob = new Blob([response], { type: 'application/pdf' })
          documentName.value = window.URL.createObjectURL(blob)
          // self.fileName = documentName
        }
        /*this.$store.commit('aapcr/SET_STATE', {
          loading: false,
        })*/
      })
    }

    return {
      moment,

      documentName,

      columns: listTableColumns,
      mainStore,
      list,

      publish,
      viewPdf,
    }
  },
})
</script>
