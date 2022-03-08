<template>
    <div>
      <a-spin :spinning="formLoading">
        <a-tree-select
              v-model:value="officeId"
              style="width: 100%"
              :dropdown-style="{ maxHeight: '400px', overflow: 'auto' }"
              :tree-data="offices"
              placeholder="Select Office"
              tree-node-filter-prop="title"
              show-search
              allow-clear
              label-in-value
              @change="getPersonnelList($event, index)"
            />
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
              @change="getAccessList($event, index)"
            />
        <a-table  :default-expand-all-rows="true" row-key="id" :columns="columns" :data-source="data" :row-selection="{ selectedRowKeys: selectedRowKeys, onChange: onSelectChange }" />
      </a-spin>
    <a-button v-if="updateBtn" type="primary" @click="onSave" >Save</a-button>
    <a-button v-else type="primary" @click="onUpdate" >Update</a-button>
    </div>
    
</template>
<script>
import { computed, defineComponent, onMounted, ref  } from 'vue';
import { useStore } from 'vuex'
import { getPersonnelByOffice } from '@/services/api/hris';
import { getAccessByUser } from '@/services/api/system/permission';
import { stubFalse } from 'lodash';

const columns = [
  {
    title: 'name',
    dataIndex: 'permission_name',
    key: 'permission_name',
  },
  
];
 
export default defineComponent({
  name:"AccessRightsTable",
  setup() {
    const store = useStore()
    const list = computed(() => store.getters['system/permission'].list)
    const offices = computed(() => store.getters['external/external'].mainOfficesChildren)
    const officeId = ref(undefined)
    const personnelId = ref(undefined)
    const memberList = ref([])
    const accessList = ref([])
    const selectedRowKeys = ref([])
    const updateBtn = ref(true)

    let formLoading = ref(false)
    const onSelectChange = selected => {
      selectedRowKeys.value = selected;
    };
    const getPersonnelList = (officeId, index) => {
      memberList.value = []
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

     const getAccessList = (personnelId, index) => {
       accessList.value = []
      if (personnelId) {
        formLoading.value = true
        const id = personnelId.value
        getAccessByUser(id).then(response => {
          if (response.status) {
            response.accessLists.forEach((accessList) =>{
            selectedRowKeys.value.push(accessList.access_right_id);

           
            })
            updateBtn.value = false ;
          }else{
            selectedRowKeys.value = [];
            updateBtn.value = true ;
          }
          formLoading.value = false
        })
      }
    }

    onMounted(() => {
       let params = {
        selectable: {
          allColleges: false,
          mains: true,
        },
        isAcronym: false,
      }
      params = encodeURIComponent(JSON.stringify(params))
      store.dispatch('external/FETCH_MAIN_OFFICES_CHILDREN', { payload: params })
      store.dispatch('system/FETCH_PERMISSION')
    })

    const onSave = () => {
         let params = {
                  personnelId: personnelId.value.value,
                  listPermissions : selectedRowKeys.value,
         }
        store.dispatch('system/SAVE_PERMISSION',{ payload: params })
    }
    const onUpdate = () => {
         let params = {
                  personnelId: personnelId.value.value,
                  listPermissions : selectedRowKeys.value,
         }
        store.dispatch('system/UPDATE_PERMISSION',{ payload: params })
    }

    return {
      data: list,
      columns,
      offices,
      getPersonnelList,
      officeId,
      personnelId,
      memberList,
      selectedRowKeys,
      onSelectChange,
      formLoading,
      onSave,
      getAccessList,
      accessList,
      updateBtn,
      onUpdate,
    };
  },
});
</script>
