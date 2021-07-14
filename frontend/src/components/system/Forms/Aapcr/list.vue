<template>
  <div>
    <div class="mb-5">
      <a-breadcrumb>
        <a-breadcrumb-item>Home</a-breadcrumb-item>
        <a-breadcrumb-item>AAPCR List</a-breadcrumb-item>
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
        <a-table :columns="listTableColumns"
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
              <a-icon type="file-pdf" :style="{fontSize: '18px'}" @click="viewPdf(record.id, record.document_name)"/>
            </a-tooltip>
            <template v-if="record.finalized_date && !record.published_date && record.is_active">
              <a-divider type="vertical" />
              <a-tooltip>
                <template slot="title"><span>Publish</span></template>
                  <a-icon type="file-done" :style="{fontSize: '18px'}" @click="handlePublish(record.id, record.year)"/>
                </a-tooltip>
            </template>
          </span>
        </a-table>
      </div>
    </div>
<!--    <a-modal v-model="visible"-->
<!--             width="100%"-->
<!--             :dialog-style="{ top: '20px' }"-->
<!--             :body-style="{ 'height': '900px' }"-->
<!--             :footer="null"-->
<!--             :title="fileName"-->
<!--             @cancel="handleClose">-->
<!--      <iframe height="100%" width=100% :src="`${getFilePath}`" ></iframe>-->
<!--    </a-modal>-->
  </div>
</template>

<script>
import { mapState } from 'vuex'
import { Modal } from 'ant-design-vue'
import * as apiForm from '@/services/mainForms/aapcr'
import ListMixin from '@/services/formMixins/list'

export default {
  title: 'AAPCR List',
  name: 'aapcr-list',
  mixins: [ListMixin],
  computed: {
    ...mapState({
      list: state => state.aapcr.list,
      loading: state => state.aapcr.loading,
      dateFormat: state => state.dateFormat,
    }),
  },
  created() {
    this.onLoad()
  },
  data() {
    return {
    }
  },
  methods: {
    onLoad() {
      this.$store.dispatch('aapcr/FETCH_LIST')
    },
    handleUpdate(id) {
      this.$router.push({
        name: 'main.form',
        params: { formId: this.formId, id: id },
      })
    },
    viewPdf(id, documentName) {
      const self = this
      this.$store.commit('aapcr/SET_STATE', {
        loading: true,
      })
      const fetchPdfData = apiForm.fetchPdfData
      fetchPdfData(id, documentName).then(response => {
        if (response) {
          self.visible = true
          const blob = new Blob([response], { type: 'application/pdf' })
          self.name = window.URL.createObjectURL(blob)
          self.fileName = documentName
          window.open(self.getFilePath, '_blank')
        }
        this.$store.commit('aapcr/SET_STATE', {
          loading: false,
        })
      })
    },
    handlePublish(id, year) {
      const self = this
      Modal.confirm({
        title: 'Are you sure you want to publish this?',
        content: 'You won\'t be able to revert this!',
        okText: 'Yes',
        cancelText: 'No',
        onOk() {
          const payload = {
            id: id,
            year: year,
          }
          self.$store.dispatch('aapcr/PUBLISH', { payload: payload })
        },
      })
    },
  },
  components: {
  },
}
</script>
<style lang="scss">
@import "@/components/system/Forms/style.module.scss";
</style>
