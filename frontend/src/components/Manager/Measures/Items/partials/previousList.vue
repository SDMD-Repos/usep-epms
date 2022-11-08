<template>
  <a-modal v-model:visible="isVisible" :title="`${year-1} Measure Items`" ok-text="Add to list"
           width="1000px"
           @ok="addPreviousMeasures" @cancel="closeModal">
    <a-table class="ant-table-striped" :columns="columns" :data-source="list" size="small" bordered
             :row-selection="{ selectedRowKeys: selectedRowKeys, onChange: onSelectChange }" :pagination="false"
             :row-class-name="(record, index) => (index % 2 === 1 ? 'table-striped' : null)"
    >
      <template #expandedRowRender="{ record }">
        <a-card>
          <a-descriptions bordered title="Details" size="small" layout="vertical">
            <a-descriptions-item label="Display as Items?" >
              <b>{{ record.display_as_items ? 'True' : 'False' }}</b>
            </a-descriptions-item>
            <a-descriptions-item label="Is Custom?"><b>{{ record.is_custom ? 'True' : 'False' }}</b></a-descriptions-item>
            <a-descriptions-item label="Description"><b>{{ record.description }}</b></a-descriptions-item>
            <a-descriptions-item label="Variable Equivalent">
              <b>{{ record.variable_equivalent }}</b>
            </a-descriptions-item>
            <a-descriptions-item label="Elements"><b>{{ record.elements }}</b></a-descriptions-item>
          </a-descriptions>

          <a-list bordered :data-source="!record.is_custom ? record.categories : record.custom_items" size="small" class="mt-4">
            <template #header><h6>{{ !record.is_custom ? 'Categories' : 'Items' }}</h6></template>
            <template #renderItem="{ item }">
              <a-list-item>
                <a-list-item-meta >
                  <template v-if="!record.is_custom" #title>
                    {{ item.numbering ? item.numbering + ". " + item.name : item.name }}
                  </template>
                  <template #description>
                    <template v-if="!record.is_custom">
                      <span v-for="i in item.items" :key="i.id">{{ i.rating.numerical_rating + " - " + i.description }}<br /></span>
                    </template>
                    <template v-else>
                      {{ item.rating.numerical_rating + " - " + item.description }}
                    </template>
                  </template>
                </a-list-item-meta>
              </a-list-item>
            </template>
          </a-list>

        </a-card>
      </template>
    </a-table>
  </a-modal>
</template>
<script>
import { defineComponent, ref, watch, reactive, toRefs, createVNode } from "vue"
import { ExclamationCircleOutlined } from '@ant-design/icons-vue'
import { Modal } from "ant-design-vue";

const columns = [
  { title: 'Name', dataIndex: 'name', key: 'name' },
]

export default defineComponent({
  name: "MeasuresPreviousList",
  props: {
    visible: Boolean,
    year: { type: Number, default: new Date().getFullYear() },
    list: { type: Array, default: () => { return [] }},
  },
  emits: ['close-modal', 'multiple-save-measures'],
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
          emit('multiple-save-measures', state.selectedRowKeys)
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
