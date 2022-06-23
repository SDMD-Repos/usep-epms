<template>
  <div>
    <a-spin :spinning="formLoading">
      <a-form-item>
        <a-row type="flex">
            <a-col :sm="{ span: 4 }" :md="{ span: 3 }" :lg="{ span: 2 }"><b>Office/College:</b></a-col>
             <a-col :sm="{ span: 12, offset: 1 }" :md="{ span: 10, offset: 1 }" :lg="{ span: 10, offset: 1 }">
              <a-tree-select
              v-model:value="officeId"
              style="width: 100%"
              :dropdown-style="{ maxHeight: '400px', overflow: 'auto' }"
              placeholder="Select Office/College"
              tree-node-filter-prop="title"
              :tree-data="vpOfficesList"
              show-search
              allow-clear
              label-in-value
              @change="getOfficeEmployee"
              v-if="opcrvpFormPermission"
              />
               <span v-else>{{ officeDetails.office_name ? officeDetails.office_name : "Not Set"}}</span>
        </a-col>
        </a-row>
        <a-row type="flex" class="mt-3">
            <a-col :sm="{ span: 4 }" :md="{ span: 3 }" :lg="{ span: 2 }"><b>Office Head: </b></a-col>
          <a-col :sm="{ span: 12, offset: 1 }" :md="{ span: 10, offset: 1 }" :lg="{ span: 10, offset: 1 }">
          <a-tree-select
            v-model:value="personnelId"
            style="width: 100%"
            :dropdown-style="{ maxHeight: '400px', overflow: 'auto' }"
            placeholder="Select Personnel"
            tree-node-filter-prop="title"
            show-search
            allow-clear
            label-in-value
            :tree-data="memberList"
             v-if="editBtn"
          />
             <span v-else>{{officeDetails ?  officeDetails.pmaps_name  ?  officeDetails.pmaps_name : "Not Set" : "Not Set"}}</span>
           </a-col>
        </a-row>
          <div class="mt-4"></div>
          <a-row type="flex" justify="center"  class="mt-3"  v-if="opcrvpFormPermission" >
           <a-col :sm="{ span: 12, offset: 1 }" :md="{ span: 10, offset: 1 }" :lg="{ span: 8, offset: 1 }">
                <a-button style="width: 90px;"   type="primary"  class="mr-3" v-if="editBtn" @click="onSave">Save</a-button>
                <a-button style="width: 90px;"  type="primary" class="mr-3"  v-if="editBtn" @click="onCancel">Cancel</a-button>
                <a-button style="width: 90px;"  type="primary" class="mr-3"  v-else @click="onEdit">Edit</a-button>
                </a-col>
          </a-row>
          <a-row type="flex" class="mt-3">
          <a-col :sm="{ span: 4 }" :md="{ span: 3 }" :lg="{ span: 2 }"><b>Staff Head: </b></a-col>
           <a-col :sm="{ span: 12, offset: 1 }" :md="{ span: 10, offset: 1 }" :lg="{ span: 10, offset: 1 }">
           <a-tree-select
            v-model:value="staffId"
            style="width: 100%"
            :dropdown-style="{ maxHeight: '400px', overflow: 'auto' }"
            placeholder="Select Personnel"
            tree-node-filter-prop="title"
            show-search
            allow-clear
            label-in-value
            :tree-data="memberListStaff"
             v-if="editBtnStaff"
          />
            <span v-else>{{ officeDetails ? officeDetails.staff_name ?  officeDetails.staff_name : "Not Set" : "Not Set"}}</span>
           </a-col>
          </a-row>
           <div class="mt-4"></div>
              <a-row type="flex" justify="center"  class="mt-3" v-if="vpopcrHeadPermission && officeDetails.pmaps_id ===  loginId ">
                  <a-col :sm="{ span: 12, offset: 1 }" :md="{ span: 10, offset: 1 }" :lg="{ span: 8, offset: 1 }">
              <a-button style="width: 90px;" type="primary" class="mr-3" v-if="editBtnStaff" @click="onSaveStaff">Save</a-button>
              <a-button style="width: 90px;" type="primary" class="mr-3" v-if="editBtnStaff" @click="onCancelStaff">Cancel</a-button>
              <a-button style="width: 90px;" type="primary" class="mr-3" v-else @click="onEditStaff">Edit</a-button>
              </a-col>
              </a-row>
               <a-col :sm="{ span: 4 }" :md="{ span: 3 }" :lg="{ span: 2 }"></a-col>
      </a-form-item>
    </a-spin>
  </div>
</template>

<script>
import {computed, defineComponent, ref, onMounted, watch} from 'vue'
import { useStore } from 'vuex'
import { Modal } from 'ant-design-vue'

import { getPersonnelByOffice } from '@/services/api/hris';
import { usePermission } from '@/services/functions/permission'

