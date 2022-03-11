<template>

 <a-collapse v-model:activeKey="activeKey">
    <a-collapse-panel key="1" header="AAPCR">
         <form-admin/>
    </a-collapse-panel>
    <a-collapse-panel key="2" header="OPCR (VPs)" :disabled="false">
      <p>OPCR (VPs)</p>
    </a-collapse-panel>
    <a-collapse-panel key="3" header="OPCR">
      <p>OPCR</p>
    </a-collapse-panel>
    <a-collapse-panel key="4" header="CPCR">
      <p>CPCR</p>
    </a-collapse-panel>
    <a-collapse-panel key="5" header="IPCR">
      <p>IPCR</p>
    </a-collapse-panel>
  </a-collapse>

</template>


<script>
import { computed, defineComponent, onMounted, ref  } from 'vue';
import FormAdmin from '@/components/SystemAdmin/Forms/aapcr'
export default defineComponent({
    name:"FormAdminTable",
    components: {
        FormAdmin,
    },
    setup() {
        
    },
})
</script>
