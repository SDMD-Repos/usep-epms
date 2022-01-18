<template>
  <a-modal v-model:visible="isVisible" title="File Upload"
           :ok-text="okPublishText" :closable="false" :confirm-loading="isUploading"
           @ok="onOkClick" @cancel="onClose">
    <p>{{ modalNote }}</p>
    <a-upload-dragger
      :file-list="fileList"
      :before-upload="beforeUpload"
      :remove="handleRemove"
      name="file"
      accept=".pdf"
    >
      <p class="ant-upload-drag-icon">
        <cloud-upload-outlined />
      </p>
      <p class="ant-upload-text">Click or drag a PDF file to upload</p>
      <p class="ant-upload-hint">
        Max file size: 5 MB
      </p>
    </a-upload-dragger>
  </a-modal>
</template>
<script>
import { defineComponent, ref, watch } from 'vue'
import { CloudUploadOutlined } from '@ant-design/icons-vue'
import { message } from 'ant-design-vue'

export default defineComponent({
  name: "UploadPublishModal",
  components: {
    CloudUploadOutlined,
  },
  props: {
    isUploadOpen: Boolean,
    okPublishText: {
      type: String,
      default: "",
    },
    modalNote: {
      type: String,
      default: "",
    },
    list: {
      type: Array,
      default: () => [],
    },
    isUploading: Boolean,
  },
  emits: ['add-to-list', 'remove-file', 'upload', 'cancel-upload'],
  setup(props, { emit }) {
    let isVisible = ref()
    const fileList = ref([])

    // EVENTS
    watch(() => [props.isUploadOpen, props.list] , ([isOpen, list]) => {
      isVisible.value = isOpen
      fileList.value = list
    })

    // METHODS
    const beforeUpload = file => {
      const isPdf = file.type === 'application/pdf'
      if (!isPdf) {
        message.error('You can only upload a PDF file!')
      }
      const isLt5M = file.size / 1024 / 1024 < 5
      if (!isLt5M) {
        message.error('File size must not exceed to 5MB')
      }
      const isListEmpty = fileList.value.length < 1
      if (!isListEmpty) {
        message.error('Only one file is allowed to upload')
      }

      if (isPdf && isLt5M && isListEmpty) {
        emit('add-to-list', file)
        return false
      }else {
        // return isPdf && isLt5M && isListEmpty
        return true
      }
    }

    const handleRemove = file => {
      emit('remove-file', file)
    }

    const onOkClick = () => {
      if (fileList.value.length < 1) {
        message.error('No file was selected')
      } else {
        emit('upload')
      }
    }

    const onClose = () => {
      emit('cancel-upload')
    }

    return {
      isVisible,
      fileList,

      beforeUpload,
      handleRemove,
      onOkClick,
      onClose,
    }
  },
})
</script>
