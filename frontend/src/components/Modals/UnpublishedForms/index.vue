<template>
  <a-modal v-model:visible="isVisible" :title="documentName"
           width="60%" wrap-class-name="viewUploadedModal" :footer="null"
           @cancel="onClose">

    <a-table :columns="columns" :data-source="list" size="small" row-key="id" bordered>

      <!-- Custom column render-->
      <template #bodyCell="{ column, record, index }">
        <template v-if="column.key === 'count'">
          {{ index + 1 }}
        </template>

        <template v-if="column.key === 'details'">
          Unpublished {{ record.changed_date_disp }} by {{ record.requested_by }}
        </template>

        <template v-if="column.key === 'operation'">
          <a-tooltip title="View PDF">
            <a-button type="link" @click="viewFile(record)">View</a-button>
          </a-tooltip>
        </template>
      </template>
    </a-table>
  </a-modal>
</template>
<script>
import { defineComponent, ref, watch } from 'vue'

const unplishedFormsColumns = [
  {
    title: '#',
    width: 50,
    key: 'count',
  },
  {
    title: 'Reason',
    dataIndex: 'remarks',
    key: 'remarks',
  },
  {
    title: 'Details',
    key: 'details',
  },
  {
    title: 'Action',
    width: 50,
    key: 'operation',
  },
]

export default defineComponent({
  name: 'UnpublishedFormsModal',
  props: {
    modalState: Boolean,
    formDetails: { type: Object, default: () => {} },
  },
  emits: ['close-list-modal', 'view-file', 'delete-file'],
  setup(props, { emit }) {
    const isVisible = ref(false)
    const documentName = ref(null)
    const list = ref([])

    watch(() => [props.modalState, props.formDetails], ([visible, details]) => {
      isVisible.value = visible
      documentName.value = details.document_name || details.office_name || null
      list.value = typeof details.status !== 'undefined' ? details.status.filter(i => i.status === 'verified') : []
    })

    const onClose = () => {
      emit('close-list-modal')
    }

    const viewFile = data => {
      emit('view-file', { data: data, fromUnpublished: true })
    }

    return {
      columns: unplishedFormsColumns,

      isVisible,
      documentName,
      list,

      onClose,
      viewFile,
      // onDelete,
    }
  },
})
</script>
