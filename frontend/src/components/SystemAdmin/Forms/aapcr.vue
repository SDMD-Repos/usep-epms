<template>
   <div>
             <a-form-item label="Office/College:">
              <a-row type="flex">
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
                />
                  </a-col>
                    <a-col :sm="{ span: 8, offset: 1 }" :md="{ span: 10, offset: 1 }" :lg="{ span: 10, offset: 1 }">
                    <a-button style="width: 90px;" type="primary" @click="onSubmit" >Save</a-button>
                    </a-col>
                 </a-row>
        </a-form-item>
       
        
        
     
    </div>
</template>

<script>
import { computed, defineComponent, onMounted, ref } from 'vue'
import { useStore } from 'vuex'
export default defineComponent({
  name: 'AapcrTab',
  components: {},
  setup() {
    const store = useStore()
    const offices = computed(() => store.getters['external/external'].mainOfficesChildren)
    const officeId = ref(undefined)
    return {
      offices,
      officeId,
    }
  },
})
</script>
