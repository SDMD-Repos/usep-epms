<template>
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
      <a-col :sm="{ span: 4 }" :md="{ span: 3 }" :lg="{ span: 2 }"><b>Personnel: </b></a-col>
      <a-col :sm="{ span: 12, offset: 1 }" :md="{ span: 10, offset: 1 }" :lg="{ span: 10, offset: 1 }">
        <a-tree-select
          v-model:value="personnelId"
          style="width: 100%"
          :dropdown-style="{ maxHeight: '400px', overflow: 'auto' }"
          :tree-data="personnelList"
          placeholder="Select Personnel"
          tree-node-filter-prop="title"
          show-search
          allow-clear
          label-in-value
        />
      </a-col>
    </a-row>
    <div class="mt-4"></div>
    <a-row type="flex" justify="center" align="middle">
      <a-button style="width: 90px;" type="primary" @click="onSave" >Save</a-button>
    </a-row>
  </div>
</template>

<script>
import { computed, defineComponent, onMounted, ref } from 'vue'
import { useStore } from 'vuex'
import {message} from "ant-design-vue";
export default defineComponent({
  name: 'OpcrTab',
  components: {},
  setup() {
    const store = useStore()
    const officeId = ref(undefined)
    const personnelId = ref(undefined)

    const vpOffices = computed(() => store.getters['external/external'].vpOfficeChildren)
    const personnelList = computed(() => store.getters['external/external'].personnel)

    const fetchPersonnelList = personnelId => {
      const id = personnelId.value
      store.dispatch('external/FETCH_PERSONNEL_BY_OFFICE', { payload: id })
    }

    const validateFields = () => {
      if (!officeId.value) return false
      if (!personnelId.value) return false
      return true
    }

    const onSave = () => {
      if(validateFields())
        message.success("Saved", 2)
      else
        message.error("Please Fillout All Fields", 2)
    }

    return {
      personnelId,
      personnelList,
      fetchPersonnelList,
      vpOffices,
      officeId,
      onSave,
    }
  },
})
</script>
