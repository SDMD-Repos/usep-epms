<template>
  <div>
    <a-modal v-model="visible"
             title="File Upload"
             ok-text="Unpublish"
             :closable="false"
             :confirm-loading="isUploading"
             @ok="onUnpublish"
             @cancel="onClose">
      <p>Unpublishing this requires you to upload the published PDF copy of the form</p>
      <a-upload-dragger
        name="file"
        :file-list="fileList"
        :before-upload="beforeUpload"
        :remove="handleRemove"
        accept=".pdf"
      >
        <p class="ant-upload-drag-icon">
          <a-icon type="cloud-upload" />
        </p>
        <p class="ant-upload-text">
          Click or drag a PDF file to upload
        </p>
        <p class="ant-upload-hint">
          Max file size: 5 MB
        </p>
      </a-upload-dragger>
    </a-modal>
  </div>
</template>
<script>
export default {
  name: 'uploadPublishModal',
  props: {
    isFileUpload: Boolean,
    list: Array,
    uploading: Boolean,
  },
  watch: {
    isFileUpload(val) {
      this.visible = val
    },
    list(val) {
      this.fileList = val
    },
    uploading(val) {
      this.isUploading = val
    },
  },
  data() {
    const isFileUpload = this.isFileUpload
    const list = this.list
    const uploading = this.uploading
    return {
      visible: isFileUpload,
      fileList: list,
      isUploading: uploading,
    }
  },
  methods: {
    beforeUpload(file) {
      const isPdf = file.type === 'application/pdf'
      if (!isPdf) {
        this.$message.error('You can only upload a PDF file!')
      }
      console.log(file.size)
      const isLt5M = file.size / 1024 / 1024 < 5
      if (!isLt5M) {
        this.$message.error('File size must not exceed to 5MB')
      }
      const isListEmpty = this.fileList.length < 1
      if (!isListEmpty) {
        this.$message.error('Only one file is allowed to upload')
      }
      if (isPdf && isLt5M && isListEmpty) {
        this.$emit('add-to-list', file)
        return false
      } else {
        return isPdf && isLt5M && isListEmpty
      }
    },
    handleRemove(file) {
      this.$emit('remove-file', file)
    },
    onClose() {
      this.$emit('cancel-upload')
    },
    onUnpublish() {
      if (this.fileList.length < 1) {
        this.$message.error('Please upload a published copy of the form')
      } else {
        this.$emit('upload')
      }
    },
  },
}
</script>
