<template>
  <div>
    <div class="row">
      <div class="col-xl-12 col-lg-12">
        <div class="card">
          <div class="card-header card-header-flex flex-column">
            <a-tabs defaultActiveKey="1" class="kit-tabs kit-tabs-bold" @change="callback">
              <a-tab-pane tab="Programs" key="1" />
              <a-tab-pane tab="Sub Categories" key="2" />
            </a-tabs>
          </div>
          <div class="card-body">
            <programs-form v-if="activeKey === '1'" />
            <sub-categories-form v-if="activeKey === '2'" />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
import ProgramsForm from '@/components/system/AppSettings/Form/Programs'
import SubCategoriesForm from '@/components/system/AppSettings/Form/SubCategories'
export default {
  components: {
    ProgramsForm,
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
