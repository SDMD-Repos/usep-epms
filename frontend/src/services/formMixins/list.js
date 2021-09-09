import { listTableColumns } from '@/services/formColumns'
import moment from 'moment'
import { Modal } from 'ant-design-vue'

export default {
  props: ['formId'],
  data() {
    return {
      visible: false,
      name: '',
      fileName: '',
      listTableColumns,
      config: {
        toolbar: {
          toolbarViewerRight: {
            presentationMode: false,
            viewBookmark: false,
            openFile: false,
            secondaryToolbarToggle: false,
          },
        },
        secondaryToolbar: false,
      },
      // upload modal
      isFileUpload: false,
      fileList: [],
      cachedId: null,
      okTextUploadModal: '',
      noteInModal: '',
      isConfirmDeleteFile: false,
      // uploaded list modal
      isUploadedViewed: false,
      viewedForm: {},
    }
  },
  computed: {
  },
  methods: {
    moment,
    deactivate(id) {
      const self = this
      Modal.confirm({
        title: 'Are you sure you want to deactivate this?',
        content: 'You won\'t be able to revert this!',
        okText: 'Yes',
        cancelText: 'No',
        onOk() {
          const payload = {
            id: id,
          }
          self.$store.dispatch(self.formId + '/DEACTIVATE', { payload: payload })
        },
      })
    },
    handleClose() {
      this.visible = !this.visible
    },

    // For unpublish and upload functions
    onUnpublish(id) {
      const self = this
      Modal.confirm({
        title: 'Are you sure you want to unpublish this?',
        content: 'You won\'t be able to revert this!',
        okText: 'Yes',
        cancelText: 'No',
        onOk() {
          self.okTextUploadModal = 'Unpublish'
          self.cachedId = id
          self.isFileUpload = true
          self.noteInModal = 'Unpublishing this requires you to upload the published PDF copy of the form'
          self.isConfirmDeleteFile = false
        },
      })
    },
    addUploadItem(file) {
      this.fileList = [...this.fileList, file]
    },
    removeFile(file) {
      const index = this.fileList.indexOf(file)
      const newFileList = this.fileList.slice()
      newFileList.splice(index, 1)
      this.fileList = newFileList
    },
    cancelUpload() {
      this.isFileUpload = false
      this.fileList = []
      this.cachedId = null
      if (this.isConfirmDeleteFile) {
        this.isUploadedViewed = true
      }
    },

    // For viewing of uploaded files in a modal
    viewUploadedList(data) {
      const files = [...data.files]
      this.isUploadedViewed = true
      files.forEach(item => {
        item.created_at_disp = moment(item.created_at).format(this.dateFormat)
      })
      data.files = files
      this.viewedForm = data
    },
    onCloseList() {
      this.isUploadedViewed = false
      this.viewedForm = {}
    },
    deleteFile(data) {
      this.cachedId = data.id
      this.isFileUpload = true
      this.okTextUploadModal = 'Confirm Deletion'
      this.noteInModal = 'Deleting this requires you to upload the new published PDF copy of the form to be deleted'
      this.isConfirmDeleteFile = true
      this.isUploadedViewed = false
    },
  },
}
