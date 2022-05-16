<template>
  <div>
    <a-spin :spinning="loading" tip="Fetching data in HRIS...">
      <a-select v-model:value="year" placeholder="Select year" style="width: 200px" @change="fetchSignatories">
        <template v-for="(y, i) in yearList" :key="i">
          <a-select-option :value="y">
            {{ y }}
          </a-select-option>
        </template>
      </a-select>
      <div class="mt-2" v-if="formId === 'vpopcr'">
        <a-select v-model:value="selectedOffice" placeholder="Select office" style="width: 450px" @change="fetchSignatories">
          <template v-for="(y, i) in vpOfficesList" :key="i">
            <a-select-option :value="y.value" >
              {{ y.title }}
            </a-select-option>
          </template>
        </a-select>
      </div>
      <div class="mt-4">
        <a-collapse v-model:activeKey="activeKey">
          <a-collapse-panel v-for="(type, key) in signatoryTypes" :header="type.name" :key="`${key}`">
            <signatory-list :year="year"
                            :form-id="formId"
                            :list="filterBySignatory(type.id)"
                            :loading="loading" />
            <template #extra v-if="formId !== 'vpopcr' || (formId === 'vpopcr' && typeof selectedOffice !== 'undefined')">
              <user-add-outlined v-if="!filterBySignatory(type.id).length" @click="openModal($event, 'create', type.id)"/>
              <edit-outlined v-else @click="openModal($event, 'update', type.id)" />
            </template>
          </a-collapse-panel>
        </a-collapse>
      </div>
    </a-spin>

    <form-modal
      :visible="isOpenModal" :modal-title="modalTitle" :ok-text="okText" :action-type="action" :office-list="offices"
      :position-list="positionList" :details="signatoryDetails" :form-state="formState"
      @add-signatory="addSignatory" @delete-signatory="deleteSignatory" @close-modal="resetFormModal"
    />
  </div>
</template>
<script>
import { defineComponent, ref, watch, reactive, onMounted, computed } from 'vue'
import { useStore } from 'vuex'
import { UserAddOutlined, EditOutlined } from '@ant-design/icons-vue'
import { Modal } from "ant-design-vue"
import SignatoryList from './partials/list'
import FormModal from './partials/formModal'

export default defineComponent({
  name: "SignatoryManager",
  components: {
    SignatoryList, FormModal, UserAddOutlined, EditOutlined,
  },
  props: {
    formName: {
      type: String,
      default: '',
    },
  },
  setup(props) {
    const store = useStore()

    // DATA
    const year = ref(new Date().getFullYear())
    const isOpenModal = ref(false)

    const formState = reactive({
      signatories: [],
    })

    let formId = ref(props.formName)
    let action = ref('')
    let modalTitle = ref('')
    let okText = ref('')
    const selectedOffice = ref(undefined)

    const signatoryDetails = ref({})

    // COMPUTED
    const signatoryTypes = computed(() => store.getters['formManager/manager'].signatoryTypes)
    const offices = computed(() => store.getters['external/external'].mainOfficesChildren)
    const vpOfficesList = computed(() => store.getters['external/external'].vpOffices)
    const positionList = computed(() => store.getters['external/external'].positionList)
    const signatoryList = computed(() => store.getters['formManager/manager'].signatories)
    const loading = computed(() => store.getters['external/external'].loading)

    const yearList = computed(() => {
      const now = new Date().getFullYear()
      const min = 10
      const lists = []
      for (let i = now; i >= (now - min); i--) {
        lists.push(i)
      }
      return lists
    })

    // EVENTS
    onMounted(() => {
      let params = {
        selectable: { allColleges: true, mains: true },
        isAcronym: false,
      }

      store.dispatch('external/FETCH_MAIN_OFFICES_CHILDREN', { payload: params })
      store.dispatch('external/FETCH_VP_OFFICES', { payload: { officesOnly: 1 } })
      store.dispatch('formManager/FETCH_ALL_SIGNATORY_TYPES')
      store.dispatch('external/FETCH_ALL_POSITIONS')
      fetchSignatories()
    })

    watch(() => props.formName , formName => {
      formId.value = formName
    })

    // METHODS
    const fetchSignatories = () => {
      const data = {
        year: year.value,
        formId: formId.value,
      }
      store.commit('formManager/SET_STATE', {
        signatories: [],
      })
      if (formId.value === 'vpopcr') {
        if (typeof selectedOffice.value !== 'undefined') {
          data.officeId = selectedOffice.value
          store.dispatch('formManager/FETCH_YEAR_SIGNATORIES', { payload: data })
        }
      } else {
        store.dispatch('formManager/FETCH_YEAR_SIGNATORIES', { payload: data })
      }
    }

    const filterBySignatory = type => {
      return signatoryList.value.filter((i) => {
        return i.type_id === parseInt(type)
      })
    }

    const openModal = (event, action, type) => {
      event.stopPropagation()
      isOpenModal.value = true
      signatoryDetails.value = {
        typeId: type,
        year: year.value,
        formId: formId.value,
        office: selectedOffice.value,
      }
      changeAction(action,type)
    }

    const addSignatory = () => {
      if(formState.signatories.length < 3) {
        formState.signatories.push({
          id: 'new',
          officeId: undefined,
          personnelId: undefined,
          position: undefined,
          isCustom: false,
        })
      } else {
        Modal.error({
          title: () => 'Up to 3 signatories are only allowed to be added',
          content: () => '',
        })
      }
    }

    const deleteSignatory = index => {
      formState.signatories.splice(index, 1)
    }

    const populateSignatory = type => {
      const list = filterBySignatory(type)
      list.forEach(item => {
        let officeIdValue = item.office_id ? item.office_id : item.office_name
        if (item.office_id) {
          officeIdValue = {
            value: parseInt(item.office_id),
            label: item.office_name,
          }
        }
        let personnelIdValue = item.personnel_id ? item.personnel_id : item.personnel_name
        if (item.personnel_id) {
          personnelIdValue = {
            value: item.personnel_id,
            label: item.personnel_name,
          }
        }
        formState.signatories.push({
          id: item.id,
          officeId: officeIdValue,
          personnelId: personnelIdValue,
          position: item.position,
          isCustom: !item.office_id && !item.personnel_id,
        })
      })
    }

    const changeAction = (event, type) => {
      if (event === 'create') {
        modalTitle.value = 'New Signatory'
        okText.value = 'Create'
        action.value = 'create'
        addSignatory()
      } else if (event === 'update') {
        modalTitle.value = 'Update Signatory'
        okText.value = 'Update'
        action.value = 'update'
        populateSignatory(type)
      }
    }

    const resetFormModal = () => {
      formState.signatories = []
      signatoryDetails.value = {}
      isOpenModal.value = false
    }

    return {
      activeKey: ref('0'),
      year,
      formId,
      isOpenModal,
      formState,
      action,
      modalTitle,
      okText,
      signatoryDetails,
      selectedOffice,

      yearList,
      signatoryTypes,
      offices,
      vpOfficesList,
      positionList,
      signatoryList,
      loading,

      fetchSignatories,
      filterBySignatory,
      openModal,
      addSignatory,
      deleteSignatory,
      resetFormModal,
    }
  },
})
</script>
