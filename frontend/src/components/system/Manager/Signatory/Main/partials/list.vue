<template>
  <div>
    <a-table bordered :data-source="list" :columns="columns" :loading="loading">
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
  </div>
</template>
<script>

const columns = [
  { title: 'Personnel', className: 'column-personnel', dataIndex: 'personnel_name', key: 'personnelName' },
  { title: 'Office/College', className: 'column-office', dataIndex: 'office_name', key: 'officeName' },
  { title: 'Position', className: 'column-position', dataIndex: 'position', key: 'position' },
  { title: 'Action', className: 'column-action', dataIndex: '', key: 'x', scopedSlots: { customRender: 'action' } },
]

export default {
  props: ['list', 'year', 'formId', 'officeId', 'loading'],
  data() {
    return {
      columns,
    }
  },
  methods: {
    onDelete(key) {
      const data = {
        year: this.year,
        formId: this.formId,
        officeId: this.officeId,
        id: key,
      }
      this.$store.dispatch('formManager/DELETE_POSITION_SIGNATORY', { payload: data })
    },
  },
}
</script>

<style lang="scss">
th.column-personnel,
th.column-office,
th.column-action {
  text-align: center !important;
}
</style>
