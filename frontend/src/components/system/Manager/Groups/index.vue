<template>
  <div>
    <div class="row">
      <div class="col-xl-12 col-lg-12">
        <div class="card">
          <div class="card-header card-header-flex align-items-center">
            <div class="d-flex flex-column justify-content-center mr-auto">
            </div>
            <div>
              <a-tooltip placement="top">
                <a-button type="primary" icon="plus" class="mr-3" @click="openModal('create', null)">New Group</a-button>
              </a-tooltip>
            </div>
          </div>
          <div class="card-body">
            <a-table :columns="columns" :dataSource="groups" :loading="loading">

              <span slot="dateCreated" slot-scope="text, record">
                {{ moment(record.created_at).format(dateFormat)}}
              </span>

              <template slot="action" slot-scope="text, record">
                <a type="primary" @click="openModal('view', record)">View</a>
                <a-divider type="vertical" />
                <a-popconfirm
                  title="Are you sure you want to delete this?"
                  @confirm="onDelete(record.key)"
                  okText="Yes"
                  cancelText="No"
                >
                  <a type="primary">Delete</a>
                </a-popconfirm>
              </template>
            </a-table>
          </div>
        </div>
        <form-modal ref="groupModal" :open="isOpenModal"
                    :form-object="form"
                    :modal-title="modalTitle"
                    :ok-text="okText"
                    :action-type="action"
                    :office-list="offices"
                    :supervising-list="vpOfficesList"
                    :date-formatter="moment"
                    @change-action="changeAction"
                    @close-modal="resetModalData"
                    @submit-form="submitForm" />
      </div>
    </div>
  </div>
</template>
<script>
import FormModal from './partials/formModal'
import { mapState } from 'vuex'
import moment from 'moment'
import { Modal } from 'ant-design-vue'

const columns = [
  { title: 'Name', dataIndex: 'name', key: 'name' },
  { title: 'Date Created', dataIndex: 'created_at', key: 'created_at', scopedSlots: { customRender: 'dateCreated' } },
  { title: 'Action', dataIndex: '', key: 'x', scopedSlots: { customRender: 'action' } },
]

export default {
  components: {
    FormModal,
  },
  data() {
    return {
      columns,
      form: {
        id: null,
        name: '',
        hasChair: false,
        chairOffice: undefined,
        chairId: undefined,
        effectivity: new Date().getFullYear(),
        supervising: undefined,
        members: [],
        deleted: [],
      },
      groupId: null,
      isOpenModal: false,
      action: '',
      modalTitle: '',
      okText: '',
    }
  },
  computed: {
    ...mapState({
      offices: state => state.external.mainOfficesChildren,
      vpOfficesList: state => state.external.vpOffices,
      groups: state => state.formManager.groups,
      loading: state => state.formManager.loading,
      dateFormat: state => state.dateFormat,
    }),
  },
  created() {
    this.onLoad()
  },
  methods: {
    moment,
    onLoad() {
      this.$store.dispatch('formManager/FETCH_GROUPS')
      let params = {
        selectable: {
          allColleges: true,
          mains: true,
        },
        isAcronym: false,
      }
      params = encodeURIComponent(JSON.stringify(params))
      this.$store.dispatch('external/FETCH_MAIN_OFFICES_CHILDREN', { payload: params })
      this.$store.dispatch('external/FETCH_VP_OFFICES', { payload: { officesOnly: 1 } })
    },
    openModal(event, record) {
      this.resetModalData()
      this.isOpenModal = true
      this.groupId = record !== null ? record.id : record
      if (this.groupId) {
        this.form.id = record.id
        this.form.name = record.name
        if (record.oic_id) {
          this.form.hasChair = true
          this.form.chairId = {
            key: record.oic_id,
            label: record.oic_name,
          }
          this.form.chairOffice = {
            value: record.oic_dept_id,
            label: record.oic_dept_name,
          }
          this.$refs.groupModal.getPersonnelList(this.form.chairOffice, 'oic')
        }
        this.form.effectivity = record.effective_until
        if (record.supervising_id) {
          this.form.supervising = {
            key: record.supervising_id,
            label: record.supervising_name,
          }
        }
        record.members.forEach(item => {
          const data = {
            id: {
              value: item.member_id,
              label: item.member_name,
            },
            officeId: {
              value: item.office_id,
              label: item.office_name,
            },
            dataId: item.id,
          }
          this.form.members.push(data)
        })
      }
      this.changeAction(event)
    },
    changeAction(action) {
      if (action === 'create') {
        this.modalTitle = 'New Group'
        this.okText = 'Create'
        this.action = 'create'
      } else if (action === 'view') {
        this.modalTitle = 'View Group'
        this.okText = 'Edit'
        this.action = 'view'
      } else if (action === 'update') {
        this.modalTitle = 'Update Group'
        this.okText = 'Update'
        this.action = 'update'
      }
    },
    resetModalData() {
      this.isOpenModal = false
      this.form = {
        name: '',
        hasChair: false,
        chairOffice: undefined,
        chairId: undefined,
        effectivity: new Date().getFullYear(),
        supervising: undefined,
        members: [],
        deleted: [],
      }
    },
    submitForm() {
      const self = this
      const { form } = this
      Modal.confirm({
        title: 'Are you sure you want to save this?',
        content: '',
        okText: 'Yes',
        cancelText: 'No',
        onOk() {
          if (self.action === 'create') {
            self.$store.dispatch('formManager/CREATE_GROUP', { payload: form })
          } else {
            console.log(form)
            self.$store.dispatch('formManager/UPDATE_GROUP', { payload: form })
          }
          self.isOpenModal = false
          self.resetModalData()
        },
      })
    },
    onDelete(key) {
      this.$store.dispatch('formManager/DELETE_GROUP', { payload: key })
    },
  },
}
</script>
