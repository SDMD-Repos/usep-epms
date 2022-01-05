<template>
  <div>
    <div class="row">
      <div class="col-xl-12 col-lg-12">
        <div class="card">
          <div class="card-body">
            <a-tabs v-model:activeKey="activeKey" :animated="false">
              <a-tab-pane tab="Functions" key="1"><categories-form v-if="activeKey === '1'"/></a-tab-pane>
              <a-tab-pane tab="Programs" key="2"><programs-form v-if="activeKey === '2'" /></a-tab-pane>
              <a-tab-pane tab="Sub Categories" key="3"><sub-categories-form v-if="activeKey === '3'"/></a-tab-pane>
            </a-tabs>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
import CategoriesForm from '@/components/Manager/Form/Categories'
import ProgramsForm from '@/components/Manager/Form/Programs'
import SubCategoriesForm from '@/components/Manager/Form/SubCategories'
import { defineComponent, ref } from 'vue'

export default defineComponent({
  components: {
    CategoriesForm,
    ProgramsForm,
    SubCategoriesForm,
  },
  setup() {
    return {
      activeKey: ref('1'),
    }
  },
})
</script>
