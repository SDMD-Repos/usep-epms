<template>
  <a-table :columns="columns" :data-source="functions" bordered>
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
import { defineComponent, watch, onMounted, computed } from 'vue';
import { useStore } from 'vuex'
import { WarningOutlined } from '@ant-design/icons-vue'

const columns = [
  { title: 'Name', dataIndex: 'name', key: 'name' },
  { title: 'Percentage', dataIndex: 'percentage', key: 'percentage' },
  { title: 'Action', dataIndex: 'operation', key: 'operation' },
]

export default defineComponent({
  name: 'FunctionsTable',
  components: {
    WarningOutlined,
  },
  props: {
    year: { type: Number, default: new Date().getFullYear() },
    isDelete : Boolean,
    allAccess : Boolean,
  },
  setup(props) {
    const store = useStore()
    const functions = computed(() => store.getters['formManager/functions'])

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
      store.dispatch('formManager/FETCH_FUNCTIONS', { payload: { year: (year - 1), isPrevious: true }})
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
