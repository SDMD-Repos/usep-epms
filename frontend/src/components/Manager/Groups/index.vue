<template>
  <div>
    <a-table :columns="columns" :data-source="groups" :loading="loading" bordered>
      <template #title>
        <a-button type="primary" class="mr-3" @click="openModal('create', null)" v-if="createGroupPermission">
          <template #icon><PlusOutlined /></template>
          New Group
        </a-button>
      </template>

      <template #dateCreated="{ record }">
        {{ moment(record.created_at).format(mainStore.dateFormat)}}
      </template>

      <template #operation="{ record }">
        <a @click="openModal('view', record)">View</a>
        <span v-if="deleteGroupPermission">
          <a-divider type="vertical" />
          <a-popconfirm
            title="Are you sure you want to delete this?"
            @confirm="onDelete(record.key)"
            ok-text="Yes"
            cancel-text="No"

          >
            <template #icon><warning-outlined /></template>
            <a type="primary" >Delete</a>
          </a-popconfirm>
        </span>
      </template>
    </a-table>

    <form-modal ref="groupModal" :visible="isOpenModal" :modal-title="modalTitle" :action-type="action" :ok-text="okText"
                :form-state="formState" :form-rules="rules" :office-list="offices" :supervising-list="vpOfficesList"
                :validate="validate" :validate-infos="validateInfos" :is-edit="editGroupPermission" :is-delete="deleteGroupPermission"
                @change-action="changeAction" @close-modal="resetModalData" @submit-form="submitForm"/>
  </div>
</template>
<script>
import FormModal from './partials/formModal'
import { computed, defineComponent, ref, reactive, toRaw, onMounted, createVNode } from 'vue'
import { useStore } from 'vuex'
import moment from 'moment'
import { Form, Modal } from 'ant-design-vue'
import { WarningOutlined, PlusOutlined, ExclamationCircleOutlined } from '@ant-design/icons-vue'

const columns = [
  { title: 'Name', dataIndex: 'name', key: 'name' },
  { title: 'Date Created', dataIndex: 'created_at', key: 'created_at', slots: { customRender: 'dateCreated' } },
  { title: 'Action', dataIndex: 'operation', slots: { customRender: 'operation' } },
]

const useForm = Form.useForm

