<template>
  <a-table :columns="columns" :dataSource="categoryList" :loading="loading">
    <template slot="action" slot-scope="text, record">
      <a-popconfirm
        title="Are you sure you want to delete this?"
        @confirm="onDelete(record.key)"
        okText="Yes"
        cancelText="No"
      >
        <a type="primary">Delete</a>
      </a-popconfirm>
    </template>
  </a-table>
</template>
<script>
import { mapState } from 'vuex'

const columns = [
  { title: 'Name', dataIndex: 'name', key: 'name' },
  { title: 'Percentage', dataIndex: 'percentage', key: 'percentage' },
  { title: 'Action', dataIndex: '', key: 'x', scopedSlots: { customRender: 'action' } },
]

export default {
  name: 'ProgramsTable',
  computed: {
    ...mapState({
      categoryList: state => state.formManager.functions,
    }),
    loading() {
      return this.$store.state.formManager.loading
    },
  },
  created() {
    this.onLoad()
  },
  data() {
    return {
      columns,
    }
  },
  methods: {
    onLoad() {
      this.$store.dispatch('formManager/FETCH_FUNCTIONS')
    },
    onDelete(key) {
      this.$store.dispatch('formManager/DELETE_FUNCTION', { payload: key })
    },
  },
}
</script>
