<template>
  <div>
    <a-select v-model:value="mainCategory" placeholder="Select" style="width: 350px" label-in-value>
      <a-select-option v-for="(y, i) in programsByFunction" :value="y.id" :key="i">
        {{ y.name }}
      </a-select-option>
    </a-select>

    <div class="mt-4">
      <indicator-table :form-id="formId"/>
    </div>
  </div>
</template>
<script>
import { computed, defineComponent, ref, onMounted } from "vue"
import { useStore } from 'vuex'
import IndicatorTable from '@/components/Tables/Forms/Main'

export default defineComponent({
  name: "AapcrItems",
  components: { IndicatorTable },
  props: {
    functionId: { type: String, default: "" },
    formId: { type: String, default: "" },
  },
  setup(props) {
    const store = useStore()

    const mainCategory = ref(undefined)

    // COMPUTED
    const programs = computed(() => store.getters['formManager/manager'].programs)

    const programsByFunction = computed( () => { return programs.value.filter(i => i.category_id === props.functionId) })

    // EVENTS
    onMounted(() => {
      store.dispatch('formManager/FETCH_PROGRAMS')
    })

    return {
      mainCategory,
      programsByFunction,
    }
  },
})
</script>
