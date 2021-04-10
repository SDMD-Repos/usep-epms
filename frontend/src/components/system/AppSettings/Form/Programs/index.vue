<template>
  <div>
    <a-form :form="form">
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
</template>
<script>
import { mapState } from 'vuex';

export default {
  name: 'ProgramsForm',
  computed: {
    ...mapState(['functions']),
    loading() {
      return this.$store.state.formSettings.loading
    },
  },
  data() {
    return {
      form: this.$form.createForm(this),
    }
  },
}
</script>
