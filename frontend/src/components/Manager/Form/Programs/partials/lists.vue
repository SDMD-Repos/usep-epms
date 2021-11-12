<template>
  <a-table :columns="columns" :data-source="programs" bordered>
    <template #operation="{ record }">
      <a-popconfirm
        title="Are you sure you want to delete this?"
        @confirm="onDelete(record.key)"
        ok-text="Yes"
        cancel-text="No"
      >
        <template #icon><warning-outlined /></template>
        <a type="primary">Delete</a>
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
  name: 'ProgramsTable',
  components: {
    WarningOutlined,
  },
  setup() {
    const store = useStore()
    const programs = computed(() => store.getters['formManager/manager'].programs)

    onMounted(() => {
      store.dispatch('formManager/FETCH_PROGRAMS')
    })

    // METHODS
    const onDelete = key => {
      store.dispatch('formManager/DELETE_PROGRAM', { payload: key })
    }

    return {
      columns,
      programs,
      onDelete,
    }
  },
})
</script>
