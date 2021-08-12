<template>
  <a-table :columns="columns" :dataSource="subCategoryList" :loading="loading">
    <template slot="action" slot-scope="text, record">
      <a-popconfirm
        title="Are you sure you want to delete this?"
        @confirm="onDelete(record.key)"
        okText="Yes"
        cancelText="No"
      >
        <a type="primary">Delete</a>
        <a-icon slot="icon" type="warning"/>
      </a-popconfirm>
    </template>
  </a-table>
</template>
<script>

const columns = [
  { title: 'Name', dataIndex: 'name', key: 'name' },
  { title: 'Function', dataIndex: 'category.name', key: 'function' },
  { title: 'Action', dataIndex: '', key: 'x', scopedSlots: { customRender: 'action' } },
]

export default {
  name: 'ProgramsTable',
  props: {
    subCategoryList: {
      required: true,
    },
  },
  computed: {
    loading() {
      return this.$store.state.formManager.loading
    },
  },
  data() {
    return {
      columns,
    }
  },
  methods: {
    loadSubCategories() {
      this.$store.dispatch('formManager/FETCH_SUB_CATEGORIES')
    },
    onDelete(key) {
      this.$emit('delete', key)
    },
  },
}
</script>