export default defineComponent({
  components: {
    FormModal,
    WarningOutlined,
    PlusOutlined,
  },
  setup() {
    const store = useStore()

    // COMPUTED
    const mainStore = computed(() => store.getters.mainStore)
    const loading = computed(() => store.getters['external/external'].loading)
    const groups = computed(() => store.getters['formManager/manager'].groups)
    const vpOfficesList = computed(() => store.getters['external/external'].vpOffices)
    const offices = computed(() => store.getters['external/external'].mainOfficesChildren)
    const createGroupPermission = computed(() => store.getters['system/permission'].createGroupPermission)
    const editGroupPermission = computed(() => store.getters['system/permission'].editGroupPermission)
    const deleteGroupPermission = computed(() => store.getters['system/permission'].deleteGroupPermission)

    // DATA
    const isOpenModal = ref(false)
    let action = ref('')
    let modalTitle = ref('')
    let okText = ref('')
    const groupModal = ref()

    const formState = reactive({
      id: null,
      name: '',
      hasChair: false,
      chairOffice: undefined,
      chairId: undefined,
      effectivity: new Date().getFullYear(),
      supervising: undefined,
      members: [],
      deleted: [],
    })

    let checkIfEmpty = async (rule, value) => {
      if (formState.hasChair && (value === '' || typeof value === 'undefined' || value.length === 0)) {
        return Promise.reject('This field is required')
      } else {
        return Promise.resolve()
      }
    }

    const rules = reactive({
      name: [
        { required: true, message: 'This field is required', trigger: 'change' },
        { whitespace: true, message: 'Please input a valid Group name', trigger: 'change' },
        { min: 3, message: 'Length should be at least 3 characters', trigger: 'change' },
      ],
      effectivity: [
        { required: true, message: 'Please pick a date' },
      ],
      supervising: [
        { required: true, message: 'Please choose one' },
      ],
      chairId: [
        { validator: checkIfEmpty, trigger: 'change' },
      ],
    })

    // EVENTS
    onMounted(() => {
      checkUserPermission()

      store.dispatch('formManager/FETCH_GROUPS')

      let params = {
        selectable: {
          allColleges: false,
          mains: true,
        },
        isAcronym: false,
        isOfficesOnly: true,
      }

      params = encodeURIComponent(JSON.stringify(params))
      store.dispatch('external/FETCH_MAIN_OFFICES_CHILDREN', { payload: params })
      store.dispatch('external/FETCH_VP_OFFICES', { payload: { officesOnly: 1 } })
    })

    // METHODS
    const checkUserPermission = () => {
      const groupCreatePermissions = ["manager", "m-group", "mg-create"]
      const groupeEditPermissions = ["manager", "m-group", "mg-edit"]
      const groupeDeletePermissions = ["manager", "m-group", "mg-delete"]

      store.dispatch('system/CHECK_PERMISSION', { payload: {permission: groupCreatePermissions, name:'createGroupPermission'} })
      store.dispatch('system/CHECK_PERMISSION', { payload: {permission: groupeEditPermissions, name:'editGroupPermission'} })
      store.dispatch('system/CHECK_PERMISSION', { payload: {permission: groupeDeletePermissions, name:'deleteGroupPermission'} })
    }

    const openModal = (event, record) => {
      resetModalData()
      isOpenModal.value = true
      const groupId = record !== null ? record.id : record
      if (groupId) {
        formState.id = record.id
        formState.name = record.name
        if (record.oic_id) {
          formState.hasChair = true
          formState.chairOffice = {
            value: record.oic_dept_id,
            label: record.oic_dept_name,
          }
          groupModal.value.getPersonnelList(formState.chairOffice, 'oic')
          formState.chairId = {
            value: record.oic_id,
            label: record.oic_name,
          }
        }
        formState.effectivity = record.effective_until
        if (record.supervising_id) {
          formState.supervising = {
            value: record.supervising_id,
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
          formState.members.push(data)
        })
      }
      changeAction(event)
    }

    const changeAction = event => {
      if (event === 'create') {
        modalTitle.value = 'New Group'
        okText.value = 'Create'
        action.value = 'create'
      } else if (event === 'view') {
        modalTitle.value = 'View Group'
        okText.value = 'Edit'
        action.value = 'view'
      } else if (event === 'update') {
        modalTitle.value = 'Update Group'
        okText.value = 'Update'
        action.value = 'update'
      }
    }

    const { resetFields, validate, validateInfos } = useForm(formState, rules)

    const resetModalData = () => {
      isOpenModal.value = false
      resetFields()
    }

    const submitForm = async () => {
      Modal.confirm({
        title: () => 'Are you sure you want to save this?',
        icon: () => createVNode(ExclamationCircleOutlined),
        content: () => '',
        okText: 'Yes',
        cancelText: 'No',
        onOk() {
          if (action.value === 'create') {
            store.dispatch('formManager/CREATE_GROUP', { payload: toRaw(formState) })
          } else {
            store.dispatch('formManager/UPDATE_GROUP', { payload: toRaw(formState) })
          }
          isOpenModal.value = false
          resetFields()
        },
        onCancel() {},
      });
    }

    const onDelete = key => {
      store.dispatch('formManager/DELETE_GROUP', { payload: key })
    }

    return {
      columns,
      moment,

      mainStore,
      loading,
      groups,
      offices,
      vpOfficesList,

      isOpenModal,
      formState,
      rules,
      action,
      modalTitle,
      okText,
      groupModal,
      createGroupPermission,
      deleteGroupPermission,
      editGroupPermission,

      useForm,
      validate,
      validateInfos,

      openModal,
      changeAction,
      resetModalData,
      submitForm,
      onDelete,
    }
  },
})

</script>
