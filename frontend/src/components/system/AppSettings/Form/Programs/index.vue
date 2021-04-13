<template>
  <div>
    <a-form :form="form" @submit="handleSubmit">
      <div class="row">
        <div class="col-lg-6">
          <a-form-item label="Program Name">
            <a-input placeholder="Program Name" v-decorator="['name', { rules: [{ required: true, message: 'This field is required' }] }]" />
          </a-form-item>
        </div>
        <div class="col-lg-4">
          <a-form-item label="Functions">
            <a-select v-decorator="['category_id', { rules: [{ required: true, message: 'This field is required' }] }]">
              <a-select-option v-for="func in functions" v-bind:value="func.id" v-bind:key="func.id">{{ func.name }}</a-select-option>
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
        <a-button style="width: 150px;" type="primary" htmlType="submit" class="mr-3">Add</a-button>
      </div>
    </a-form>
    <programs-table />
  </div>
</template>
<script>
import { mapState } from 'vuex'
import ProgramsTable from './partials/lists'
import { Modal } from 'ant-design-vue'

export default {
  name: 'ProgramsForm',
  components: {
    ProgramsTable,
  },
  computed: {
    ...mapState({
      functions: state => state.formSettings.functions,
    }),
  },
  mounted() {
    this.$store.dispatch('formSettings/FETCH_FUNCTIONS')
  },
  data() {
    return {
      form: this.$form.createForm(this),
    }
  },
  methods: {
    handleSubmit(e) {
      const that = this
      e.preventDefault()
      this.form.validateFields((err, values) => {
        if (!err) {
          Modal.confirm({
            title: 'Are you sure you want to create this program?',
            content: '',
            onOk() {
              console.log(values)
              that.$store.dispatch('formSettings/CREATE_PROGRAM', { payload: values })
              that.form.resetFields()
            },
            onCancel() {},
          })
        }
      })
    },
  },
}
</script>
