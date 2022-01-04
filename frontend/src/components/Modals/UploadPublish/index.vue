<template>
  <a-modal v-model:visible="isVisible">
    <p>hi there</p>
  </a-modal>
</template>
<script>
import { defineComponent, ref, watch } from 'vue'

export default defineComponent({
  name: "UploadPublish",
  props: {
    isUploadOpen: Boolean,
  },
  setup(props) {
    let isVisible = ref()
    // console.log(isUploadOpen)
    // EVENTS
    watch(() => [props.isUploadOpen] , ([visible]) => {
      isVisible.value = visible
    })

    return {
      isVisible,
    }
  },
})
</script>
