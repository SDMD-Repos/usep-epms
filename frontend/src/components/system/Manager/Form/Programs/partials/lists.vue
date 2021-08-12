<template>
  <a-table :columns="columns" :dataSource="programList" :loading="loading">
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
  { title: 'Function', dataIndex: 'category.name', key: 'function' },
  { title: 'Percentage', dataIndex: 'percentage', key: 'percentage' },
  { title: 'Action', dataIndex: '', key: 'x', scopedSlots: { customRender: 'action' } },
]

export default {
  name: 'ProgramsTable',
  computed: {
    ...mapState({
      programList: state => state.formManager.programs,
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
      this.$store.dispatch('formManager/FETCH_PROGRAMS')
    },
    onDelete(key) {
      this.$store.dispatch('formManager/DELETE_PROGRAM', { payload: key })
    },
  },
}
</script>
