<template>
  <div>
    <a-select v-model:value="year" placeholder="Select year" style="width: 200px" @change="fetchSettings">
      <template v-for="(y, i) in years" :key="i">
        <a-select-option :value="y">
          {{ y }}
        </a-select-option>
      </template>
    </a-select>

    <div class="mt-4">

    </div>
  </div>
</template>
<script>
import {computed, defineComponent, ref} from "vue"

export default defineComponent({
  name: 'OfficesAccountableComponent',
  setup() {
    // DATA
    const year = ref(new Date().getFullYear())

    // COMPUTED
    const years = computed(() => {
      const max = new Date().getFullYear() + 1
      const min = 10
      const lists = []
      for (let i = max; i >= (max - min); i--) {
        lists.push(i)
      }
      return lists
    })

    //METHODS
    const fetchSettings = () => {

    }

    return {
      year,

      years,

      fetchSettings,
    }
  },
})
</script>
