<template>
  <div>
    <a-select v-model:value="year" placeholder="Select year" style="width: 200px">
      <template v-for="(y, i) in years" :key="i">
        <a-select-option :value="y"> {{ y }} </a-select-option>
      </template>
    </a-select>

    <div class="mt-4">
      <a-collapse v-model:activeKey="activeKey" accordion>
        <a-collapse-panel v-for="(category, key) in categories" :key="`${key}`" :header="category.name">
          <indicator-component
            :function-id="category.id" :form-id="formId" :item-source="dataSource" :targets-basis-list="targetsBasisList"
            :categories="categories" :year="year"/>
        </a-collapse-panel>
      </a-collapse>
    </div>
  </div>
</template>
<script>
import { computed, defineComponent, ref, onMounted } from "vue";
import { useStore } from 'vuex'
import IndicatorComponent from './partials/items'

export default defineComponent({
  name: "AAPCRForm",
  components: { IndicatorComponent },
  props: {
    formId: { type: String, default: '' },
  },
  setup() {
    const PAGE_TITLE = "AAPCR Form"

    const store = useStore()

    // DATA
    const year = ref(new Date().getFullYear())
    const activeKey = ref('0')
    const dataSource = ref([])
    const targetsBasisList = ref([])

    // COMPUTED
    const years = computed(() => {
      const now = new Date().getFullYear()
      const min = 10
      const lists = []
      for (let i = now; i >= (now - min); i--) {
        lists.push(i)
      }
      return lists
    })

    const categories = computed(() => store.getters['formManager/functions'])

    // EVENTS
    onMounted(() => {
      store.commit('SET_DYNAMIC_PAGE_TITLE', { pageTitle: PAGE_TITLE })
      initializeFormFields()
    })

    // METHODS
    const initializeFormFields = async () => {
      // formLoading.value = true
      await store.dispatch('formManager/FETCH_FUNCTIONS')
      await store.dispatch('formManager/FETCH_SUB_CATEGORIES')
      await store.dispatch('formManager/FETCH_MEASURES')
      await store.dispatch('formManager/FETCH_CASCADING_LEVELS')
      // formLoading.value = false
    }

    return {
      year,
      activeKey,
      dataSource,
      targetsBasisList,

      years,
      categories,
    }
  },
})
</script>
<style lang="scss">
@import "@/components/Forms/style.module.scss";
</style>
