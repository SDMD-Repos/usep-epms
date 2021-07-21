<template>
  <div class="row">
    <div class="col-xl-12 col-lg-12">
      <a-table
        :columns="columns"
        :data-source="filteredDataSource"
        bordered
        size="middle"
        :scroll="{ x: 'calc(2600px + 50%)', y: 600 }"
      >
      </a-table>
    </div>
  </div>
</template>
<script>
import ItemMixin from '@/services/formMixins/item'

export default {
  name: 'list-item',
  mixins: [ItemMixin],
  data() {
    return {
      columns: [],
      filteredDataSource: [],
    }
  },
  created() {
    this.initializeColumns()
  },
  methods: {
    initializeColumns() {
      let { columns } = this
      const remarksIndex = this.getFormColumns.findIndex(i => i.key === 'otherRemarks')
      columns = [...this.getFormColumns]
      columns.splice(remarksIndex, 0, {
        title: 'Remarks',
        key: 'remarks',
        dataIndex: 'remarks',
        className: 'column-other-remarks',
        width: 200,
        ellipsis: true,
      })
      const deleteKeys = ['subCategory', 'cascadingLevel', 'otherRemarks']
      columns = [...columns.filter(i => deleteKeys.indexOf(i.key) === -1)]
      const addtl = {
        title: '#',
        key: 'count',
        className: 'column-count',
        width: 60,
        scopedSlots: { customRender: 'count' },
      }
      columns.splice(0, 0, addtl)
      this.columns = columns
    },
  },
}
</script>
