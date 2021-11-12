<template>
  <div>
    <a-table :columns="columns" :data-source="groups" bordered>
      <template #title>
        <a-button type="primary" class="mr-3" @click="openModal('create', null)">
          <template #icon><PlusOutlined /></template>
          New Group
        </a-button>
      </template>
      <template #operation="{ record }">
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
                :modal-title="modalTitle"
                :action-type="action"
                :ok-text="okText"
                :form-state="formState"
                :office-list="offices"
                :supervising-list="vpOfficesList"
                @change-action="changeAction" @close-modal="resetModalData"/>
  </div>
</template>
<script>
import FormModal from './partials/formModal'
import { computed, defineComponent, ref, reactive, onMounted} from 'vue'
import { useStore } from 'vuex'
import { WarningOutlined, PlusOutlined } from '@ant-design/icons-vue'

const columns = [
  { title: 'Name', dataIndex: 'name', key: 'name' },
  { title: 'Date Created', dataIndex: 'created_at', key: 'created_at', scopedSlots: { customRender: 'dateCreated' } },
  { title: 'Action', dataIndex: 'operation', slots: { customRender: 'operation' } },
]

export default defineComponent({
  components: {
    FormModal,
    WarningOutlined,
    PlusOutlined,
  },
  setup() {
    const store = useStore()

    // COMPUTED
    const groups = computed(() => store.getters['formManager/manager'].groups)
    const vpOfficesList = computed(() => store.getters['external/external'].vpOffices)
    const offices = computed(() => store.getters['external/external'].mainOfficesChildren)

    // DATA
    const isOpenModal = ref(false)
    let action = ref('')
    let modalTitle = ref('')
    let okText = ref('')

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

    // EVENTS
    onMounted(() => {
      store.dispatch('formManager/FETCH_GROUPS')
      let params = {
        selectable: {
          allColleges: true,
          mains: true,
        },
        isAcronym: false,
      }
      params = encodeURIComponent(JSON.stringify(params))
      store.dispatch('external/FETCH_MAIN_OFFICES_CHILDREN', { payload: params })
      store.dispatch('external/FETCH_VP_OFFICES', { payload: { officesOnly: 1 } })
    })

    // METHODS
    const openModal = (event, record) => {
      isOpenModal.value = true
      changeAction(event)
    }

    const changeAction = event => {
      console.log(event)
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

    const resetModalData = () => {
      isOpenModal.value = false
    }

    return {
      columns,
      groups,
      offices,
      vpOfficesList,

      isOpenModal,
      formState,
      action,
      modalTitle,
      okText,

      openModal,
      changeAction,
      resetModalData,
    }
  },
})

</script>
