<template>
  <a-modal v-model:visible="isVisible" :title="documentName"
           width="50%" wrap-class-name="viewUploadedModal" :footer="null"
           @cancel="onClose">
    <a-table :columns="columns"
             :data-source="list"
             size="small"
             row-key="id">

      <!-- Custom column render-->
      <template #fileName="{ text, record }">
        <a-button type="link" @click="viewFile(record)">{{ text }}</a-button>
      </template>

      <template #operation="{ record }">
        <a-popconfirm
          title="Are you sure you want to delete this file?"
          ok-text="Yes"
          cancel-text="No"
          @confirm="onDelete(record)"
        >
          <a-tooltip>
            <template #title><span>Delete</span></template>
            <DeleteOutlined :style="{fontSize: '18px'}"/>
          </a-tooltip>
        </a-popconfirm>
      </template>
    </a-table>
  </a-modal>
</template>
<script>
import { defineComponent, ref, watch } from 'vue'
import { uploadedListColumns } from '@/services/columns'
import { DeleteOutlined } from "@ant-design/icons-vue"

export default defineComponent({
  components: { DeleteOutlined },
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
      list.value = details.files || []
    })

    const onClose = () => {
      emit('close-list-modal')
    }

    const viewFile = data => {
      emit('view-file', data)
    }

    const onDelete = (data) => {
      emit('delete-file', data)
      emit('close-list-modal')
    }

    return {
      columns: uploadedListColumns,

      isVisible,
      documentName,
      list,

      onClose,
      viewFile,
      onDelete,
    }
  },
})
</script>
