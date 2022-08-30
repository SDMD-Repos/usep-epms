<template>
  <div>
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
    <div class="mt-2" v-if="formId === 'opcr'">
      <a-tree-select
        v-model:value="selectedOffice"
        :dropdown-style="{ maxHeight: '400px', overflow: 'auto' }"
        style="width: 450px"
        placeholder="Select Office/College"
        tree-node-filter-prop="title"
        :tree-data="offices"
        tree-default-expand-all
        show-search
        allow-clear
        label-in-value
        @change="fetchSignatories"
      />

    </div>


    <div class="mt-4">
      <a-collapse v-model:activeKey="activeKey">
        <a-collapse-panel v-for="(type, key) in signatoryTypes" :header="type.name" :key="`${key}`">
          <signatory-list :year="year"
                          :form-id="formId"
                          :list="filterBySignatory(type.id)" />
          <template #extra v-if="formId !== 'vpopcr' || (formId === 'vpopcr' && typeof selectedOffice !== 'undefined')">
            <user-add-outlined v-if="!filterBySignatory(type.id).length" @click="openModal($event, 'create', type.id)"/>
            <edit-outlined v-else @click="openModal($event, 'update', type.id)" />
          </template>
        </a-collapse-panel>
      </a-collapse>
    </div>

    <form-modal
      :visible="isOpenModal" :modal-title="modalTitle" :ok-text="okText" :action-type="action" :office-list="offices"
      :position-list="positionList" :details="signatoryDetails" :form-state="formState"
      @add-signatory="addSignatory" @delete-signatory="deleteSignatory" @close-modal="resetFormModal"
    />
  </div>
</template>
<script>
import { defineComponent, ref, watch, reactive, onMounted, onBeforeMount, inject, computed } from 'vue'
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

    const _message = inject('a-message')

    // DATA
    const year = ref(new Date().getFullYear())
    const isOpenModal = ref(false)
    const officeDetails = computed(()=> {
      switch (props.formName) {
         case 'aapcr':
           return store.getters['system/permission'].officeHeadDetailsAAPCR
         break
        case 'vpopcr':
          return store.getters['system/permission'].officeHeadDetailsVPOPCR
          break
        case 'opcr':
          return store.getters['system/permission'].officeHeadDetailsOPCR
          break
        default:
          return {}
          break
      }
    })

    const formState = reactive({
      signatories: [],
    })

    watch(() => [officeDetails.value] , ([officeDetails]) => {
      if (officeDetails && Object.keys(officeDetails).length > 0){
        selectedOffice.value = {
          "label": officeDetails.office_name,
          "value": officeDetails.office_id,
        }
      }
    })

    let formId = ref(props.formName)
    let action = ref('')
    let modalTitle = ref('')
    let okText = ref('')
    const selectedOffice = ref(undefined)
    const memberList = ref([])

    const signatoryDetails = ref({})

    // COMPUTED
    const signatoryTypes = computed(() => store.getters['formManager/manager'].signatoryTypes)
    const offices = computed(() => store.getters['external/external'].mainOfficesChildren)
    const vpOfficesList = computed(() => store.getters['external/external'].vpOffices)
    const positionList = computed(() => store.getters['external/external'].positionList)
    const signatoryList = computed(() => store.getters['formManager/manager'].signatories)

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
    onBeforeMount(async () => {

      let params = {
        selectable: { allColleges: true, mains: true },
        isAcronym: false,
        isOfficesOnly: true,
      }

      switch (formId.value){
        case 'opcr':
          params.selectable = { allColleges: false, mains: false }
          break
        default:
          break
      }

      await store.dispatch('formManager/FETCH_ALL_SIGNATORY_TYPES')
      await store.dispatch('external/FETCH_MAIN_OFFICES_CHILDREN', { payload: params })
      await store.dispatch('external/FETCH_VP_OFFICES', { payload: { officesOnly: 1 } })
      await store.dispatch('external/FETCH_ALL_POSITIONS')
      await store.dispatch('system/FETCH_OFFICE_DETAILS',{ payload: { form_id: formId.value, office_id: null }})
    })

    onMounted(() => {
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

      switch (formId.value){
        case 'vpopcr':
          if (typeof selectedOffice.value !== 'undefined') {
            data.officeId = selectedOffice.value
            store.dispatch('formManager/FETCH_YEAR_SIGNATORIES', { payload: data })
          }
          break
        case 'opcr':
          if (selectedOffice.value && typeof selectedOffice.value !== 'undefined'){
            data.officeId = selectedOffice.value.value
            store.dispatch('formManager/FETCH_YEAR_SIGNATORIES', { payload: data })
          }
          break
        default:
          store.dispatch('formManager/FETCH_YEAR_SIGNATORIES', { payload: data })
          break
      }
      store.dispatch('system/FETCH_OFFICE_DETAILS',{ payload: { form_id: formId.value, office_id: null }})
    }

    const filterBySignatory = type => {
      return signatoryList.value.filter((i) => {
        return i.type_id === parseInt(type)
      })
    }

    const openModal = (event, action, type) => {
      event.stopPropagation()
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
          memberList: [],
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

    const populateSignatory = async type => {
      const list = filterBySignatory(type)

      _message.loading('Loading...')

      for await ( const item of list) {
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
          memberList: item.memberList,
          position: item.position,
          isCustom: !item.office_id && !item.personnel_id,
        })
      }

      await _message.destroy()

      isOpenModal.value = true
    }

    const changeAction = (event, type) => {
      if (event === 'create') {
        modalTitle.value = 'New Signatory'
        okText.value = 'Create'
        action.value = 'create'
        addSignatory()
        isOpenModal.value = true
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
      memberList,

      yearList,
      signatoryTypes,
      offices,
      vpOfficesList,
      positionList,
      signatoryList,

      fetchSignatories,
      filterBySignatory,
      openModal,
      addSignatory,
      deleteSignatory,
      resetFormModal,
      officeDetails,
    }
  },
})
</script>
