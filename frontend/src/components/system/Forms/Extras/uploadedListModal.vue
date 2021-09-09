<template>
  <div>
    <a-modal v-model="isViewed"
             :title="documentName"
             width="50%"
             wrap-class-name="viewUploadedModal"
             :footer="null"
             @cancel="onClose">
      <a-table :columns="uploadedColumns"
               :data-source="list"
               size="small"
               rowKey="id">

        <!-- Custom column render-->
        <span slot="fileName" slot-scope="text, record">
          <a @click="viewFile(record)">{{ text }}</a>
        </span>

        <span slot="action" slot-scope="text, record">
          <a-popconfirm
            title="Are you sure you want to delete this file?"
            ok-text="Yes"
            cancel-text="No"
            @confirm="onDelete(record)"
          >
            <a-tooltip>
              <template slot="title"><span>Delete</span></template>
              <a-icon type="delete" :style="{fontSize: '18px'}" />
            </a-tooltip>
          </a-popconfirm>
        </span>
      </a-table>
    </a-modal>
  </div>
</template>
<script>

const uploadedColumns = [
  {
    title: 'File Name',
    dataIndex: 'file_name',
    width: 350,
    scopedSlots: { customRender: 'fileName' },
  },
  {
    title: 'Date & Time Uploaded',
    dataIndex: 'created_at_disp',
    className: 'column-created-at',
  },
  {
    title: '',
    key: 'operation',
    className: 'column-action',
    scopedSlots: { customRender: 'action' },
  },
]

export default {
  props: {
    uploadModalState: Boolean,
    dateFormat: String,
    formDetails: Object,
  },
  watch: {
    uploadModalState(val) {
      this.isViewed = val
    },
    formDetails(val) {
      this.list = val.files || []
      this.documentName = val.document_name || val.office_name || null
    },
  },
  data() {
    const uploadModalState = this.uploadModalState
    const formDetails = this.formDetails || []
    return {
      isViewed: uploadModalState,
      list: formDetails.files || [],
      documentName: formDetails.document_name || null,
      uploadedColumns,
    }
  },
  methods: {
    onClose() {
      this.$emit('close-list-modal')
    },
    viewFile(data) {
      this.$emit('view-file', data)
    },
    onDelete(data) {
      this.$emit('delete-file', data)
    },
  },
}
</script>
