<template>
  <a-table :columns="columns" :data-source="measuresList" :loading="loading" bordered>
    <template #title>
      <a-button type="primary" class="mr-3" @click="openModal('create', null)">
        <template #icon><PlusOutlined /></template>
        New Measure
      </a-button>
    </template>

    <template #dateCreated="{ record }">
      {{ moment(record.created_at).format(mainStore.dateFormat) }}
    </template>

    <template #operation="{ record }">
      <a @click="openModal('view', record)">View</a>
      <a-divider type="vertical" />
      <a-popconfirm
        title="Are you sure you want to delete this?"
        @confirm="onDelete(record.key)"
        ok-text="Yes"
        cancel-text="No"
      >
        <template #icon><warning-outlined /></template>
        <a type="primary">Delete</a>
      </a-popconfirm>
    </template>
  </a-table>
  <form-modal :visible="isOpenModal"
              :action-type="action"
              :modal-title="modalTitle"
              :ok-text="okText"
              :form-state="formState"
              :validate="validate"
              :validate-infos="validateInfos"
              @close-modal="resetModalData"
              @change-action="changeAction"
              @submit-form="onSubmit"/>
</template>
<script>
import { computed, defineComponent, ref, reactive, toRaw, createVNode, onMounted } from 'vue'
import { useStore } from 'vuex'
import moment from 'moment'
import { WarningOutlined, PlusOutlined, ExclamationCircleOutlined } from '@ant-design/icons-vue'
import FormModal from './partials/formModal'
import { Form, Modal } from "ant-design-vue";

const columns = [
  { title: 'Name', dataIndex: 'name', key: 'name' },
  { title: 'Date Created', dataIndex: 'created_at', key: 'created_at', slots: { customRender: 'dateCreated' } },
  { title: 'Action', dataIndex: 'operation', slots: { customRender: 'operation' } },
]

const useForm = Form.useForm

export default defineComponent({
  components: {
    WarningOutlined,
    PlusOutlined,
    FormModal,
  },
  setup() {
    const store = useStore()

    // DATA
    const isOpenModal = ref(false)
    let action = ref('')
    let modalTitle = ref('')
    let okText = ref('')
    const measureId = ref()

    const formState = reactive({
      id: null,
      name: '',
      items: [],
      deleted: [],
    })

    const rules = reactive({
      name: [
        { required: true, message: 'This field is required', trigger: 'change' },
        { whitespace: true, message: 'Please input a valid Group name', trigger: 'change' },
        { min: 3, message: 'Length should be at least 3 characters', trigger: 'change' },
      ],
    })

    // COMPUTED
    const mainStore = computed(() => store.getters.mainStore)
    const measuresList = computed(() => store.getters['formManager/manager'].measures)
    const loading = computed(() => store.getters['formManager/manager'].loading)

    // EVENTS
    onMounted(() => {
      store.dispatch('formManager/FETCH_MEASURES')
    })

    // METHODS
    const openModal = (event, record) => {
      resetModalData()
      isOpenModal.value = true
      const measureId = record !== null ? record.id : record
      if (measureId) {
        // const formItems = record.items
        formState.id = record.id
        formState.name = record.name
        formState.items = record.items
      }
      changeAction(event)
    }

    const changeAction = event => {
      if (event === 'create') {
        modalTitle.value = 'New Measure'
        okText.value = 'Create'
        action.value = 'create'
      } else if (event === 'view') {
        modalTitle.value = 'View Measure'
        okText.value = 'Edit'
        action.value = 'view'
      } else if (event === 'update') {
        modalTitle.value = 'Update Measure'
        okText.value = 'Update'
        action.value = 'update'
      }
    }

    const onDelete = key => {
      store.dispatch('formManager/DELETE_MEASURE', { payload: key })
    }

    const { resetFields, validate, validateInfos } = useForm(formState, rules)

    const resetModalData = () => {
      isOpenModal.value = false
      resetFields()
    }

    const onSubmit = () => {
      Modal.confirm({
        title: () => 'Are you sure you want to save this?',
        icon: () => createVNode(ExclamationCircleOutlined),
        content: () => '',
        okText: 'Yes',
        cancelText: 'No',
        onOk() {
          if (action.value === 'create') {
            store.dispatch('formManager/CREATE_MEASURE', { payload: toRaw(formState) })
          } else {
            store.dispatch('formManager/UPDATE_MEASURE', { payload: toRaw(formState) })
          }
          isOpenModal.value = false
          resetFields()
        },
        onCancel() {},
      });
    }

    return {
      columns,
      moment,

      isOpenModal,
      action,
      modalTitle,
      okText,
      formState,

      mainStore,
      measuresList,
      loading,

      validate,
      validateInfos,

      openModal,
      onDelete,
      resetModalData,
      changeAction,
      onSubmit,
    }
  },
})
</script>
