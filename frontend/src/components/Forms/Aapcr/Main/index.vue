<template>
  <div>
    <a-spin :spinning="loading" tip="Initializing form...">
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
              :categories="categories" :year="year" :counter="counter" :budget-list="budgetList"
              @add-targets-basis-item="addTargetsBasisItem" @update-data-source="updateDataSource" @delete-source-item="deleteSourceItem"
              @add-deleted-item="addDeletedItem" @update-source-item="updateSourceItem" @add-budget-list-item="addBudgetListItem"/>
          </a-collapse-panel>
        </a-collapse>
      </div>

      <div class="mt-4" v-if="budgetList.length">
        <budget-list-component :budget-list="budgetList" @delete-budget-item="deleteBudgetItem"/>
      </div>
    </a-spin>
  </div>
</template>
<script>
import { defineComponent, ref, onMounted, computed } from "vue";
import { useStore } from 'vuex'
import IndicatorComponent from './partials/items'
import BudgetListComponent from './partials/budget'
import { useFormOperations } from '@/services/functions/indicator'
import { useProgramBudget } from '@/services/functions/form/main'

export default defineComponent({
  name: "AAPCRForm",
  components: { IndicatorComponent, BudgetListComponent },
  props: {
    formId: { type: String, default: '' },
  },
  setup() {
    const PAGE_TITLE = "AAPCR Form"

    const store = useStore()

    // DATA
    const year = ref(new Date().getFullYear())
    const activeKey = ref('0')
    const deletedItems = ref([])

    const {
      // DATA
      dataSource, targetsBasisList, counter,
      // METHODS
      updateDataSource, addTargetsBasisItem, updateSourceCount, deleteSourceItem, updateSourceItem } = useFormOperations()

    const { budgetList, addBudgetListItem, deleteBudgetItem } = useProgramBudget()

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
    const loading = computed(() => store.getters['formManager/manager'].loading)

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
      await store.dispatch('formManager/FETCH_PROGRAMS')
      // formLoading.value = false
    }

    const addDeletedItem = id => {
      deletedItems.value.push(id)
    }

    return {
      year,
      activeKey,
      dataSource,

      years,
      categories,
      loading,
      deletedItems,

      addDeletedItem,

      // useFormOperations
      targetsBasisList,
      counter,

      // useProgramBudget
      budgetList,
      addBudgetListItem,
      deleteBudgetItem,

      updateDataSource,
      addTargetsBasisItem,
      updateSourceCount,
      deleteSourceItem,
      updateSourceItem,
    }
  },
})
</script>
<style lang="scss">
@import "@/components/Forms/style.module.scss";
</style>
