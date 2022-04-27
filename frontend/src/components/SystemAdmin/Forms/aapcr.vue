<template>
   <div>
<a-spin :spinning="formLoading">
             <a-form-item >
                <a-row type="flex">
                  <a-col :sm="{ span: 4 }" :md="{ span: 3 }" :lg="{ span: 2 }"><b>Office/College:</b></a-col>
                  <a-col :sm="{ span: 12, offset: 1 }" :md="{ span: 10, offset: 1 }" :lg="{ span: 10, offset: 1 }">
                    <a-tree-select
                      v-model:value="officeId"
                      style="width: 100%"
                      :dropdown-style="{ maxHeight: '400px', overflow: 'auto' }"
                      :tree-data="offices"
                      placeholder="Select Office/College"
                      tree-node-filter-prop="title"
                      show-search
                      allow-clear
                      label-in-value
                      @change="getPersonnelList"
                      v-if="editBtn"
                    />
                    <span v-else>{{officeDetails ? officeDetails.office_name  : "Not Set"}}</span>
                  </a-col>
                </a-row>

                <a-row type="flex" class="mt-3">
                  <a-col :sm="{ span: 3 }" :md="{ span: 2 }" :lg="{ span:2 }"><b>Office Head: </b></a-col>
                  <a-col :sm="{ span: 12, offset: 1 }" :md="{ span: 10, offset: 1 }" :lg="{ span: 10, offset: 1 }" >
                      <a-tree-select
                        v-model:value="personnelId"
                        style="width: 100%"
                        :dropdown-style="{ maxHeight: '400px', overflow: 'auto' }"
                        :tree-data="memberList"
                        placeholder="Select Personnel"
                        tree-node-filter-prop="title"
                        show-search
                        allow-clear
                        label-in-value
                        v-if="editBtn"
                      />
                     <span v-else>{{officeDetails ? officeDetails.pmaps_name  : "Not Set"}}</span>
                  </a-col>

                </a-row>

                 <a-row type="flex" justify="center" class="mt-3" v-if="aapcrFormPermission">
                   <a-col :sm="{ span: 12, offset: 1 }" :md="{ span: 10, offset: 1 }" :lg="{ span: 8, offset: 1 }">
                      <a-button style="width: 100px;" type="primary" class="mr-3" @click="onSave" v-if="editBtn" > Save</a-button>
                      <a-button style="width: 100px;" type="primary" v-if="editBtn" @click="onCancel">Cancel</a-button>
                      <a-button style="width: 100px;" type="primary" class="mr-3" @click="onEdit"  v-else>Edit</a-button>
                   </a-col>
              </a-row>
                <a-divider />
                <a-row type="flex" class="mt-3">
                  <a-col :sm="{ span: 3 }" :md="{ span: 2 }" :lg="{ span:2 }"><b>Staff Head: </b></a-col>
                  <a-col :sm="{ span: 12, offset: 1 }" :md="{ span: 10, offset: 1 }" :lg="{ span: 10, offset: 1 }" >
                      <a-tree-select
                        v-model:value="staffId"
                        style="width: 100%"
                        :dropdown-style="{ maxHeight: '400px', overflow: 'auto' }"
                        :tree-data="memberListStaff"
                        placeholder="Select Personnel"
                        tree-node-filter-prop="title"
                        show-search
                        allow-clear
                        label-in-value
                        v-if="editBtnStaff"
                      />
                      <span v-else>{{officeDetails ? officeDetails.staff_name ? officeDetails.staff_name : "Not Set"  : "Not Set"}}</span>
                  </a-col>
                </a-row>
                 <a-row type="flex" justify="center" class="mt-3" v-if="aapcrHeadPermission">
                   <a-col :sm="{ span: 12, offset: 1 }" :md="{ span: 10, offset: 1 }" :lg="{ span: 8, offset: 1 }">
                     <a-button style="width: 100px;" type="primary" class="mr-3" @click="onSaveStaff" v-if="editBtnStaff" >Save</a-button>
                      <a-button style="width: 100px;" type="primary" class="mr-3" @click="onCancelStaff" v-if="editBtnStaff" >Cancel</a-button>
                   <a-button style="width: 100px;" type="primary" class="mr-3" @click="onEditStaff" v-else >Edit Staff</a-button>
                   </a-col>
              </a-row>
                <a-col :sm="{ span: 4 }" :md="{ span: 3 }" :lg="{ span: 2 }"></a-col>
        </a-form-item>
</a-spin>
    </div>
</template>

