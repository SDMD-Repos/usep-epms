<template>
  <div>
    <form-list-layout :columns="columns" :data-list="list" :form="formId"
                      @publish="publish" @view-pdf="viewPdf" :loading="loading"/>
  </div>
</template>

<script>
import { computed, defineComponent, ref, onMounted } from "vue"
import { useStore } from "vuex"
import { useRouter } from "vue-router"
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
    const PAGE_TITLE = 'OPCR (VP) List'

    const store = useStore()
    const router = useRouter()

    // DATA
    let columns = ref([])
    const { listTableColumns } = ListMixin()

    // COMPUTED
    const list = computed(() => store.getters['opcrvp/form'].list)
    const loading = computed(() => store.getters['opcrvp/form'].loading)

    onMounted(() => {
      renderColumns()
      store.commit('SET_DYNAMIC_PAGE_TITLE', { pageTitle: PAGE_TITLE })
      store.dispatch('opcrvp/FETCH_LIST')
    })

    // METHODS
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
      store.dispatch('opcrvp/PUBLISH', { payload: payload })
    }

    const viewPdf = data => {
      const route = router.resolve({
        name: "viewerPdf",
        params: {
          formId: props.formId,
          id: data.id,
          documentName: data.office_name,
        },
      });
      window.open(route.href, "_blank");
    }

    return {
      columns,

      list,
      loading,

      publish,
      viewPdf,
    }
  },
})
</script>
