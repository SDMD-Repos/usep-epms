<template>
  <div>
    <a-form :form="form" @submit="handleSubmit">
      <div class="row">
        <div class="col-lg-5">
          <a-form-item label="Function Name">
            <a-input v-decorator="['name', { rules: [{ required: true, message: 'This field is required' }] }]" />
          </a-form-item>
        </div>
        <div class="col-lg-2">
          <a-form-item label="Percentage">
            <a-input-number placeholder="%" :min="1" :max="100"
                            v-decorator="['percentage', { rules: [{ required: true, message: 'This field is required' }] }]"/>
          </a-form-item>
        </div>
        <div class="col-lg-1 mt-lg-5 ml-lg-n5">
          <a-button type="primary" htmlType="submit" class="mr-3">Add</a-button>
        </div>
      </div>
    </a-form>
    <br />
    <categories-table />
  </div>
</template>
<script>
import { mapState } from 'vuex'
import CategoriesTable from './partials/list'
import { Modal } from 'ant-design-vue'

export default {
  components: {
    CategoriesTable,
  },
  computed: {
    ...mapState({
      loading: state => state.formManager.loading,
    }),
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
            title: 'Are you sure you want to create this function?',
            content: '',
            onOk() {
              console.log(values)
              that.$store.dispatch('formManager/CREATE_FUNCTION', { payload: values })
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
