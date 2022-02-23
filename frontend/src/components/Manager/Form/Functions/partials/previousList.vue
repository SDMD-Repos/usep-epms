<template>
  <a-modal v-model:visible="isVisible" :title="`${year-1} functions`" ok-text="Add to list"
           @ok="addPreviousFunctions" @cancel="closeModal">
    <a-table class="ant-table-striped" :columns="columns" :data-source="list" size="small" bordered
             :row-selection="{ selectedRowKeys: selectedRowKeys, onChange: onSelectChange }" :pagination="false"
             :row-class-name="(record, index) => (index % 2 === 1 ? 'table-striped' : null)"
    ></a-table>
  </a-modal>
</template>
<script>
import {createVNode, defineComponent, reactive, ref, toRefs, watch} from "vue"
import {Modal} from "ant-design-vue";
import {ExclamationCircleOutlined} from "@ant-design/icons-vue";

const columns = [
  { title: 'Name', dataIndex: 'name', key: 'name' },
  { title: 'Percentage', dataIndex: 'percentage' },
]

export default defineComponent({
  props: {
    visible: Boolean,
    year: { type: Number, default: new Date().getFullYear() },
    list: { type: Array, default: () => { return [] }},
  },
  emits: ['close-modal', 'save-functions'],
  setup(props, { emit }) {
    const isVisible = ref(false)
    const state = reactive({
      selectedRowKeys: [],
    })

    watch(() => [props.visible] , ([visible]) => {
      isVisible.value = visible
    })

    // METHODS
    const onSelectChange = selectedRowKeys => {
      state.selectedRowKeys = selectedRowKeys;
    }

    const addPreviousFunctions = () => {
      Modal.confirm({
        title: () => 'Are you sure you want to add this to list?',
        icon: () => createVNode(ExclamationCircleOutlined),
        content: () => '',
        okText: 'Yes',
        cancelText: 'No',
        onOk() {
          emit('save-functions', state.selectedRowKeys)
          state.selectedRowKeys = []
        },
        onCancel() {},
      });
    }

    const closeModal = () => {
      state.selectedRowKeys = []
      emit('close-modal')
    }

    return {
      columns,

      isVisible,
      ...toRefs(state),

      onSelectChange,
      addPreviousFunctions,
      closeModal,
    }
  },
})
</script>
