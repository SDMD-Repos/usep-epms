<template>
  <div class="row">
    <div class="col-xl-12 col-lg-12">
      <div class="card">
        <div class="card-body">
          <div v-if="opcrFormPermission" >
            <a-tabs v-model:activeKey="activeKey" :animated="false">
              <a-tab-pane tab="Functions" key="1"><opcr-functions-manager v-if="formId === `opcr`" :form-id="formId"/></a-tab-pane>
              <a-tab-pane tab="Programs" key="2"><opcr-programs-manager v-if="formId === `opcr`" :form-id="formId"/></a-tab-pane>
            </a-tabs>
          </div>
          <div v-else><span>You have no permission to access this page.</span></div>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
import { defineComponent, ref, watch, onMounted } from "vue"
import { useRoute } from 'vue-router'
import OpcrFunctionsManager from '@/components/Forms/Opcr/Manager/Form/Functions'
import OpcrProgramsManager from '@/components/Forms/Opcr/Manager/Form/Programs'
import { usePermission } from '@/services/functions/permission'

export default defineComponent({
  components: { OpcrFunctionsManager, OpcrProgramsManager },
  setup() {
    const formId = ref(null)
    const route = useRoute()
    const permission ={
      listOpcr: [ "form", "f-opcr", "fo-manager" ],
    }

    // EVENTS
    const {
      // DATA
      opcrFormPermission,
      // METHODS
    } = usePermission(permission)

    onMounted(() => {
      setFormId(route.params.formId)
    })

    watch(route, to => {
      formId.value = to.params.formId
    })

    // METHODS
    const setFormId = id =>{
      formId.value = id
    }

    return {
      formId,
      opcrFormPermission,
      activeKey: ref('1'),
    }
  },
})
</script>
