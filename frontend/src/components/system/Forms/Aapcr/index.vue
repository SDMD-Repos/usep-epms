<template>
  <div>
    <div class="air__utils__heading">
      <h5>AAPCR</h5>
    </div>
    <div class="row">
      <div class="col-xl-12 col-lg-12">
        <div class="card">
          <div class="card-header card-header-flex">
            <div class="d-flex flex-column justify-content-center mr-auto">
              <h5 class="mb-0">Create New</h5>
            </div>
          </div>
          <div class="card-body" style="text-align:justify">
            <a-select v-model="year" placeholder="Select year" style="width: 200px">
              <template v-for="(y, i) in years">
                <a-select-option :value="y" :key="i">
                  {{ y }}
                </a-select-option>
              </template>
            </a-select>
            <div class="mt-4">
              <a-collapse v-model="activeKey">
                <a-collapse-panel v-for="(category, key) in categories" :key="`${key}`" :header="category.name">
                  <item-list :year="year" :function-id="category.id" :categories="categories"/>
                </a-collapse-panel>
              </a-collapse>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
import { mapState } from 'vuex'
import ItemList from './partials/itemList'

export default {
  components: {
    ItemList,
  },
  data() {
    return {
      year: new Date().getFullYear(),
      dataSource: [],
      activeKey: '0',
    }
  },
  computed: {
    ...mapState({
      categories: state => state.formSettings.functions,
    }),
    years() {
      const now = new Date().getFullYear()
      const min = 10
      const lists = []
      for (let i = now; i >= (now - min); i--) {
        lists.push(i)
      }
      return lists
    },
  },
  created() {
    this.onLoad()
  },
  methods: {
    onLoad() {
      this.$store.dispatch('formSettings/FETCH_FUNCTIONS')
    },
  },
}
</script>
