<template>
  <table class="table table-bordered">
    <thead>
      <tr>
        <th :colspan="tableColumnCount" class="pdfHeader textAlignCenter">
          <div>RATING SCALE</div>
        </th>
      </tr>
      <tr>
        <th class="textAlignCenter" rowspan="2">Numerical Rating</th>
        <template v-for="m in nonCustomMeasures" :key="m.id">
          <th :colspan="m.categories.length" :style='{"background-color": `${m.bg_color}`}'>
            <div class="textAlignCenter">{{ m.name + " (" + m.description + ")" }}</div>
          </th>
        </template>
        <th class="textAlignCenter" rowspan="2" colspan="3">Rating Equivalent</th>
      </tr>
      <tr>
        <template v-for="m in nonCustomMeasures" :key="m.id">
          <th class="textAlignCenter" :colspan="m.categories.length" :style='{"background-color": `${m.bg_color}`}'>
            {{ m.variable_equivalent }}
          </th>
        </template>
      </tr>
      <tr>
        <th class="textAlignCenter">Elements</th>
        <template v-for="m in nonCustomMeasures" :key="m.id">
          <th :colspan="m.categories.length" :style='{"background-color": `${m.bg_color}`}'><p>{{ m.elements }}</p></th>
        </template>
        <th class="textAlignCenter" rowspan="2">Average Point Score</th>
        <th class="textAlignCenter" rowspan="2">Adjectival Rating</th>
        <th class="textAlignCenter" rowspan="2">Description</th>
      </tr>
      <tr>
        <th class="textAlignCenter" >Category</th>
        <template v-for="m in nonCustomMeasures" :key="m.id">
          <template v-for="c in m.categories" :key="c.id">
            <th :style='{"background-color": `${m.bg_color}`}'>{{ c.numbering ? c.numbering + ". " + c.name : c.name }}</th>
          </template>
        </template>
      </tr>
    </thead>
    <tbody>
      <tr v-for="r in ratings" :key="r.id">
        <td class="colRating textAlignCenter">{{ r.numerical_rating }}</td>
        <template v-for="m in nonCustomMeasures" :key="m.id">
          <td v-for="c in m.categories" :key="c.id" :style='{"background-color": `${m.bg_color}`}'>
            <template v-for="i in c.items.filter( f => r.id === f.rating.id)" :key="i.id">
              {{ i.description }}
            </template>
          </td>
        </template>
        <td>{{ r.aps_from + " - " + r.aps_to }}</td>
        <td>{{ r.adjectival_rating }}</td>
        <td>{{ r.description }}</td>
      </tr>
    </tbody>
  </table>
</template>
<script>
import { defineComponent, computed } from "vue";

export default defineComponent({
  name: 'PDFMeasuresTable',
  props: {
    measures: { type: Array, default: () => [] },
    ratings: { type: Array, default: () => [] },
  },
  setup(props) {
    // COMPUTED
    const nonCustomMeasures = computed(() => props.measures.filter(i => !i.is_custom ))

    const tableColumnCount = computed(() => {
      let numColumns = 4
      nonCustomMeasures.value.forEach(e => {
        numColumns += e.categories.length
      })

      return numColumns
    })

    return {
      nonCustomMeasures, tableColumnCount,
    }
  },
})
</script>
<style scoped>
.pdfHeader {
  background-color: #d0cece;
  font-weight: bold;
  font-size: 20px;
}

.textAlignCenter {
  margin: auto;
  text-align: center;
}

.colRating {
  background-color: #d0cece;
}
</style>
