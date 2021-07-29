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
      moment,
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
    }
  },
  computed: {
  },
  methods: {
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
  },
}
