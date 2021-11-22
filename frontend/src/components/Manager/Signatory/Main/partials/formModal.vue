<template>
  <a-modal v-model:visible="isVisible"
           :title="modalTitle"
           :closable="false"
           :mask-closable="false"
           :ok-text="okText"
           @ok="onOk"
           @cancel="onCancel">
    <a-form ref="signatoryRef" :model="form">
      <a-form-item>

      </a-form-item>
    </a-form>
  </a-modal>
</template>
<script>
import { defineComponent, ref, watch } from "vue"

export default defineComponent({
  props: {
    visible: Boolean,
    modalTitle: {
      type: String,
      default: '',
    },
    okText: {
      type: String,
      default: '',
    },
    actionType: {
      type: String,
      default: '',
    },
    formState: {
      type: Object,
      default: () => { return {} },
    },
  },
  emits: ['close-modal'],
  setup(props, { emit }) {
    // DATA
    let isVisible = ref()
    let form = ref()

    // EVENTS
    watch(() => [props.visible, props.formState] , ([visible, formState]) => {
      isVisible.value = visible
      form.value = formState
    })

    // METHODS
    const onOk = () => {

    }

    const onCancel = () => {
      emit('close-modal')
    }

    return {
      isVisible,
      form,

      onOk,
      onCancel,
    }
  },
})
</script>
