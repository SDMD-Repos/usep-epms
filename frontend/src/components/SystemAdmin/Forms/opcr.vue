<template>
  <a-spin :spinning="loading">
  <div>
    <a-row type="flex">
      <a-col :sm="{ span: 4 }" :md="{ span: 3 }" :lg="{ span: 2 }"><b>Office/College:</b></a-col>
      <a-col :sm="{ span: 12, offset: 1 }" :md="{ span: 10, offset: 1 }" :lg="{ span: 10, offset: 1 }">
        <a-tree-select
          v-model:value="officeId"
          style="width: 100%"
          :dropdown-style="{ maxHeight: '400px', overflow: 'auto' }"
          :tree-data="vpOffices"
          placeholder="Select Office/College"
          tree-node-filter-prop="title"
          show-search
          allow-clear
          label-in-value
          @change="fetchPersonnelList"
        />
      </a-col>
    </a-row>
    <a-row type="flex" class="mt-3">
      <a-col :sm="{ span: 4 }" :md="{ span: 3 }" :lg="{ span: 2 }"><b>Office Head: </b></a-col>
      <a-col :sm="{ span: 12, offset: 1 }" :md="{ span: 10, offset: 1 }" :lg="{ span: 10, offset: 1 }">
        <a-tree-select
          v-model:value="headId"
          style="width: 100%"
          :dropdown-style="{ maxHeight: '400px', overflow: 'auto' }"
          :tree-data="personnelList"
          placeholder="Select Personnel"
          tree-node-filter-prop="title"
          show-search
          allow-clear
          label-in-value
          v-if="editBtnHead"
        />
        <span v-else>{{ typeof headId === 'object'  ? headId.label : "" }}</span>
      </a-col>
    </a-row>
    <div class="mt-4"></div>
    <a-row type="flex" justify="center" align="middle">
      <a-button style="width: 90px;" type="primary" @click="onSaveHead" v-if="editBtnHead">Save</a-button>
      <a-button style="width: 90px;" type="primary" @click="onEditHead" v-else>Edit</a-button>
    </a-row>
    <a-row type="flex" class="mt-3">
      <a-col :sm="{ span: 4 }" :md="{ span: 3 }" :lg="{ span: 2 }"><b>Staff Head: </b></a-col>
      <a-col :sm="{ span: 12, offset: 1 }" :md="{ span: 10, offset: 1 }" :lg="{ span: 10, offset: 1 }">
        <a-tree-select
          v-model:value="staffId"
          style="width: 100%"
          :dropdown-style="{ maxHeight: '400px', overflow: 'auto' }"
          :tree-data="personnelList"
          placeholder="Select Personnel"
          tree-node-filter-prop="title"
          show-search
          allow-clear
          label-in-value
          v-if="editBtnStaff"
        />
        <span v-else>{{ typeof staffId === 'object' ? staffId.label : "" }}</span>
      </a-col>
    </a-row>
    <div class="mt-4"></div>
    <a-row type="flex" justify="center" align="middle">
      <a-button style="width: 90px;" type="primary" @click="onSaveStaff" v-if="editBtnStaff" >Save</a-button>
      <a-button style="width: 90px;" type="primary" @click="onEditStaff" v-else>Edit</a-button>
    </a-row>
  </div>
  </a-spin>
</template>

<script>
import { computed, defineComponent, onMounted, ref } from 'vue'
import { useStore } from 'vuex'
import { Modal } from 'ant-design-vue'
export default defineComponent({
  name: 'OpcrTab',
  components: {},
  setup() {
    const store = useStore()
    const form_id = ref('opcr')
    const officeId = ref(undefined)
    const headId = ref(undefined)
    const staffId = ref(undefined)
    const loading = computed(() => store.getters['external/external'].loading)
    const vpOffices = computed(() => store.getters['external/external'].vpOfficeChildren)
    const personnelList = computed(() => store.getters['external/external'].personnel)
    const formAccessDetails = computed(() => store.getters['external/external'].formAccessDetails)

    const editBtnHead = ref(false)
    const editBtnStaff = ref(false)

    const fetchPersonnelList  = officeId => {
      const id = officeId.value
      store.dispatch('external/GET_OFFICE_DETAILS', { payload: id })
      store.dispatch('external/FETCH_PERSONNEL_BY_OFFICE', { payload: id })

      headId.value = { label: computed(() => formAccessDetails.value ? formAccessDetails.value.pmaps_name : ""), value: computed(() => formAccessDetails.value ? formAccessDetails.value.pmaps_id : "")}
      staffId.value = { label: computed(() => formAccessDetails.value ? formAccessDetails.value.staff_name : ""), value: computed(() => formAccessDetails.value ? formAccessDetails.value.staff_id : "")}
    }

    const validateFields = (fields) => {
      return Object.values(fields).every(value => {
        if(Array.isArray(value)) return value.length > 0
        if (typeof value === 'object') return !!value.value
        return !!value
      })
    }

    const onSaveHead = () => {
      const headParams = {
        pmaps_id: headId.value,
        form_id: form_id.value,
        office_id: officeId.value,
      }
      if(validateFields(headParams)){
        store.dispatch('system/SAVE_FORM_HEAD',{ payload: headParams })
        editBtnHead.value = false
      }else
        Modal.error({
          title: () => 'Unable to proceed',
          content: () => 'Please select an Office Head',
        })
    }

    const onSaveStaff = () => {
      const staffParams = {
        pmaps_id: staffId.value,
        form_id: form_id.value,
        office_id: officeId.value,
      }
      if(validateFields(staffParams)){
        store.dispatch('system/SAVE_FORM_STAFF',{ payload: staffParams })
        editBtnStaff.value = false
      }else
        Modal.error({
          title: () => 'Unable to proceed',
          content: () => 'Please select an Office Staff',
        })
    }

    const onEditHead = () =>{
      editBtnHead.value = true;
    }

    const onEditStaff = () =>{
      editBtnStaff.value = true;
    }

    return {
      headId,
      staffId,
      officeId,

      personnelList,
      vpOffices,
      loading,
      formAccessDetails,
      editBtnHead,
      editBtnStaff,

      fetchPersonnelList,
      onSaveHead,
      onSaveStaff,
      onEditHead,
      onEditStaff,
    }
  },
})
</script>
