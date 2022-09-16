<template>
  <a-table :columns="columns" :data-source="allPrograms" bordered>
    <template #bodyCell="{ column, record }">
      <template v-if="column.key === 'operation'">
        <a-popconfirm
          title="Are you sure you want to delete this?"
          @confirm="onDelete(record.key)"
          ok-text="Yes"
          cancel-text="No"
        >
          <template #icon><warning-outlined /></template>
          <a v-if="record.form_id" type="primary">Delete</a>
        </a-popconfirm>
      </template>
    </template>
  </a-table>
</template>
<script>
import { defineComponent, onMounted } from 'vue';
import { useStore } from 'vuex'
import { WarningOutlined } from '@ant-design/icons-vue'

const columns = [
  { title: 'Name', dataIndex: 'name', key: 'name' },
  { title: 'Function', dataIndex: ['category', 'name'], key: 'function' },
  { title: 'Percentage', dataIndex: 'percentage', key: 'percentage' },
  { title: 'Action', dataIndex: 'operation', key: 'operation' },
]

export default defineComponent({
  name: 'OtherProgramsTable',
  components: {
    WarningOutlined,
  },
  props: {
    year: { type: Number, default: null },
    formId: { type: String, default: null },
    allPrograms: { type: Object, default: () => {} },
  },
  setup(props) {
    const store = useStore()

    onMounted(() => {})

    // METHODS
    const onDelete = key => {
      store.dispatch('formManager/DELETE_PROGRAM', { payload: { id: key, year: props.year, formId: props.formId }})
    }

    return {
      columns,
      onDelete,
    }
  },
})
</script>
