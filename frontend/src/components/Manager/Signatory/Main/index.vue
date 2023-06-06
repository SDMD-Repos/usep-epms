<template>
  <div>
    <a-row type="flex">
      <a-col :sm="{ span: 4 }" :md="{ span: 3 }" :lg="{ span: 2 }"><b>Fiscal Year:</b></a-col>
      <a-col :sm="{ span: 12, offset: 1 }" :md="{ span: 4, offset: 1 }" :lg="{ span: 3, offset: 1 }">
        <a-select v-model:value="year" placeholder="Select year" style="width: 200px" @change="fetchSignatories">
          <template v-for="(y, i) in yearList" :key="i">
            <a-select-option :value="y">
              {{ y }}
            </a-select-option>
          </template>
        </a-select>
      </a-col>
    </a-row>

    <div class="w-100 mt-2"></div>

    <a-row type="flex">
      <a-col :sm="{ span: 4 }" :md="{ span: 3 }" :lg="{ span: 2 }" v-if="formId !== 'aapcr'"><b>Office :</b></a-col>
      <a-col :sm="{ span: 12, offset: 1 }" :md="{ span: 10, offset: 1 }" :lg="{ span: 10, offset: 1 }">
        <div class="mt-2" v-if="formId === 'vpopcr' || formId === 'opcr'">
          <a-tree-select
            v-if="offices.length > 1"
            v-model:value="selectedOffice" :dropdown-style="{ maxHeight: '400px', overflow: 'auto' }"
            style="width: 450px" placeholder="Select Office/College" tree-node-filter-prop="title" :tree-data="offices"
            tree-default-expand-all show-search allow-clear label-in-value
            @change="fetchSignatories"
          />
          <span v-else>{{ selectedOffice ? selectedOffice.label : "Not Set"}}</span>
        </div>
      </a-col>
    </a-row>

    <div class="mt-4">
      <a-collapse v-model:activeKey="activeKey">
        <a-collapse-panel v-for="(type, key) in signatoryTypes" :header="type.name" :key="`${key}`">
          <signatory-list
            :year="year" :form-id="formId" :list="filterBySignatory(type.id)" :office-id="selectedOffice"/>
          <template #extra v-if="formId !== 'vpopcr' || (formId === 'vpopcr' && typeof selectedOffice !== 'undefined')">
            <user-add-outlined v-if="!filterBySignatory(type.id).length" @click="openModal($event, 'create', type.id)"/>
            <edit-outlined v-else @click="openModal($event, 'update', type.id)" />
          </template>
        </a-collapse-panel>
      </a-collapse>
    </div>

    <form-modal
      :visible="isOpenModal" :modal-title="modalTitle" :ok-text="okText" :action-type="action" :office-list="mainOfficesChildren"
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
    formName: { type: String, default: '' },
    formAccess: { type: Array, default: () => { return [] } },
  },
  setup(props) {
    const store = useStore()

    const _message = inject('a-message')

    // DATA
    const year = ref(new Date().getFullYear())
    const isOpenModal = ref(false)
    const selectedOffice = ref(undefined)
    const memberList = ref([])
    const signatoryDetails = ref({})

    const formState = reactive({
      signatories: [],
    })

    let formId = ref(props.formName)
    let action = ref('')
    let modalTitle = ref('')
    let okText = ref('')

    // COMPUTED
    const signatoryTypes = computed(() => store.getters['formManager/manager'].signatoryTypes)
    const positionList = computed(() => store.getters['external/external'].positionList)
    const vpOfficesList = computed(() => store.getters['external/external'].vpOffices)
    const mainOfficesChildren = computed(() => store.getters['external/external'].mainOfficesChildren)
    const signatoryList = computed(() => store.getters['formManager/manager'].signatories)

    const filteredFormAccess = computed(() => props.formAccess.filter(e => e.form_id === formId.value) )

    const offices = computed(() => {
      let list = []
      if(filteredFormAccess.value.length > 0) {
        filteredFormAccess.value.forEach(e => {
          list.push({ value: parseInt(e.office_id), title: e.office_name })
        })
      }else {
        switch (formId.value) {
          case 'vpopcr':
            list = vpOfficesList.value
            break
          case 'opcr':
            list = mainOfficesChildren.value
            break
        }
      }

      return list
    })

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
      await fetchOfficeList()
      await store.dispatch('formManager/FETCH_ALL_SIGNATORY_TYPES')
      await store.dispatch('external/FETCH_ALL_POSITIONS')

      let params = {
        isAcronym: false,
        isOfficesOnly: true,
        selectable: { allColleges: false, mains: true },
      }
      await store.dispatch('external/FETCH_MAIN_OFFICES_CHILDREN', { payload: params })
    })

    onMounted(() => {
      fetchSignatories()
    })

    watch(() => props.formName , formName => {
      formId.value = formName
    })

    // METHODS
    const fetchOfficeList = () => {
      if(filteredFormAccess.value.length < 1) {
        let params = {
          isAcronym: false,
          isOfficesOnly: true,
        }

        switch (formId.value){
          case 'vpopcr':
            store.dispatch('external/FETCH_VP_OFFICES', { payload: { officesOnly: 1 } })
            break
          default:
            break
        }
      }else {
        if(filteredFormAccess.value.length === 1) {
          const { title, value } = offices.value[0]
          selectedOffice.value = { label: title, value: value }
        }
      }
    }

    const fetchSignatories = () => {
      const data = {
        year: year.value,
        formId: formId.value,
        officeId: 0,
      }

      store.commit('formManager/SET_STATE', {
        signatories: [],
      })

      switch (formId.value){
        case 'vpopcr':
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
    }

    const filterBySignatory = type => {
      return signatoryList.value.filter((i) => {
        return i.type_id === parseInt(type)
      })
    }

    const openModal = (event, action, type) => {
      event.stopPropagation()
      switch (formId.value){
        case 'vpopcr':
        case 'opcr':
          if (selectedOffice.value){
            signatoryDetails.value = {
              typeId: type,
              year: year.value,
              formId: formId.value,
              office: selectedOffice.value.value,
            }
          }
          break
        default:
          signatoryDetails.value = {
            typeId: type,
            year: year.value,
            formId: formId.value,
          }
          break
      }


      changeAction(action,type)
    }

    const addSignatory = () => {
      if(formState.signatories.length < 3) {
        formState.signatories.push({
          id: 'new',
          officeId: undefined,
          isSubunit: 0,
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
            value: !item.is_subunit ? parseInt(item.office_id) : item.office_id,
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
          isSubunit: item.is_subunit,
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
      isOpenModal,
      selectedOffice,
      memberList,
      signatoryDetails,
      formState,
      formId,
      action,
      modalTitle,
      okText,

      signatoryTypes,
      positionList,
      vpOfficesList,
      mainOfficesChildren,
      signatoryList,
      offices,
      yearList,

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
