<template>
  <a-table :columns="columns" :data-source="allPrograms" bordered>
    <template #operation="{ record }">
      <a-popconfirm
        title="Are you sure you want to delete this?"
        @confirm="onDelete(record.key)"
        ok-text="Yes"
        cancel-text="No"
      >
        <template #icon><warning-outlined /></template>
        <a v-if="record.is_other" type="primary">Delete</a>
      </a-popconfirm>
    </template>
  </a-table>
</template>
<script>
import { defineComponent, computed, onMounted } from 'vue';
import { useStore } from 'vuex'
import { WarningOutlined } from '@ant-design/icons-vue'

const columns = [
  { title: 'Name', dataIndex: 'name', key: 'name' },
  { title: 'Function', dataIndex: 'category.name', key: 'function' },
  { title: 'Percentage', dataIndex: 'percentage', key: 'percentage' },
  { title: 'Action', dataIndex: 'operation', slots: { customRender: 'operation' } },
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
      store.dispatch('formManager/DELETE_OTHER_PROGRAM', { payload: { formId: props.formId, id: key, year: props.year }})
    }

    return {
      columns,
      onDelete,
    }
  },
})
</script>
