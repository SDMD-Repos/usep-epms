<template>
  <div>
    <div class="row">
      <div class="col-xl-12 col-lg-12">
        <div class="card">
          <div class="card-body">
            <a-tabs v-model:activeKey="activeKey" :animated="false">
              <a-tab-pane tab="Functions" key="1"><functions-form v-if="activeKey === '1'"/></a-tab-pane>
              <a-tab-pane tab="Programs" key="2"><programs-form v-if="activeKey === '2'" /></a-tab-pane>
              <a-tab-pane tab="Sub Categories" key="3"><sub-categories-form v-if="activeKey === '3'"/></a-tab-pane>
              <a-tab-pane tab="Fields" key="4"><fields-form v-if="activeKey === '4'"/></a-tab-pane>
            </a-tabs>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
import FunctionsForm from '@/components/Manager/Form/Functions'
import ProgramsForm from '@/components/Manager/Form/Programs'
import SubCategoriesForm from '@/components/Manager/Form/SubCategories'
import FieldsForm from '@/components/Manager/Form/Fields'
import { defineComponent, ref } from 'vue'

export default defineComponent({
  components: {
    FunctionsForm,
    ProgramsForm,
    SubCategoriesForm,
    FieldsForm,
  },
  setup() {
    return {
      activeKey: ref('1'),
    }
  },
})
</script>
