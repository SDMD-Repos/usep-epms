<template>
  <a-modal v-model:visible="isVisible" :title="`${year-1} measures`" ok-text="Add to list"
           @ok="addPreviousMeasures" @cancel="closeModal">
    <a-table class="ant-table-striped" :columns="columns" :data-source="list" size="small" bordered
             :row-selection="{ selectedRowKeys: selectedRowKeys, onChange: onSelectChange }" :pagination="false"
             :row-class-name="(record, index) => (index % 2 === 1 ? 'table-striped' : null)"
    >
      <template #expandedRowRender="{ record }">
        <a-list size="small" bordered :data-source="record.items">
          <template #renderItem="{ item }">
            <a-list-item>
              <a-list-item-meta description="">
                <template #title>
                  {{ item.rate }} - {{ item.description }}
                </template>
              </a-list-item-meta>
            </a-list-item>
          </template>
        </a-list>
      </template>
    </a-table>
  </a-modal>
</template>
<script>
import { defineComponent, ref, watch, reactive, toRefs, createVNode } from "vue"
import {/*DownCircleFilled,*/ ExclamationCircleOutlined/*, UpCircleFilled*/} from '@ant-design/icons-vue'
import { Modal } from "ant-design-vue";

const columns = [
  { title: 'Name', dataIndex: 'name', key: 'name' },
]

export default defineComponent({
  name: "MeasuresPreviousList",
  // components: { DownCircleFilled, UpCircleFilled },
  props: {
    visible: Boolean,
    year: { type: Number, default: new Date().getFullYear() },
    list: { type: Array, default: () => { return [] }},
  },
  emits: ['close-modal', 'save-measures'],
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

    const addPreviousMeasures = () => {
      Modal.confirm({
        title: () => 'Are you sure you want to add this in to the list?',
        icon: () => createVNode(ExclamationCircleOutlined),
        content: () => '',
        okText: 'Yes',
        cancelText: 'No',
        onOk() {
          emit('save-measures', state.selectedRowKeys)
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

      addPreviousMeasures,
      closeModal,
      onSelectChange,
    }
  },
})
</script>
<style scoped>
.ant-table-striped :deep(.table-striped) td {
  background-color: #fafafa;
}
.expand-icon-antd {
  font-size: 18px;
  cursor: pointer;
}
</style>
