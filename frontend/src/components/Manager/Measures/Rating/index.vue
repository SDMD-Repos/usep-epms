<template>
  <div>
    <a-select v-model:value="year" placeholder="Select year" style="width: 200px" @change="fetchAllMeasureRating">
      <template v-for="(y, i) in years" :key="i">
        <a-select-option :value="y">
          {{ y }}
        </a-select-option>
      </template>
    </a-select>

    <div class="mt-4">
      <a-table :columns="columns" :data-source="measureRatingList" :loading="loading" bordered>
        <template #title v-if="isCreate">
          <a-button type="primary" class="mr-3" @click="openModal('create', null)" >
            <template #icon><PlusOutlined /></template>
            New
          </a-button>
          <a-button type="link" v-if="previousMeasureRatings.length" @click="changePreviousModal" >Add {{ year - 1}} ratings</a-button>
        </template>

        <template #bodyCell="{ column, record }">
          <template v-if="column.key === 'average_point_score'">
            {{ record.aps_from }} - {{ record.aps_to }}
          </template>

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
      :visible="isOpenModal" :action-type="action" :modal-title="modalTitle" :ok-text="okText" :form-state="formState"
      :validate="validate" :validate-infos="validateInfos" :is-edit="isEdit" :is-create="isCreate" :is-delete="isDelete"
      @close-modal="resetModalData" @change-action="changeAction" @submit-form="checkRatingDuplicate"
    />

    <ratings-previous-list
      :visible="isPreviousViewed" :year="year" :table-columns="columns" :list="previousMeasureRatings"
      :current-list="measureRatingList"
      @save-ratings="onMultipleSave" @close-modal="changePreviousModal" />
  </div>
</template>
<script>
import {defineComponent, ref, reactive, createVNode, toRaw, onMounted, computed, inject} from "vue";
import { useStore } from 'vuex'
import { WarningOutlined, PlusOutlined, ExclamationCircleOutlined } from '@ant-design/icons-vue'
import { Form, Modal } from "ant-design-vue";
import { usePermission } from '@/services/functions/permission'
import { measuresRating } from '@/services/columns'
import FormModal from './partials/formModal'
import RatingsPreviousList from './partials/previousList'
import dayjs from 'dayjs'

const useForm = Form.useForm

