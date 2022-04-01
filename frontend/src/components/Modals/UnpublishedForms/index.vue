<template>
  <a-modal v-model:visible="isVisible" :title="documentName"
           width="50%" wrap-class-name="viewUploadedModal" :footer="null"
           @cancel="onClose">
    <a-table :columns="columns"
             :data-source="list"
             size="small"
             row-key="id">

      <!-- Custom column render-->
      <template #documentName="{ text, record }">
        <a-tooltip title="View PDF">
          <a-button type="link" @click="viewFile(record)">{{ text }}</a-button>
        </a-tooltip>
      </template>

      <template #details="{ record }">
        Unpublished {{ record.changed_date_disp }} by {{ record.requested_by }}
      </template>
    </a-table>
  </a-modal>
</template>
<script>
import { defineComponent, ref, watch } from 'vue'
import { unplishedFormsColumns } from '@/services/columns'
// import { DeleteOutlined } from "@ant-design/icons-vue"

export default defineComponent({
  name: 'UnpublishedFormsModal',
  // components: { DeleteOutlined },
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
      list.value = details.status.filter(i => i.status === 'verified') || []
    })

    const onClose = () => {
      emit('close-list-modal')
    }

    const viewFile = data => {
      emit('view-file', data)
    }

    /*const onDelete = (data) => {
      emit('delete-file', data)
      emit('close-list-modal')
    }*/

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
