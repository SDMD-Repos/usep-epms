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
      path: 'lib/pdfjs-2.8.335-dist/web/viewer.html',
      listTableColumns,
      moment,
    }
  },
  computed: {
    getFilePath() {
      if (this.name !== '') {
        return this.path + '?file=' + this.name + '&name=' + this.fileName + '.pdf'
      }
      return this.path
    },
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
