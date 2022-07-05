<template>
  <div>
    <a-select v-model:value="year" placeholder="Select year" style="width: 200px" @change="fetchMeasures">
      <template v-for="(y, i) in years" :key="i">
        <a-select-option :value="y">
          {{ y }}
        </a-select-option>
      </template>
    </a-select>

    <div class="mt-4">
      <a-table :columns="columns" :data-source="measuresList" :loading="loading" bordered>
        <template #title v-if="isCreate">
          <a-button type="primary" class="mr-3" @click="openModal('create', null)" >
            <template #icon><PlusOutlined /></template>
            New Measure
          </a-button>
          <a-button type="link" v-if="previousMeasures.length" @click="changePreviousModal" >Add {{ year - 1}} measures</a-button>
        </template>

        <template #bodyCell="{ column, record }">
          <template v-if="column.key === 'created_at'">
            {{ dayjs(record.created_at).format(mainStore.dateFormat) }}
          </template>

          <template v-if="column.key === 'operation'">
            <a @click="openModal('view', record)">View</a>
            <span v-if="isDelete">
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
          </span>
          </template>
        </template>
      </a-table>
    </div>

    <form-modal
      :visible="isOpenModal" :action-type="action" :modal-title="modalTitle" :ok-text="okText"  :form-state="formState"
      :validate="validate" :validate-infos="validateInfos" :is-edit="isEdit" :is-create="isCreate" :is-delete="isDelete"
      @close-modal="resetModalData" @change-action="changeAction" @submit-form="onSubmit"
    />

    <measures-previous-list
      :visible="isPreviousViewed" :year="year" :list="previousMeasures"
      @save-measures="onMultipleSave" @close-modal="changePreviousModal" />
  </div>
</template>
<script>
import { computed, defineComponent, ref, reactive, toRaw, createVNode, onMounted } from 'vue'
import { useStore } from 'vuex'
import { Form, Modal } from "ant-design-vue";
import { WarningOutlined, PlusOutlined, ExclamationCircleOutlined } from '@ant-design/icons-vue'
import dayjs from 'dayjs'
import FormModal from './partials/formModal'
import MeasuresPreviousList from './partials/previousList'
import { usePermission } from '@/services/functions/permission'

const columns = [
  { title: 'Name', dataIndex: 'name', key: 'name' },
  { title: 'Display as Items', dataIndex: 'displayAsItems', key: 'displayAsItems' },
  { title: 'Date Created', dataIndex: 'created_at', key: 'created_at' },
  { title: 'Action', dataIndex: 'operation', key: 'operation' },
]

const useForm = Form.useForm

export default defineComponent({
  name: 'MeasuresList',
  components: {
    WarningOutlined,
    PlusOutlined,
    FormModal,
    MeasuresPreviousList,
  },
  setup() {
    const store = useStore()

    // DATA
    const isOpenModal = ref(false)
    let action = ref('')
    let modalTitle = ref('')
    let okText = ref('')
    const year = ref(new Date().getFullYear())
    const isPreviousViewed = ref(false)

    const formState = reactive({
      id: null,
      year: year.value,
      name: '',
      displayAsItems: false,
      items: [],
      deleted: [],
    })

    const rules = reactive({
      name: [
        { required: true, message: 'This field is required', trigger: 'blur' },
        { whitespace: true, message: 'Please input a valid Group name', trigger: 'change' },
        { min: 3, message: 'Length should be at least 3 characters', trigger: 'change' },
      ],
    })

    // COMPUTED
    const mainStore = computed(() => store.getters.mainStore)
    const measuresList = computed(() => store.getters['formManager/manager'].measures)
    const previousMeasures = computed(() => store.getters['formManager/manager'].previousMeasures)
    const loading = computed(() => store.getters['formManager/manager'].loading)

    const years = computed(() => {
      const max = new Date().getFullYear() + 1
      const min = 10
      const lists = []
      for (let i = max; i >= (max - min); i--) {
        lists.push(i)
      }
      return lists
    })

    const permission = {
      listCreate: ["manager","m-measures", "mm-create"],
      listDelete: ["manager","m-measures", "mm-delete"],
      listEdit: ["manager","m-measures", "mm-edit"],
    }

    const { isCreate, isDelete, isEdit } = usePermission(permission)

    // EVENTS
    onMounted(() => {
      fetchMeasures(year.value)
    })

    // METHODS
    const fetchMeasures = async selectedYear => {
      await store.dispatch('formManager/FETCH_MEASURES', { payload : { year: selectedYear, isPrevious: false }})
      await store.dispatch('formManager/FETCH_MEASURES', { payload : { year: (selectedYear - 1), isPrevious: true }})
    }

    const openModal = (event, record) => {
      resetModalData()
      isOpenModal.value = true
      const measureId = record !== null ? record.id : record
      if (measureId) {
        formState.id = record.id
        formState.name = record.name
        formState.displayAsItems = !!record.display_as_items
        formState.items = record.items
        formState.year = record.year
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
      store.dispatch('formManager/DELETE_MEASURE', { payload: { id: key, year: year.value } })
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
          formState.year = year.value
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

    const changePreviousModal = () => {
      isPreviousViewed.value = !isPreviousViewed.value
    }

    const onMultipleSave = keys => {
      const saveKeys = previousMeasures.value.filter(item => {
        return keys.indexOf(item.key) !== -1
      })

      saveKeys.forEach(item => {
        const data = {
          name: item.name,
          displayAsItems: item.display_as_items,
          year: year.value,
          items: item.items,
        }
        store.dispatch('formManager/CREATE_MEASURE', { payload: data })
      })
      changePreviousModal()
    }

    return {
      columns,
      dayjs,

      isOpenModal,
      action,
      modalTitle,
      okText,
      year,
      isPreviousViewed,
      formState,

      mainStore,
      measuresList,
      previousMeasures,
      loading,
      years,
      isCreate,
      isDelete,
      isEdit,

      validate,
      validateInfos,

      fetchMeasures,
      openModal,
      onDelete,
      resetModalData,
      changeAction,
      onSubmit,
      changePreviousModal,
      onMultipleSave,
    }
  },
})
</script>