export default defineComponent({
  name: 'MeasureRatingsTab',
  components: { WarningOutlined, PlusOutlined, FormModal, RatingsPreviousList },
  setup() {
    const store = useStore()

    const _message = inject('a-message')

    // DATA
    const year = ref(new Date().getFullYear())
    const ratingList = ref([])
    const isOpenModal = ref(false)
    const action = ref('')
    const modalTitle = ref('')
    const okText = ref('')
    const isPreviousViewed = ref(false)

    const requestPayload = ref([])

    const formState = reactive({
      id: null,
      year: year.value,
      numericalRating: null,
      averagePointScore: { from: null, to: null },
      adjectivalRating: "",
      description: null,
    })

    const rules = reactive({
      numericalRating: [
        { required: true, message: 'This field is required', trigger: 'blur' },
        { type: 'number', min: 1, max: 10, message: 'Must be a number', trigger: 'blur'},
      ],
      'averagePointScore.from': [
        { required: true, message: 'This field is required', trigger: 'blur' },
      ],
      'averagePointScore.to': [
        { required: true, message: 'This field is required', trigger: 'blur' },
      ],
      adjectivalRating: [
        { required: true, message: 'This field is required', trigger: 'blur' },
        { whitespace: true, message: 'Please input a valid Adjectival Rating', trigger: 'blur' },
      ],
      description: [
        { required: true, message: 'This field is required', trigger: 'blur' },
        { whitespace: true, message: 'Please input a valid Description', trigger: 'blur' },
      ],
    })

    const { resetFields, validate, validateInfos } = useForm(formState, rules)

    const permission = {
      listCreate: ["manager","m-measures", "mm-create"],
      listDelete: ["manager","m-measures", "mm-delete"],
      listEdit: ["manager","m-measures", "mm-edit"],
    }

    const { isCreate, isDelete, isEdit } = usePermission(permission)

    // COMPUTED
    const mainStore = computed(() => store.getters.mainStore)
    const loading = computed(() => store.getters['formManager/manager'].loading)
    const measureRatingList = computed(() => store.getters['formManager/manager'].measureRatings)
    const previousMeasureRatings = computed(() => store.getters['formManager/manager'].previousMeasureRatings)

    const years = computed(() => {
      const max = new Date().getFullYear() + 1
      const min = 10
      const lists = []
      for (let i = max; i >= (max - min); i--) {
        lists.push(i)
      }
      return lists
    })

    onMounted(() => {
      fetchAllMeasureRating(year.value)
    })

    // METHODS
    const fetchAllMeasureRating = async selectedYear => {
      await store.dispatch('formManager/FETCH_MEASURE_RATINGS', { payload : { year: selectedYear, isPrevious: false }})
      await store.dispatch('formManager/FETCH_MEASURE_RATINGS', { payload : { year: (selectedYear - 1), isPrevious: true }})
    }

    const openModal = (event, record) => {
      resetModalData()
      isOpenModal.value = true
      const measureId = record !== null ? record.id : record
      if (measureId) {
        formState.id = record.id
        formState.numericalRating = record.rating.numerical_rating
        formState.averagePointScore = { from: record.aps_from, to: record.aps_to }
        formState.adjectivalRating = record.adjectival_rating
        formState.description = record.description
      }
      changeAction(event)
    }

    const onDelete = key => {
      store.dispatch('formManager/DELETE_MEASURE_RATING', { payload: { id: key, year: year.value } })
    }

    const checkRatingDuplicate = () => {
      const { numericalRating } = formState
      const hasDuplicate = measureRatingList.value.filter(i => i.rating.numerical_rating === numericalRating )
      if(hasDuplicate.length > 0 && action.value === 'create') {
        _message.error("Unable to save data. A same Numerical Rating has already been set for the year " + year.value);
      }else {
        onSubmit()
      }
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
            const copy = {...toRaw(formState)}

            requestPayload.value = [{
              year: year.value,
              numerical_rating: copy.numericalRating,
              aps_from: copy.averagePointScore.from,
              aps_to: copy.averagePointScore.to,
              adjectival_rating: copy.adjectivalRating,
              description: copy.description,
            }]

            store.dispatch('formManager/CREATE_MEASURE_RATING', { payload: { ratings: requestPayload.value, year: year.value }})
          } else {
            formState.year = year.value

            store.dispatch('formManager/UPDATE_MEASURE_RATING', { payload: toRaw(formState) })
          }
          isOpenModal.value = false
          resetFields()
        },
        onCancel() {},
      });
    }

    const changeAction = event => {
      if (event === 'create') {
        modalTitle.value = 'New Rating'
        okText.value = 'Create'
        action.value = 'create'
      } else if (event === 'view') {
        modalTitle.value = 'View Rating'
        okText.value = 'Edit'
        action.value = 'view'
      } else if (event === 'update') {
        modalTitle.value = 'Update Rating'
        okText.value = 'Update'
        action.value = 'update'
      }
    }

    const resetModalData = () => {
      isOpenModal.value = false
      resetFields()
    }

    const changePreviousModal = () => {
      isPreviousViewed.value = !isPreviousViewed.value
    }

    const onMultipleSave = async keys => {
      requestPayload.value = []

      const saveKeys = previousMeasureRatings.value.filter(item => {
        return keys.indexOf(item.key) !== -1
      })

      for await ( const item of saveKeys) {
        const data = {
          year: year.value,
          numerical_rating: item.numerical_rating,
          aps_from: item.aps_from,
          aps_to: item.aps_to,
          adjectival_rating: item.adjectival_rating,
          description: item.description,
        }

        requestPayload.value.push(data)
      }

      await store.dispatch('formManager/CREATE_MEASURE_RATING', { payload: { ratings: requestPayload.value, year: year.value }})
      await changePreviousModal()
    }

    return {
      dayjs,
      columns: measuresRating,
      year, ratingList, isOpenModal, action, modalTitle, okText, isPreviousViewed, formState,
      resetFields, validate, validateInfos,

      isCreate, isDelete, isEdit,

      mainStore, loading, measureRatingList, previousMeasureRatings,
      years,

      fetchAllMeasureRating, openModal, onDelete, checkRatingDuplicate, changeAction, resetModalData,
      changePreviousModal, onMultipleSave,
    }
  },
})
</script>
