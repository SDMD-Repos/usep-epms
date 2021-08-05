<template>
  <div>
    <div class="row">
      <div class="col-xl-12 col-lg-12">
        <div class="card">
          <div class="card-header card-header-flex flex-column">
            <a-tabs defaultActiveKey="1" class="kit-tabs kit-tabs-bold" @change="callback">
              <a-tab-pane tab="Functions" key="1" />
              <a-tab-pane tab="Programs" key="2" />
              <a-tab-pane tab="Sub Categories" key="3" />
            </a-tabs>
          </div>
          <div class="card-body">
            <categories-form v-if="activeKey === '1'" />
            <programs-form v-if="activeKey === '2'" />
            <sub-categories-form v-if="activeKey === '3'" />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
import SubCategoriesForm from '@/components/system/AppSettings/Form/SubCategories'
import CategoriesForm from '@/components/system/AppSettings/Form/Categories'
import ProgramsForm from '@/components/system/AppSettings/Form/Programs'
export default {
  components: {
    ProgramsForm,
    CategoriesForm,
    SubCategoriesForm,
  },
  data() {
    return {
      activeKey: '1',
    }
  },
  methods: {
    callback: function (key) {
      this.activeKey = key
    },
  },
}
</script>
