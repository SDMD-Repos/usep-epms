<template>
    <div>
      <a-spin :spinning="loading">
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
            />
          </a-col>
        </a-row>
        <a-row type="flex" class="mt-3">
          <a-col :sm="{ span: 4 }" :md="{ span: 3 }" :lg="{ span: 2 }"><b>Personnel: </b></a-col>
          <a-col :sm="{ span: 12, offset: 1 }" :md="{ span: 10, offset: 1 }" :lg="{ span: 10, offset: 1 }">
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
              @change="getAccessList"
            />
          </a-col>
        </a-row>

        <div class="mt-4" v-if="data.length">
          <!-- <a-table  class="ant-table-striped"  :default-expand-all-rows="true" row-key="id" :columns="columns" :data-source="data"
              :row-selection="{ selectedRowKeys: selectedRowKeys, onChange: onSelectChange }" :pagination="false" /> -->
          <a-tree
          :show-line="true"
          :show-icon="false"
          checkable
          :tree-data="data"
          v-model:checkedKeys="checkedKeys"
          @check="onCheck"
          :field-names="fieldNames"
        >
         <template #title="{ permission_name}">
             <span>{{ permission_name }}</span>
           </template>
        </a-tree>
        </div>

        <div class="mt-4"></div>
        <a-row type="flex" justify="center" align="middle">
          <a-button v-if="updateBtn && savebtn" type="primary" @click="onSave" >Save</a-button>
          <a-button v-else-if="!updateBtn && savebtn" type="primary" @click="onUpdate" >Update</a-button>
        </a-row>
      </a-spin>
    </div>
</template>
<script>
import { defineComponent, onMounted, ref, computed } from 'vue';
import { useStore } from 'vuex'
import { getPersonnelByOffice } from '@/services/api/hris';
import { getAccessByUser } from '@/services/api/system/permission';

const columns = [
  {
    title: 'Access Permission',
    dataIndex: 'permission_name',
    key: 'permission_name',
  },
];

export default defineComponent({
  name:"AccessRightsTable",
  setup() {
    const store = useStore()

    const officeId = ref(undefined)
    const personnelId = ref(undefined)
    const memberList = ref([])
    const accessList = ref([])
    const checkedKeys = ref([])
    const updateBtn = ref(true)
    const savebtn = ref(false)

    let formLoading = ref(false)

    const list = computed(() => store.getters['system/permission'].list)
    const loading = computed(() => store.getters['system/permission'].loading)
    const offices = computed(() => store.getters['external/external'].mainOfficesChildren)
    const vpOffices = computed(() => store.getters['external/external'].getVpOfficeChildren)

    const fieldNames = {
      children: 'children',
      title: 'permission_name',
    };

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
      store.dispatch('external/FETCH_VP_OFFICES_CHILDREN')
      store.dispatch('system/FETCH_PERMISSION')
    })

    

    const getPersonnelList = officeId => {
      memberList.value = []
      personnelId.value = []
      if (officeId) {
        store.dispatch('system/FETCH_PERMISSION')
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

    const getAccessList = personnelId => {
      accessList.value = []
      checkedKeys.value = []
      if (personnelId) {
        formLoading.value = true
         savebtn.value = true
        const id = personnelId.value
        getAccessByUser(id).then(response => {
          if (response.status) {
            response.accessLists.forEach((accessList) =>{
              checkedKeys.value.push(accessList.access_right_id);
            })
            updateBtn.value = false ;
          }else{
            checkedKeys.value = [];
            updateBtn.value = true ;
          }
          formLoading.value = false
        })
      }else{
        savebtn.value = false
      }
    }

    const onSave = () => {
       let params = {
         personnelId: personnelId.value.value,
         listPermissions : checkedKeys.value,
       }

      store.dispatch('system/SAVE_PERMISSION',{ payload: params })
    }
    const onUpdate = () => {
      let params = {
        personnelId: personnelId.value.value,
        listPermissions : checkedKeys.value,
      }
      store.dispatch('system/UPDATE_PERMISSION',{ payload: params })
    }

    const onCheck = (data, { checkedNodes }) => {
      checkedNodes.forEach((checkedNode) => {
        if (typeof checkedNode.props.dataRef.children !== "undefined") {
          const { children } = checkedNode.props.dataRef;
          checkedKeys.value = checkedKeys.value.filter(
            (o1) => !children.some((o2) => o1 === o2.key),
          );
        }
      });
    };

    return {
      data: list,
      columns,
      offices,
      vpOffices,
      officeId,
      personnelId,
      memberList,
      fieldNames,
      checkedKeys,
      formLoading,
      accessList,
      updateBtn,
      loading,

      getPersonnelList,
      // onSelectChange,
      onSave,
      getAccessList,
      onUpdate,
      savebtn,
      onCheck,
    };
  },
});
</script>

<style scoped>
.ant-table-striped :deep(.table-striped) td {
  background-color: #fafafa;
}
</style>
