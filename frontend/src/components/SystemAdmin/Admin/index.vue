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
            />
        <a-table  :default-expand-all-rows="true" row-key="id" :columns="columns" :data-source="data" :row-selection="{ selectedRowKeys: selectedRowKeys, onChange: onSelectChange }" />
      </a-spin>
    <a-button type="primary" @click="onOk" >Save</a-button>
    </div>
    
</template>
<script>
import { computed, defineComponent, onMounted, ref  } from 'vue';
import { useStore } from 'vuex'
import { getPersonnelByOffice } from '@/services/api/hris';

const columns = [
  {
    title: 'name',
    dataIndex: 'permission',
    key: 'permission',
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
    const selectedRowKeys = ref([])
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

    const onOk = () => {
        //  console.log(selectedRowKeys.value)
        //  console.log(personnelId.value)
         let params = {
                  personnelId: personnelId.value.value,
                  listPermissions : selectedRowKeys.value,
         }
        store.dispatch('system/SAVE_PERMISSION',{ payload: params })


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
      onOk,
    };
  },
});
</script>
