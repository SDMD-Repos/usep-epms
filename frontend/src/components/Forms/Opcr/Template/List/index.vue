<template>
  <div v-if="opcrFormPermission" >
    <form-list-table
      :columns="columns" :data-list="list" :form="formId" :loading="loading" :has-form-access="opcrFormPermission"
      @update-form="updateForm" @publish="publish" @unpublish="unpublish"  />
  </div>
  <div v-else><error403 /></div>
</template>
<script>
import { defineComponent, ref, onMounted, computed } from "vue"
import { useRouter } from 'vue-router'
import { useStore } from 'vuex'
import { listTableColumns } from '@/services/columns'
import FormListTable from '@/components/Tables/Forms/List'
import { usePermission } from '@/services/functions/permission'
import Error403 from '@/components/Errors/403'

export default defineComponent({
  name: "OPCRTemplateList",
  components: { FormListTable, Error403 },
  props: {
    formId: { type: String, default: '' },
  },
  setup(props) {
    const PAGE_TITLE = 'OPCR Template List'

    const store = useStore()
    const router = useRouter()

    // DATA
    const documentName = ref(null)

    // COMPUTED
    const list = computed(() => store.getters['opcrtemplate/form'].list)
    const loading = computed(() => store.getters['opcrtemplate/form'].loading)
    const permission ={
      listOpcr: [ "form", "f-opcr", "fo-template" ],
    }
    // EVENTS
    const { opcrFormPermission } = usePermission(permission)

    onMounted(() => {
      store.commit('SET_DYNAMIC_PAGE_TITLE', { pageTitle: PAGE_TITLE })
      store.dispatch('opcrtemplate/FETCH_LIST')
    })

    // METHODS
    const updateForm = id => {
      router.push({
        name: 'main.form',
        params: { formId: props.formId, opcrTemplateId: id, update: true },
      })
    }

    const publish = data => {
      const payload = {
        id: data.id,
        year: data.year,
      }
      store.dispatch('opcrtemplate/PUBLISH', { payload: payload })
    }

    const unpublish = data => {
      const { id } = data
      store.dispatch('opcrtemplate/UNPUBLISH', { payload: { id: id } })
    }

    return {
      columns: listTableColumns,

      documentName,
      list,
      loading,
      opcrFormPermission,

      updateForm,
      publish,
      unpublish,
    }
  },
})
</script>
