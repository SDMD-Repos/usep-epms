<template>
  <a-modal v-model:visible="isVisible" :title="`${year-1} measures`" ok-text="Add to list"
           width="35%"
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

      <template #bodyCell="{ column, record }">
        <template v-if="column.key === 'displayAsItems'">
          <check-circle-filled v-if="record.display_as_items" class="display-as-items-icon" />
          <close-circle-outlined v-else class="display-as-items-icon"/>
        </template>
      </template>
    </a-table>
  </a-modal>
</template>
<script>
import { defineComponent, ref, watch, reactive, toRefs, createVNode } from "vue"
import { ExclamationCircleOutlined, CheckCircleFilled, CloseCircleOutlined } from '@ant-design/icons-vue'
import { Modal } from "ant-design-vue";

const columns = [
  { title: 'Name', dataIndex: 'name', key: 'name' },
  {
    title: 'Display as Items',
    dataIndex: 'display_as_items',
    key: 'displayAsItems',
    width: 50,
    className: 'column-display-as-items',
  },
]

export default defineComponent({
  name: "MeasuresPreviousList",
  components: { CheckCircleFilled, CloseCircleOutlined },
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
<style lang="scss">
@import "@/components/Manager/style.module.scss";
</style>
