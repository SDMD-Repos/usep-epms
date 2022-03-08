<template>
  <div>
    <a-table :columns="columns" :data-source="list" bordered>
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
  </div>
</template>
<script>
import { defineComponent } from 'vue'
import { useStore } from 'vuex'
import { WarningOutlined } from '@ant-design/icons-vue'

const columns = [
  { title: 'Personnel', className: 'column-personnel', dataIndex: 'personnel_name', key: 'personnelName' },
  { title: 'Office/College', className: 'column-office', dataIndex: 'office_name', key: 'officeName' },
  { title: 'Position', className: 'column-position', dataIndex: 'position', key: 'position' },
  { title: 'Action', dataIndex: 'operation', slots: { customRender: 'operation' } },
]

export default defineComponent({
  name: "SignatoryList",
  components: {
    WarningOutlined,
  },
  props:{
    list: {
      type: Array,
      default: () => { return [] },
    },
    year: {
      type: Number,
      default: null,
    },
    formId: {
      type: String,
      default: '',
    },
  },
  setup(props) {
    const store = useStore()

    // METHODS
    const onDelete = (key) => {
      const data = {
        year: props.year,
        formId: props.formId,
        officeId: props.officeId,
        id: key,
      }
      store.dispatch('formManager/DELETE_POSITION_SIGNATORY', { payload: data })
    }
    return {
      columns,

      onDelete,
    }
  },
})
</script>

<style lang="scss">
th.column-personnel,
th.column-office,
th.column-action {
  text-align: center !important;
}
</style>
