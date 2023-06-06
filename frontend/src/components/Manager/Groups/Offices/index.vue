<template>
  <div>
    <a-table :columns="columns" :data-source="groups" :loading="loading" bordered>
      <template #title>
        <a-button type="primary" class="mr-3" @click="openModal('create', null)" v-if="isCreate">
          <template #icon><PlusOutlined /></template>
          New Group
        </a-button>
      </template>
    </a-table>

    <form-modal ref="officeGroupModal" :visible="isOpenModal" :modal-title="modalTitle" :validate-infos="validateInfos"/>
  </div>
</template>

<script>

import FormModal from './partials/formModal'
import { PlusOutlined } from "@ant-design/icons-vue";
import { Form } from "ant-design-vue";
import { computed, defineComponent, reactive, ref } from "vue";
import { useStore } from 'vuex'

import { usePermission } from '@/services/functions/permission'

const useForm = Form.useForm

const columns = [
  { title: 'Name', dataIndex: 'name', key: 'name' },
  { title: 'Date Created', dataIndex: 'created_at', key: 'created_at' },
  { title: 'Action', dataIndex: 'operation', key: 'operation' },
]

export default defineComponent({
  name: "OfficeGroups",
  components: { PlusOutlined, FormModal },
  setup() {
    const store = useStore()

    // DATA
    const isOpenModal = ref(false)
    let action = ref('')
    let modalTitle = ref('')
    let okText = ref('')
    const groupModal = ref()

    const formState = reactive({
      id: null,
      name: '',
      effectivity: new Date().getFullYear(),
      supervising: undefined,
      offices: [],
      deleted: [],
    })

    const permission = {
      listCreate: ["manager", "m-group", "mg-create"],
      listDelete: ["manager", "m-group", "mg-delete"],
      listEdit: ["manager", "m-group", "mg-edit"],
    }

    const { isCreate, isDelete, isEdit } = usePermission(permission)

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
    })

    const groups = []

    // COMPUTED
    const loading = computed(() => store.getters['external/external'].loading)

    // METHODS
    const openModal = (event, record) => {
      resetModalData()
      isOpenModal.value = true
      // const groupId = record !== null ? record.id : record
      /*if (groupId) {
        formState.id = record.id
        formState.name = record.name
        if (record.oic_id) {
          groupModal.value.getPersonnelList(formState.chair.office, 'oic')
          formState.chair.id = {
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
            isSubunit: item.is_subunit,
            dataId: item.id,
          }
          formState.members.push(data)
        })
      }*/
      changeAction(event)
    }

    const changeAction = event => {
      if (event === 'create') {
        modalTitle.value = 'New Office Group'
        okText.value = 'Create'
        action.value = 'create'
      } else if (event === 'view') {
        modalTitle.value = 'View Office Group'
        okText.value = 'Edit'
        action.value = 'view'
      } else if (event === 'update') {
        modalTitle.value = 'Update Office Group'
        okText.value = 'Update'
        action.value = 'update'
      }
    }

    const { resetFields, validate, validateInfos } = useForm(formState, rules)

    const resetModalData = () => {
      isOpenModal.value = false
      resetFields()
    }

    return {
      columns: columns,
      groups,
      loading,

      isCreate,

      isOpenModal,
      modalTitle,

      validateInfos,
      validate,

      openModal,
    }
  },
})

</script>
