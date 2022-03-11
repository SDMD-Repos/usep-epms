<template>
   <template>
    <a-form-item label="Activity name" v-bind="validateInfos.name">
      <a-input v-model:value="modelRef.name" />
    </a-form-item>
   </template>    
</template>


<script>
import { computed, defineComponent, onMounted, ref  } from 'vue';

export default defineComponent({
    name:"AapcrTab",
    components: {

    },
    setup() {
        
    },
})
</script>
