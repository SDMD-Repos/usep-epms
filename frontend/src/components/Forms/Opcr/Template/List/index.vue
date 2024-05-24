<template>
  <div v-if="opcrFormPermission" >
    <form-list-table
      :columns="columns" :data-list="list" :form="formId" :loading="loading"
      @update-form="updateForm" @publish="publish" @unpublish="unpublish"  />

  </div>
  <div v-else><span>You have no permission to access this page.</span></div>
</template>
<script>
import { defineComponent, ref, onMounted, computed } from "vue"
import { useRouter } from 'vue-router'
import { useStore } from 'vuex'
import { listTableColumns } from '@/services/columns'
import { useUnpublish } from '@/services/functions/formListActions'
import FormListTable from '@/components/Tables/Forms/List'
import { usePermission } from '@/services/functions/permission'

export default defineComponent({
  name: "OPCRTemplateList",
  components: { FormListTable },
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
    const {
      // DATA
      opcrFormPermission,
      // METHODS
    } = usePermission(permission)

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
      documentName,

      columns: listTableColumns,
      list,
      loading,

      unpublish,

      updateForm,
      publish,

      opcrFormPermission,
    }
  },
})
</script>
