<template>
  <a-modal v-model:visible="isVisible" title="Unpublish" width="30%"
           :closable="false" :mask-closable="false" :ok-button-props="{ disabled: !remarks }"
           @ok="onOkClick" @cancel="onClose">
    <a-textarea v-model:value="remarks" placeholder="Reason for unpublishing the document" :rows="4"/>
  </a-modal>
</template>
<script>
import { defineComponent, watch, ref } from "vue"

export default defineComponent({
  name: "UnpublishRemarksModal",
  props: { isUnpublish: Boolean, formId: { type: String, default: '' } },
  emits: ['unpublish', 'close-remarks-modal'],
  setup(props, { emit }) {
    const isVisible = ref(false)
    const remarks = ref('')

    watch(() => [props.isUnpublish], ([visible]) => {
      isVisible.value = visible
    })

    // METHODS
    const onOkClick = () => {
      emit('unpublish', { remarks: remarks.value, form: props.formId })
    }

    const onClose = () => {
      emit('close-remarks-modal')
      resetForm()
    }

    const resetForm = () => {
      remarks.value = ''
    }

    return {
      isVisible,
      remarks,

      onOkClick,
      onClose,
    }
  },
})
</script>
