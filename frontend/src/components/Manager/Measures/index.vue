<template>
  <a-table :columns="columns" :data-source="measuresList" bordered>
    <template #title>
      <a-button type="primary" class="mr-3" @click="openModal('create', null)">
        <template #icon><PlusOutlined /></template>
        New Measure
      </a-button>
    </template>

<!--    <template #dateCreated="{ record }">-->
<!--      {{ moment(record.created_at).format(mainStore.dateFormat)}}-->
<!--    </template>-->

    <template #operation="{ record }">
      <a @click="openModal('view', record)">View</a>
      <a-divider type="vertical" />
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
import { computed, defineComponent } from 'vue'
import { WarningOutlined, PlusOutlined } from '@ant-design/icons-vue'

const columns = [
  { title: 'Name', dataIndex: 'name', key: 'name' },
  { title: 'Action', dataIndex: 'operation', slots: { customRender: 'operation' } },
]

export default defineComponent({
  components: {
    WarningOutlined,
    PlusOutlined,
  },
  setup() {
    // COMPUTED
    const measuresList = computed(() => store.getters['formManager/manager'].measures)

    // METHODS
    const openModal = () => {
      console.log('openModal')
    }

    const onDelete = () => {
      console.log('onDelete')
    }

    return {
      columns,

      measuresList,

      openModal,
      onDelete,
    }
  },
})
</script>
