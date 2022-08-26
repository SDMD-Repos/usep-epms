<template>
  <div  v-if="hasOpcrAccess || opcrFormPermission">
    <form-list-table
      :columns="columns" :data-list="list" :form="formId" :loading="loading"
      @update-form="updateForm" @publish="publish" @unpublish="unpublish" @unpublish-template="unpublishTemplate"/>
  </div>
  <div v-else><error403 /></div>
</template>
<script>
import { defineComponent, ref, onMounted, computed } from "vue"
import { useRouter } from 'vue-router'
import { useStore } from 'vuex'
import { listTableColumns } from '@/services/columns'
import { useUnpublish } from '@/services/functions/formListActions'
import FormListTable from '@/components/Tables/Forms/List'
import { usePermission } from '@/services/functions/permission'
import Error403 from '@/components/Errors/403'

export default defineComponent({
  name: "OpcrList",
  components: {
    FormListTable, Error403,
  },
  props: {
    formId: { type: String, default: '' },
  },
  setup(props) {
    const PAGE_TITLE = 'OPCR List'

    const store = useStore()
    const router = useRouter()

    // DATA
    const documentName = ref(null)
    const permission = { listOpcr: [ "form", "f-opcr", "fo-formlist"] }
    const { opcrFormPermission } = usePermission(permission)

    // COMPUTED
    const list = computed(() => store.getters['opcr/form'].list)
    const loading = computed(() => store.getters['opcr/form'].loading)
    const hasOpcrAccess = computed(() => store.getters['opcr/form'].hasOpcrAccess)
    const { unpublish } = useUnpublish()

    // EVENTS
    onMounted(() => {
      store.commit('SET_DYNAMIC_PAGE_TITLE', { pageTitle: PAGE_TITLE })
      store.dispatch('opcr/FETCH_LIST')
    })

    // METHODS
    const updateForm = id => {
      router.push({
        name: 'main.form',
        params: { formId: props.formId, opcrTemplateId: id },
      })
    }

    const publish = data => {
      const payload = {
        id: data.id,
        year: data.year,
      }
      store.dispatch('opcr/PUBLISH', { payload: payload })
    }

    return {
      documentName,

      columns: listTableColumns,
      list,
      loading,

      unpublish,

      updateForm,
      publish,

      hasOpcrAccess,
      opcrFormPermission,
    }
  },
})
</script>
