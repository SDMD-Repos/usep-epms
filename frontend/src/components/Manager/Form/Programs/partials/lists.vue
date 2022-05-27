<template>
  <a-table :columns="columns" :data-source="programs" bordered>
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
import { defineComponent, computed, onMounted } from 'vue';
import { useStore } from 'vuex'
import { WarningOutlined } from '@ant-design/icons-vue'

const columns = [
  { title: 'Name', dataIndex: 'name', key: 'name' },
  { title: 'Function', dataIndex: 'category.name', key: 'function' },
  { title: 'Percentage', dataIndex: 'percentage', key: 'percentage' },
  { title: 'Action', dataIndex: 'operation', key: 'operation' },
]

export default defineComponent({
  name: 'ProgramsTable',
  components: {
    WarningOutlined,
  },
  props: {
    year: { type: Number, default: null },
    isDelete : Boolean,
    allAccess : Boolean,
  },

  setup(props) {
    const store = useStore()
    const programs = computed(() => store.getters['formManager/manager'].programs)

    // METHODS
    const onDelete = key => {
      store.dispatch('formManager/DELETE_PROGRAM', { payload: { id: key, year: props.year }})
    }

    return {
      columns,
      programs,
      onDelete,
    }
  },
})
</script>
