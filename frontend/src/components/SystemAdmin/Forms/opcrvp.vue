<template>
  <div>
      <a-row type="flex">
          <a-col :sm="{ span: 4 }" :md="{ span: 3 }" :lg="{ span: 2 }"><b>Office/College:</b></a-col>
           <a-col :sm="{ span: 12, offset: 1 }" :md="{ span: 10, offset: 1 }" :lg="{ span: 10, offset: 1 }">
            <a-tree-select
            v-model:value="officeId"
            style="width: 100%"
            :dropdown-style="{ maxHeight: '400px', overflow: 'auto' }"
            placeholder="Select Office/College"
            tree-node-filter-prop="title"
            show-search
            allow-clear
            label-in-value
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
              <a-button style="width: 90px;" type="primary">Save</a-button>
        </a-row>
        <a-row type="flex" class="mt-3">
        <a-col :sm="{ span: 4 }" :md="{ span: 3 }" :lg="{ span: 2 }"><b>Office Head: </b></a-col>
         <a-col :sm="{ span: 12, offset: 1 }" :md="{ span: 10, offset: 1 }" :lg="{ span: 10, offset: 1 }">
         <a-tree-select
          v-model:value="headId"
          style="width: 100%"
          :dropdown-style="{ maxHeight: '400px', overflow: 'auto' }"
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
            <a-button style="width: 90px;" type="primary" @click="onSaveStaff">Save</a-button>
            </a-row>
  </div>
</template>

<script>
import { defineComponent } from 'vue'

export default defineComponent({
    setup() {
        
    },
})
</script>
