<template>
  <div>
    <div class="mb-5">
      <a-breadcrumb>
        <a-breadcrumb-item>Home</a-breadcrumb-item>
        <a-breadcrumb-item>OPCR (VP) List</a-breadcrumb-item>
      </a-breadcrumb>
    </div>
    <div class="card">
      <div class="card-header card-header-flex">
        <div class="d-flex flex-column justify-content-center mr-auto">
        </div>
        <div class="d-flex flex-column justify-content-center">
        </div>
      </div>
      <div class="card-body">
        <a-table :columns="columns"
                 :dataSource="list"
                 :loading="loading"
                 :scroll="{ x: 'calc(50px + 50%)' }">
          <!-- Custom data row render-->

          <span slot="dateCreated" slot-scope="text, record">
            {{ moment(record.created_at).format(dateFormat) }}
          </span>

          <span slot="datePublished" slot-scope="text, record">
            {{ record.published_date ? moment(record.published_date).format(dateFormat) : 'Unpublished' }}
          </span>

          <template slot="status" slot-scope="text, record">
            <div v-if="record.is_active" class="font-size-12 badge badge-success" :style="{cursor: 'pointer'}" @click="deactivate(record.id)">
              <a-tooltip>
                <template slot="title"><span>Click to deactivate</span></template>
                Active
              </a-tooltip>
            </div>
            <span v-else class="font-size-12 badge badge-primary">
              Inactive
            </span>
          </template>

          <span slot="action" slot-scope="text, record">
            <template v-if="record.is_active && !record.published_date">
              <a-tooltip>
                <template slot="title"><span>Update</span></template>
                <a-icon type="edit" :style="{fontSize: '18px'}" @click="handleUpdate(record.id)" />
              </a-tooltip>
              <a-divider type="vertical" />
            </template>
            <a-tooltip>
              <template slot="title"><span>View PDF</span></template>
              <a-icon type="file-pdf" :style="{fontSize: '18px'}" @click="viewPdf(record.id, record.office_name)"/>
            </a-tooltip>
            <template v-if="record.finalized_date && !record.published_date && record.is_active">
              <a-divider type="vertical" />
              <a-tooltip>
                <template slot="title"><span>Publish</span></template>
                  <a-icon type="file-done" :style="{fontSize: '18px'}" @click="handlePublish(record)"/>
                </a-tooltip>
            </template>
            <template v-if="record.published_date && record.is_active">
              <a-divider type="vertical" />
              <a-tooltip>
                <template slot="title"><span>Unpublish</span></template>
                <a-icon type="close-square" theme="filled" :style="{fontSize: '18px'}" @click="onUnpublish(record.id)"/>
              </a-tooltip>
            </template>
            <template v-if="record.files.length">
              <a-divider type="vertical" />
              <a-tooltip>
                <template slot="title"><span>View Archived</span></template>
                <a-icon type="unordered-list" :style="{fontSize: '18px'}" @click="viewUploadedList(record)"/>
              </a-tooltip>
            </template>
          </span>
        </a-table>
      </div>
    </div>

    <!-- View PDF Modal-->
    <a-modal v-model="visible"
             width="100%"
             :dialog-style="{ top: '0px' }"
             :body-style="{ 'height': '100vh', 'padding': '0px 0px 10px 0px',}"
             :footer="null"
             wrap-class-name="pdfModal"
             :title="fileName"
             @cancel="handleClose">
      <vue-pdf-app :pdf="name" theme="light" :file-name="fileName" :config="config"></vue-pdf-app>
    </a-modal>

    <!-- Upload File Modal -->
    <upload-publish-modal :is-file-upload="isFileUpload"
                          :list="fileList"
                          :uploading="loading"
                          :upload-ok-text="okTextUploadModal"
                          :modal-note="noteInModal"
                          @upload="uploadFile"
                          @add-to-list="addUploadItem"
                          @cancel-upload="cancelUpload"
                          @remove-file="removeFile"/>

    <!-- View Uploaded Files Modal -->
    <uploaded-list-modal :upload-modal-state="isUploadedViewed"
                         :date-format="dateFormat"
                         :form-details="viewedForm"
                         @close-list-modal="onCloseList"
                         @view-file="openFile" @delete-file="deleteFile"/>
  </div>
</template>

