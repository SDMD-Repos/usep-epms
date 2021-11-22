<template>
  <a-table :columns="columns" :data-source="subCategoryList" bordered>
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
import { defineComponent, computed } from 'vue';
import { useStore } from 'vuex'
import { WarningOutlined } from '@ant-design/icons-vue'

const columns = [
  { title: 'Name', dataIndex: 'name', key: 'name' },
  { title: 'Function', dataIndex: 'category.name', key: 'function' },
  { title: 'Action', dataIndex: 'operation', slots: { customRender: 'operation' } },
]

export default defineComponent({
  name: 'ProgramsTable',
  components: {
    WarningOutlined,
  },
  props: {
    subCategoryList: {
      required: true,
      type: Array,
    },
  },
  emits: ['delete'],
  setup(props, { emit } ) {
    const store = useStore()
    const loading = computed( () => store.getters['formManager/formManager'].loading)

    // METHODS
    const onDelete = key => {
      emit('delete', key)
    }

    return {
      columns,
      loading,
      onDelete,
    }
  },
})
</script>