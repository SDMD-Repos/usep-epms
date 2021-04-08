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
            <a-form :form="form" v-if="activeKey === '1'">
              <div class="row">
                <div class="col-lg-6">
                  <a-form-item label="Program Name">
                    <a-input placeholder="Program Name" v-decorator="['programName']" />
                  </a-form-item>
                </div>
                <div class="col-lg-4">
                  <a-form-item label="Functions">
                    <a-select defaultValue="Red">
                      <a-select-option value="red">Red</a-select-option>
                      <a-select-option value="black">Black</a-select-option>
                      <a-select-option value="cyan">Cyan</a-select-option>
                      <a-select-option value="blue">Blue</a-select-option>
                    </a-select>
                  </a-form-item>
                </div>
                <div class="col-lg-2">
                  <a-form-item label="Percentage">
                    <a-input placeholder="Percentage" v-decorator="['percentage']" />
                  </a-form-item>
                </div>
              </div>
              <div class="form-actions mt-0">
                <a-button style="width: 150px;" type="primary" htmlType="submit" class="mr-3">Submit</a-button>
              </div>
            </a-form>

          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<script>

export default {
  components: {
  },
  data() {
    return {
      activeKey: '1',
      form: this.$form.createForm(this),
    }
  },
  methods: {
    callback: function (key) {
      this.activeKey = key
    },
  },
}
</script>
