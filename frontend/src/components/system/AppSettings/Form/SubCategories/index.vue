<template>
  <div>
    <a-form :form="form" @submit="handleSubmit">
      <div class="row">
        <div class="col-lg-4">
          <a-form-item label="Sub Category Name">
            <a-input placeholder="Sub Category Name"
                     v-decorator="['name', { rules: [{ required: true, message: 'This field is required' }] }]" />
          </a-form-item>
        </div>
        <div class="col-lg-4">
          <a-form-item label="Functions">
            <a-select v-decorator="['category_id', { rules: [{ required: true, message: 'This field is required' }] }]"
                      @change="handleSelectChange">
              <a-select-option v-for="func in functions" v-bind:value="func.id" v-bind:key="func.id">
                {{ func.name }}
              </a-select-option>
            </a-select>
          </a-form-item>
        </div>
        <div class="col-lg-4">
          <a-form-item label="Parent Sub Category">
            <a-tree-select
              v-model="parentId"
              style="width: 100%"
              :dropdown-style="{ maxHeight: '400px', overflow: 'auto' }"
              dropdownClassName="treeSelectDropDown"
              :tree-data="parentSubs"
              placeholder="Select Parent Sub Category"
              :replace-fields="normalizer"
              allow-clear
              tree-default-expand-all
            >
            </a-tree-select>
          </a-form-item>
        </div>
      </div>
      <div class="form-actions mt-0">
        <a-button style="width: 150px;" type="primary" htmlType="submit" class="mr-3">Add</a-button>
      </div>
    </a-form>
    <sub-categories-table :sub-category-list="subCategoryList" @delete="onDelete"/>
  </div>
</template>
<script>
import { mapState } from 'vuex'
import SubCategoriesTable from './partials/lists'
import { Modal } from 'ant-design-vue'

export default {
  name: 'SubCategoriesForm',
  components: {
    SubCategoriesTable,
  },
  computed: {
    ...mapState({
      functions: state => state.formSettings.functions,
      subCategoryList: state => state.formSettings.subCategories,
    }),
  },
  created() {
    this.loadSubCategories()
  },
  mounted() {
    this.$store.dispatch('formSettings/FETCH_FUNCTIONS')
  },
  data() {
    return {
      form: this.$form.createForm(this),
      parentSubs: [],
      parentId: null,
      normalizer: {
        title: 'name',
        value: 'id',
      },
    }
  },
  methods: {
    loadSubCategories() {
      this.$store.dispatch('formSettings/FETCH_SUB_CATEGORIES')
    },
    onDelete(key) {
      this.$store.dispatch('formSettings/DELETE_SUB_CATEGORY', { payload: key })
    },
    handleSelectChange(value) {
      this.parentId = null
      this.parentSubs = this.subCategoryList.filter((i) => {
        return i.category_id === value
      })
    },
    handleSubmit(e) {
      const that = this
      e.preventDefault()
      this.form.validateFields((err, values) => {
        if (!err) {
          Modal.confirm({
            title: 'Are you sure you want to create this sub category?',
            content: '',
            onOk() {
              values.parentId = that.parentId || null
              that.$store.dispatch('formSettings/CREATE_SUB_CATEGORY', { payload: values })
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
