<template>
  <div>
    <a-row type="flex">
      <a-col :sm="{ span: 4 }" :md="{ span: 3 }" :lg="{ span: 2 }"><b>Fiscal Year:</b></a-col>
      <a-col :sm="{ span: 12, offset: 1 }" :md="{ span: 4, offset: 1 }" :lg="{ span: 3, offset: 1 }">
        <a-select v-model:value="year" placeholder="Select year" style="width: 200px" @change="checkFormAvailability">
          <template v-for="(y, i) in years" :key="i">
            <a-select-option :value="y"> {{ y }} </a-select-option>
          </template>
        </a-select>
      </a-col>
    </a-row>
    <div class="w-100 mt-2"></div>
    <a-row>
      <a-col :sm="{ span: 4 }" :md="{ span: 3 }" :lg="{ span: 2 }"><b>VP Office :</b></a-col>
      <a-col :sm="{ span: 12, offset: 1 }" :md="{ span: 10, offset: 1 }" :lg="{ span: 10, offset: 1 }">
        <a-select v-model:value="vpOffice" placeholder="Select VP Office" style="width: 100%" :options="vpOfficesList"
                  option-label-prop="title" allow-clear label-in-value>
          <template #option="{ title }">
            {{ title }}
          </template>
        </a-select>
      </a-col>
    </a-row>
  </div>
</template>
<script>
import { defineComponent, ref, computed, onMounted } from 'vue'
import { useStore } from 'vuex'
import { useFormOperations } from '@/services/functions/indicator'

export default defineComponent({
  name: "VpOPCRForm",
  props: {
    formId: { type: String, default: '' },
  },
  setup() {
    const PAGE_TITLE = "OPCR (VP) Form"

    const store = useStore()

    const vpOffice = ref(undefined)

    const { year, cachedYear, years } = useFormOperations()

    const vpOfficesList = computed(() => store.getters['external/external'].vpOffices)

    onMounted(() => {
      store.commit('SET_DYNAMIC_PAGE_TITLE', { pageTitle: PAGE_TITLE })
      store.dispatch('external/FETCH_VP_OFFICES', { payload: { officesOnly: 1 } })
    })

    return {
      vpOffice,

      vpOfficesList,

      // useFormOperations
      year,
      cachedYear,
      years,
    }
  },
})
</script>
