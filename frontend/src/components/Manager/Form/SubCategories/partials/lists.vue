<template>
  <a-table :columns="columns" :data-source="subCategoryList" bordered>
    <template #bodyCell="{ column, record }">
      <template v-if="column.key === 'operation'">
        <a-popconfirm
          title="Are you sure you want to delete this?"
          @confirm="onDelete(record.key)"
          ok-text="Yes"
          cancel-text="No"
          v-if="isDelete"
        >
          <template #icon><warning-outlined /></template>
          <a type="primary">Delete</a>
        </a-popconfirm>
      </template>
    </template>
  </a-table>
</template>
<script>
import { defineComponent, computed } from 'vue';
import { useStore } from 'vuex'
import { WarningOutlined } from '@ant-design/icons-vue'

const columns = [
  { title: 'Name', dataIndex: 'name', key: 'name' },
  { title: 'Function', dataIndex: ['category', 'name'], key: 'function' },
  { title: 'Ordering', dataIndex: 'ordering', key: 'ordering' },
  { title: 'Action', dataIndex: 'operation', key: 'operation' },
]

export default defineComponent({
  name: 'SubCategoriesTable',
  components: {
    WarningOutlined,
  },
  props: {
    subCategoryList: {
      required: true,
      type: Array,
    },
    isDelete : Boolean,
    allAccess: Boolean,
  },
  emits: ['delete'],
  setup(props, { emit } ) {
    const store = useStore()
    const loading = computed( () => store.getters['formManager/manager'].loading)

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
