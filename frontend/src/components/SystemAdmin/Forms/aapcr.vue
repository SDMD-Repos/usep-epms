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
                    />
                  </a-col>
                </a-row>
                <a-row type="flex" class="mt-3">
                  <a-col :sm="{ span: 3 }" :md="{ span: 2 }" :lg="{ span:2 }"><b>Personnel: </b></a-col>
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
                      />
                     
                  </a-col>
                </a-row>
                 <a-row type="flex" justify="center" class="mt-3">
                   <a-col :sm="{ span: 12, offset: 1 }" :md="{ span: 10, offset: 1 }" :lg="{ span: 8, offset: 1 }">
                   <a-button style="width: 100px;" type="primary" class="mr-3" @click="onSave" > Save</a-button>
                   </a-col>
              </a-row>
                <a-col :sm="{ span: 4 }" :md="{ span: 3 }" :lg="{ span: 2 }"></a-col>
        </a-form-item>
</a-spin>
    </div>
</template>

<script>
import { computed, defineComponent, onMounted, ref } from 'vue'
import { useStore } from 'vuex'
import { getPersonnelByOffice } from '@/services/api/hris';
export default defineComponent({
  name: 'AapcrTab',
  components: {},
  setup() {
    const store = useStore()
    const offices = computed(() => store.getters['external/external'].mainOfficesChildren)
    const loading = computed(() => store.getters['external/external'].loading)
    const officeId = ref(undefined)
    const memberList = ref([])
    const personnelId = ref(undefined)
    
    let formLoading = ref(false)
    
    const getPersonnelList = officeId => {
      // personnelId.value= "";
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
    const onSave = () =>{
      // console.log(personnelId.value)
      let params = {
       pmaps_id: personnelId.value,
       form_id: 'aapcr',
      }
      store.dispatch('system/SAVE_AAPCR_HEAD',{ payload: params });
    }

    return {
      offices,
      officeId,
      memberList,
      personnelId,
      getPersonnelList,
      loading,
      
      formLoading,
      onSave,
    }
  },
})
</script>