export default defineComponent({
  name: 'OpcrVpTab',
    setup() {
      const store = useStore()
      const vpOfficesList = computed(() => store.getters['external/external'].vpOffices)
      // const formAccessDetails = computed(() => store.getters['external/external'].formAccessDetails)
      const officeDetails = computed(()=>store.getters['system/permission'].officeHeadDetailsVPOPCR)
      const vpopcrHeadPermission = computed(() => store.getters['system/permission'].vpopcrHeadPermission)

      const officeId = ref(undefined)
      const headId = ref(undefined)
      const staffId = ref(undefined)
      const personnelId = ref(undefined)
      const loginId = store.state.user.pmapsId


      const memberListStaff = ref([])
      const memberList = ref([])

      let formLoading = ref(false)
      const editBtn = ref(false)
      const editBtnStaff = ref(false)
      const permission ={
                      listOpcrvp: ["adminPermission","ap-form", "apf-opcrvr"],
                    }
      const {
          // DATA
        opcrvpFormPermission,
          // METHODS
      } = usePermission(permission)

      // EVENTS
      watch(() => [officeDetails.value] , ([officeDetails]) => {
        if (officeDetails){
          officeId.value = {
            "label": officeDetails.office_name,
            "value": officeDetails.office_id,
          }
        }
      })

      const getPersonnelList = officeId => {
       memberList.value = []
          if (officeId) {
            formLoading.value = true
            const id = officeId.value
            getPersonnelByOffice(id).then(response => {
              if (response) {
                const { personnel } = response
                memberList.value = personnel.filter( data => { return data.id !== officeDetails.value.staff_id})
              }
              formLoading.value = false
            })
          }
      }

      const getStaffList =  officeId => {
       memberListStaff.value = []
      if (officeId) {
        formLoading.value = true
        const id = officeId.value
        getPersonnelByOffice(id).then(response => {
          if (response) {
            const { personnel } = response
            memberListStaff.value = personnel.filter( data => { return data.id !== officeDetails.value.pmaps_id})
          }
          formLoading.value = false
        })
      }
    }

      const getOfficeEmployee = officeId => {
        store.commit('system/SET_STATE',{officeHeadDetailsVPOPCR:[]})
        store.dispatch('system/FETCH_OFFICE_DETAILS',{payload:{form_id:'vpopcr',office_id:officeId}})
        staffId.value = []
      }

      const onSave = () => {
        let params = {
          office_id: officeId.value,
          pmaps_id: personnelId.value,
          form_id: 'vpopcr',

        }
        if(personnelId.value){
          store.dispatch('system/SAVE_FORM_HEAD',{ payload: params });
        }else{
          Modal.error({
          title: () => 'Unable to proceed',
          content: () => 'Please select a Office Head',
          })
        }
          editBtn.value = false;
      }

      const onCancel = () =>{
          editBtn.value = false;
          personnelId.value = {
            "label": officeDetails.value ? officeDetails.value.pmaps_name : "Not Set",
            "value": officeDetails.value ? officeDetails.value.pmaps_id : "Not Set",
          }
      }

      const onCancelStaff = () =>{
        editBtnStaff.value = false;
        staffId.value = {
          "label": officeDetails.value ? officeDetails.value.staff_name : "Not Set",
          "value": officeDetails.value ? officeDetails.value.id : "Not Set",
        }
      }

      const onEdit = () => {
        officeId.value = {
          "label": officeDetails.value.office_name,
          "value": officeDetails.value.office_id,
        }
        getPersonnelList(officeId.value)
        editBtn.value = true;
      }
       const onEditStaff = () => {
         officeId.value = {
           "label": officeDetails.value.office_name,
           "value": officeDetails.value.office_id,
         }
        getStaffList(officeId.value)
        editBtnStaff.value = true;
      }

      const onSaveStaff = () =>{
        let params = {
        pmaps_id: staffId.value,
        form_id: 'vpopcr',
        office_id: officeId.value,
        }

        if(staffId.value){
            store.dispatch('system/SAVE_FORM_STAFF',{ payload: params });

        }else{
            Modal.error({
              title: () => 'Unable to proceed',
              content: () => 'Please select a Office Staff',
            })
        }
         editBtnStaff.value=false
      }


      onMounted( () => {
        store.dispatch('system/FETCH_OFFICE_DETAILS',{payload:{form_id:'vpopcr',office_id:null}})
        store.dispatch('external/FETCH_VP_OFFICES', { payload: { officesOnly: 1 } })
        store.dispatch('system/CHECK_VPOPCR_HEAD_PERMISSION', {
                                                          payload: {
                                                                    pmaps_id: store.state.user.pmapsId,
                                                                    form_id: 'vpopcr',
                                                                     },
                                                            })
      })

    return  {
      officeId,
      headId,
      staffId,
      memberList,
      vpOfficesList,
      personnelId,
      formLoading,
      memberListStaff,
      editBtn,
      officeDetails,
      editBtnStaff,
      opcrvpFormPermission,
      vpopcrHeadPermission,
      loginId,

      onSave,
      onSaveStaff,
      onEdit,
      onCancel,
      onEditStaff,
      onCancelStaff,

      getOfficeEmployee,

      }
    },
})
</script>
