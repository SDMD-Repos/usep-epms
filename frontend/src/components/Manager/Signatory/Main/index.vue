<template>
  <div>
    <a-select v-model:value="year" placeholder="Select year" style="width: 200px" @change="fetchSignatories">
      <template v-for="(y, i) in yearList" :key="i">
        <a-select-option :value="y">
          {{ y }}
        </a-select-option>
      </template>
    </a-select>
    <div class="mt-4">
      <a-collapse v-model:activeKey="activeKey" accordion>
        <a-collapse-panel v-for="(type, key) in signatoryTypes" :header="type.name" :key="`${key}`">
          <signatory-list :year="year"
                          :form-id="formId"
                          :list="filterBySignatory(type.id)"
                          :loading="loading" />
          <template #extra>
            <user-add-outlined v-if="!filterBySignatory(type.id).length" @click="openModal($event, 'create', type.id)"/>
            <edit-outlined v-else @click="openModal($event, 'update', type.id)" />
          </template>
        </a-collapse-panel>
      </a-collapse>

      <form-modal :visible="isOpenModal"
                  :modal-title="modalTitle"
                  :ok-text="okText"
                  :action-type="action"
                  :office-list="offices"
                  :position-list="positionList"
                  :form-state="formState"
                  @close-modal="resetFormModal"/>
    </div>
  </div>
</template>
<script>
import { computed, defineComponent, ref, watch, reactive, onMounted } from 'vue'
import { useStore } from 'vuex'
import SignatoryList from './partials/list'
import FormModal from './partials/formModal'
import { UserAddOutlined, EditOutlined } from '@ant-design/icons-vue'

export default defineComponent({
  components: {
    SignatoryList,
    FormModal,
    UserAddOutlined,
    EditOutlined,
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

    // COMPUTED
    const signatoryTypes = computed(() => store.getters['formManager/manager'].signatoryTypes)
    const offices = computed(() => store.getters['external/external'].mainOfficesChildren)
    const positionList = computed(() => store.getters['external/external'].positionList)

    const yearList = computed(() => {
      const now = new Date().getFullYear()
      const min = 10
      const lists = []
      for (let i = now; i >= (now - min); i--) {
        lists.push(i)
      }
      return lists
    })

    const signatoryList = computed(() => store.getters['formManager/manager'].signatories)
    const loading = computed(() => store.getters['external/external'].loading)

    // EVENTS
    onMounted(() => {
      let params = {
        selectable: {
          allColleges: true,
          mains: true,
        },
        isAcronym: false,
      }
      params = encodeURIComponent(JSON.stringify(params))
      store.dispatch('external/FETCH_MAIN_OFFICES_CHILDREN', { payload: params })
      store.dispatch('formManager/FETCH_ALL_SIGNATORY_TYPES')
      store.dispatch('external/FETCH_ALL_POSITIONS')
      fetchSignatories()
    })

    watch(() => props.formName , (formName) => {
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
      // if (formId.value === 'vpopcr') {
      //   const { office } = this
      //   if (typeof office !== 'undefined') {
      //     data.officeId = office
      //     this.$store.dispatch('formManager/FETCH_YEAR_SIGNATORIES', { payload: data })
      //   }
      // } else {
      store.dispatch('formManager/FETCH_YEAR_SIGNATORIES', { payload: data })
      // }
    }

    const filterBySignatory = type => {
      return signatoryList.value.filter((i) => {
        return i.type_id === type
      })
    }

    const openModal = (event, action, type) => {
      event.stopPropagation()
      console.log(action, type)
      isOpenModal.value = true
      changeAction(action)
    }

    const changeAction = event => {
      if (event === 'create') {
        modalTitle.value = 'New Signatory'
        okText.value = 'Create'
        action.value = 'create'
        formState.signatories.push({
          id: 'new',
          officeId: undefined,
          personnelId: undefined,
          position: undefined,
          isCustom: false,
        })
      } else if (event === 'update') {
        modalTitle.value = 'Update Signatory'
        okText.value = 'Update'
        action.value = 'update'
      }
    }

    const resetFormModal = () => {
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

      yearList,
      signatoryTypes,
      offices,
      positionList,
      signatoryList,
      loading,

      fetchSignatories,
      filterBySignatory,
      openModal,
      resetFormModal,
    }
  },
})
</script>
