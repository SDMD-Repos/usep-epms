<template>
  <a-table :columns="columns" :data-source="otherPrograms" bordered>
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
  name: 'OtherProgramsTable',
  components: {
    WarningOutlined,
  },
  props: {
    year: { type: Number, default: null },
    form_id: { type: String, default: null },
  },

  setup(props) {
    const store = useStore()
    const otherPrograms = computed(() => { return store.getters['formManager/manager'].otherPrograms.map(x => Object.assign({}, x, { "is_other": true }))})
    const programs = computed(() => { return store.getters['formManager/manager'].programs.map(x => Object.assign({}, x, { "is_other": true }))})

    const mergePrograms = computed(() => {
      return otherPrograms.value.concat(programs)
    })

    onMounted(() => {
      store.dispatch('formManager/FETCH_OTHER_PROGRAMS', { payload : { form_id:props.form_id, year: props.year, isPrevious: false }})
      store.dispatch('formManager/FETCH_PROGRAMS', { payload : { year: props.year, isPrevious: false }})
    })

    // METHODS
    const onDelete = key => {
      store.dispatch('formManager/DELETE_OTHER_PROGRAM', { payload: { form_id: props.form_id, id: key, year: props.year }})
    }

    return {
      columns,
      otherPrograms,
      onDelete,
      mergePrograms,
      programs,
    }
  },
})
</script>
