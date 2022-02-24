<template>
  <a-table :columns="columns" :data-source="functions" bordered>
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
import { defineComponent, computed, watch, ref, onMounted } from 'vue';
import { useStore } from 'vuex'
import { WarningOutlined } from '@ant-design/icons-vue'

const columns = [
  { title: 'Name', dataIndex: 'name', key: 'name' },
  { title: 'Percentage', dataIndex: 'percentage', key: 'percentage' },
  { title: 'Action', dataIndex: 'operation', slots: { customRender: 'operation' } },
]

export default defineComponent({
  name: 'FunctionsTable',
  components: {
    WarningOutlined,
  },
  props: {
    year: { type: Number, default: new Date().getFullYear() },
  },
  setup(props) {
    const store = useStore()
    const functions = computed(() => store.getters['formManager/manager'].functions)

    // EVENTS
    watch(() => [props.year], ([year]) => {
      fetchFunctions(year)
    })

    onMounted(() => {
      fetchFunctions(props.year)
    })

    // METHODS
    const fetchFunctions = year => {
      store.dispatch('formManager/FETCH_FUNCTIONS', { payload: { year: year, isPrevious: false }})
    }

    const onDelete = key => {
      store.dispatch('formManager/DELETE_FUNCTION', { payload: { id: key, year: props.year }} )
    }

    return {
      columns,

      functions,

      onDelete,
    }
  },
})
</script>
