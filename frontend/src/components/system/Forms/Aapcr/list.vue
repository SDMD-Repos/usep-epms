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
                 :dataSource="aapcrList"
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
  </div>
</template>

<script>
import { listTableColumns } from '@/services/formColumns'
import { mapState } from 'vuex'
import moment from 'moment'
import { Modal } from 'ant-design-vue'
import * as apiForm from '@/services/mainForms/aapcr'

export default {
  name: 'aapcr-list',
  computed: {
    ...mapState({
      aapcrList: state => state.aapcr.aapcrList,
      loading: state => state.aapcr.loading,
      dateFormat: state => state.dateFormat,
    }),
  },
  created() {
    this.onLoad()
  },
  data() {
    return {
      listTableColumns,
      moment,
    }
  },
  methods: {
    onLoad() {
      this.$store.dispatch('aapcr/FETCH_AAPCRS')
    },
    handleUpdate(id) {
      this.$router.push({
        name: 'aapcr.form',
        params: { id: id },
      })
    },
    viewPdf2(id, documentName) {
      const baseUrl = process.env.VUE_APP_BACKEND_URL
      window.open(baseUrl + '/api/forms/aapcr/viewPdf/' + id + '/' + documentName + '.pdf', '_blank')
    },
    viewPdf(id, documentName) {
      // const baseUrl = process.env.VUE_APP_BACKEND_URL
      this.$store.commit('aapcr/SET_STATE', {
        loading: true,
      })
      const fetchPdfData = apiForm.fetchPdfData
      fetchPdfData(id, documentName).then(response => {
        if (response) {
          const blob = new Blob([response], { type: 'application/pdf' })
          const _url = window.URL.createObjectURL(blob)
          // _url.setAttribute('download', _url)?
          // const link = document.createElement('a')
          // link.href = window.URL.createObjectURL(blob)
          // link.download = documentName + '.pdf'
          // link.setAttribute('target', '_blank')
          // link.click()
          // console.log(response)
          // console.log(_url)
          window.open(_url, '_blank').focus()
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
          self.$store.dispatch('aapcr/PUBLISH_AAPCR', { payload: payload })
        },
      })
    },
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
          self.$store.dispatch('aapcr/DEACTIVATE_AAPCR', { payload: payload })
        },
      })
    },
  },
}
</script>
<style lang="scss">
@import "@/components/system/Forms/Aapcr/style.module.scss";
</style>