<script>
import { computed, defineComponent, onMounted, ref , watch} from 'vue'
import { useStore } from 'vuex'
import { Modal } from 'ant-design-vue'
import { getPersonnelByOffice } from '@/services/api/hris';
import { usePermission } from '@/services/functions/permission'

export default defineComponent({
  name: 'AapcrTab',
  components: {},
  setup() {
    const store = useStore()
    const offices = computed(() => store.getters['external/external'].mainOfficesChildren)
    const loading = computed(() => store.getters['external/external'].loading)
    const officeDetails = computed(()=>store.getters['system/permission'].officeHeadDetailsAAPCR)
  
    const aapcrHeadPermission = computed(() => store.getters['system/permission'].aapcrHeadPermission)

  
 
    const officeId = ref(undefined)

    const editBtn = ref(false)
    const editBtnStaff = ref(false)

    const memberList = ref([])
    const memberListStaff = ref([])
    const personnelId = ref(undefined)
    const staffId = ref(undefined)

    let formLoading = ref(false)

    const permission ={
                      listAapcr: ["adminPermission","ap-form", "apf-aapcr"],
                    }
     const {
          // DATA
        aapcrFormPermission,
          // METHODS
      } = usePermission(permission)

     onMounted(() => {
      store.dispatch('system/FETCH_OFFICE_DETAILS',{payload:{form_id:'aapcr',office_id:null}})
      store.dispatch('system/CHECK_APCR_HEAD_PERMISSION', { 
                                                          payload: {
                                                                    pmaps_id: store.state.user.pmapsId,
                                                                    form_id:'aapcr',
                                                                     },
                                                            })
    
    })
    

    const getPersonnelList = officeId => {
      memberList.value = []
      personnelId.value = []
      if (officeId) {
        formLoading.value = true
        const id = officeId.value
        getPersonnelByOffice(id).then(response => {
          if (response) {
            const { personnel } = response
            memberList.value = personnel
          }
          formLoading.value = false
        })
      }
    }

    const getStaffList =  staffOfficeId => {
       memberListStaff.value = []
      if (staffOfficeId) {
        formLoading.value = true
        const id = staffOfficeId
        getPersonnelByOffice(id).then(response => {
          if (response) {
            const { personnel } = response
            memberListStaff.value = personnel
          }
          formLoading.value = false
        })
      }
    }
    const onSave = () =>{
      let params = {
       pmaps_id: personnelId.value,
       form_id: 'aapcr',
       office_id: officeId.value,
      }
      if(personnelId.value){
            store.dispatch('system/SAVE_FORM_HEAD',{ payload: params });
            editBtn.value = false;
      }else{
        Modal.error({
          title: () => 'Unable to proceed',
          content: () => 'Please select a Office Head',
        })
      }
    }

    const onCancel = () =>{
          editBtn.value = false;
          officeId.value = []
          personnelId.value = []
          staffId.value = []
          memberList.value = []
          store.commit('system/SET_STATE',{officeHeadDetailsVPOPCR:[]})
      }


    const onCancelStaff = () =>{
          editBtnStaff.value = false;
          officeId.value = []
          personnelId.value = []
          staffId.value = []
          memberList.value = []
          store.commit('system/SET_STATE',{officeHeadDetailsVPOPCR:[]})
      }

    const onSaveStaff = () =>{
      let params = {
       pmaps_id: staffId.value,
       form_id: 'aapcr',
       office_id:  {"value":officeDetails.value.office_id},
       }

      if(staffId.value){
            store.dispatch('system/SAVE_FORM_STAFF',{ payload: params });
            editBtnStaff.value = false;
      }else{
        Modal.error({
          title: () => 'Unable to proceed',
          content: () => 'Please select a Office Staff',
        })
      }
    }
    const onEdit = () =>{
      editBtn.value = true;
    }
    const onEditStaff = () =>{
      getStaffList(officeDetails.value.office_id)
     
      editBtnStaff.value = true;
    }

    

    return {
      offices,
      officeId,
      memberList,
      personnelId,
      getPersonnelList,
      loading,
      memberListStaff,
      officeDetails,
      // officeHeadPmapsId,
      // officeHeadOfficeId,
      // officeHeadOfficeName,
      formLoading,
      onSave,
      onSaveStaff,
      editBtn,
      editBtnStaff,
      onEdit,
      onEditStaff,
      onCancel,
      onCancelStaff,
      // isCreate,
      // allAccess,
      aapcrFormPermission,
      aapcrHeadPermission,
      getStaffList,
      staffId,
    }
  },
})
</script>
