<template>
  <div>
    <div v-if="!createNew">
      <a-button type="primary" @click="createNew = !createNew" icon="plus">New Function</a-button>
      <br />
    </div>
    <a-form :form="form" @submit="handleSubmit" v-if="createNew">
      <div class="row">
        <div class="col-lg-6">
          <a-form-item label="Category Name">
            <a-input v-decorator="['name', { rules: [{ required: true, message: 'This field is required' }] }]" />
          </a-form-item>
        </div>
        <div class="col-lg-2">
          <a-form-item label="Percentage">
            <a-input-number placeholder="%" :min="1" :max="100"
                            v-decorator="['percentage', { rules: [{ required: true, message: 'This field is required' }] }]"/>
          </a-form-item>
        </div>
      </div>
      <div class="form-actions mt-0">
        <a-button type="primary" htmlType="submit" class="mr-3">Create</a-button>
        <a-button class="mr-3" @click="createNew = !createNew">Cancel</a-button>
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
      loading: state => state.formSettings.loading,
    }),
  },
  data() {
    return {
      form: this.$form.createForm(this),
      createNew: false,
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
              that.$store.dispatch('formSettings/CREATE_FUNCTION', { payload: values })
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