<script>
import { mapState } from 'vuex'
import { Modal } from 'ant-design-vue'
import ListMixin from '@/services/formMixins/list'
import * as opcrvpForm from '@/services/mainForms/opcrvp'
import VuePdfApp from 'vue-pdf-app'
import 'vue-pdf-app/dist/icons/main.css'
import uploadPublishModal from '../Extras/uploadPublishModal'
import uploadedListModal from '../Extras/uploadedListModal'

export default {
  title: 'OPCR (VP) List',
  name: 'vp-opcr-list',
  mixins: [ListMixin],
  computed: {
    ...mapState({
      list: state => state.opcrvp.list,
      loading: state => state.opcrvp.loading,
      dateFormat: state => state.dateFormat,
    }),
  },
  date() {
    return {
      columns: [],
    }
  },
  created() {
    this.renderColumns()
    this.onLoad()
  },
  methods: {
    renderColumns() {
      let { columns } = this
      const index = this.listTableColumns.findIndex(i => i.key === 'documentName')
      columns = [...this.listTableColumns]
      columns.splice(index, 0, {
        title: 'Office Name',
        key: 'officeName',
        dataIndex: 'office_name',
        className: 'column-document-name',
        width: 250,
      })
      columns = [...columns.filter(i => i.key !== 'documentName')]
      this.columns = columns
    },
    onLoad() {
      this.$store.dispatch('opcrvp/FETCH_LIST')
    },
    handleUpdate(id) {
      this.$router.push({
        name: 'main.form',
        params: { formId: this.formId, id: id },
      })
    },
    viewPdf(id, officeName) {
      const self = this
      this.$store.commit('opcrvp/SET_STATE', {
        loading: true,
      })
      const renderPdf = opcrvpForm.renderPdf
      renderPdf(id).then(response => {
        if (response) {
          self.visible = true
          const blob = new Blob([response], { type: 'application/pdf' })
          self.name = window.URL.createObjectURL(blob)
          self.fileName = officeName + ' - OPCR'
        }
        this.$store.commit('opcrvp/SET_STATE', {
          loading: false,
        })
      })
    },
    handlePublish(data) {
      const self = this
      Modal.confirm({
        title: 'Are you sure you want to publish this?',
        content: 'You won\'t be able to revert this!',
        okText: 'Yes',
        cancelText: 'No',
        onOk() {
          const payload = {
            id: data.id,
            year: data.year,
            officeId: data.office_id,
          }
          self.$store.dispatch('opcrvp/PUBLISH', { payload: payload })
        },
      })
    },
    uploadFile() {
      const { fileList } = this
      const formData = new FormData()
      fileList.forEach(file => {
        formData.append('files[]', file)
      })
      formData.append('id', this.cachedId)
      const self = this
      if (!this.isConfirmDeleteFile) {
        this.$store.dispatch('opcrvp/UNPUBLISH', { payload: formData }).then(() => {
          self.cancelUpload()
        })
      } else {
        this.$store.commit('opcrvp/SET_STATE', {
          loading: true,
        })
        self.cancelUpload()
        self.onCloseList()
        const updateFile = opcrvpForm.updateFile
        updateFile(formData).then(response => {
          if (response) {
            self.$store.dispatch('opcrvp/FETCH_LIST').then(() => {
              const { data } = response
              self.viewUploadedList(data) // open List of Uploaded Files Modal
            })
            self.$notification.success({
              message: 'Success',
              description: 'File was deleted successfully',
            })
          }
          this.$store.commit('opcrvp/SET_STATE', {
            loading: false,
          })
        })
      }
    },
    openFile(data) {
      const self = this
      this.$message.loading('Loading...')
      const { id } = data
      const name = data.file_name
      const viewUploaded = opcrvpForm.viewUploadedFile
      viewUploaded(id).then(response => {
        if (response) {
          self.visible = true
          const blob = new Blob([response], { type: 'application/pdf' })
          self.name = window.URL.createObjectURL(blob)
          self.fileName = name
        }
        self.$message.destroy()
      })
    },
  },
  components: {
    VuePdfApp,
    uploadPublishModal,
    uploadedListModal,
  },
}
</script>
<style scoped>
.pdf-app.light {
  --pdf-toolbar-color: #782f32;
  --pdf-button-hover-font-color: #b79798;
  --pdf-input-color: #865f61;
  --pdf-button-toggled-color: #97595c;
  --pdf-thumbnail-selection-ring-color: #5f4244;
  --pdf-thumbnail-selection-ring-selected-color: #725859;
}
</style>
<style lang="scss">
@import "@/components/system/Forms/style.module.scss";
</style>
