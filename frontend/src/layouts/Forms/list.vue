<template>
  <a-table :columns="columns" :data-source="dataList">
    <template #dateCreated="{ record }">
      {{ moment(record.created_at).format(mainStore.dateFormat) }}
    </template>

    <template #datePublished="{ record }">
      {{ record.published_date ? moment(record.published_date).format(mainStore.dateFormat) : 'Unpublished' }}
    </template>

    <template #status="{ record }">
      <div v-if="record.is_active" class="font-size-12 badge badge-success" :style="{cursor: 'pointer'}" @click="deactivate(record.id)">
        <a-tooltip>
          <template #title><span>Click to deactivate</span></template>
          Active
        </a-tooltip>
      </div>
      <span v-else class="font-size-12 badge badge-primary">
          Inactive
        </span>
    </template>

    <template #operation="{ record }">
      <template v-if="record.is_active && !record.published_date">
        <a-tooltip>
          <template #title><span>Update</span></template>
          <EditOutlined :style="{ fontSize: '18px' }" />
          <!--            <a-icon type="edit" :style="{fontSize: '18px'}" @click="handleUpdate(record.id)" />-->
        </a-tooltip>
        <a-divider type="vertical" />
      </template>
      <a-tooltip>
        <template #title><span>View PDF</span></template>
        <FilePdfOutlined :style="{ fontSize: '18px' }" @click="viewPdf(record)"/>
        <!--          <a-icon type="file-pdf" :style="{fontSize: '18px'}" @click="viewPdf(record.id, record.document_name)"/>-->
      </a-tooltip>
      <template v-if="record.finalized_date && !record.published_date && record.is_active">
        <a-divider type="vertical" />
        <a-tooltip>
          <template #title><span>Publish</span></template>
          <FileDoneOutlined :style="{ fontSize: '18px' }" @click="handlePublish(record)"/>
          <!--            <a-icon type="file-done" :style="{ fontSize: '18px' }" @click="handlePublish(record.id, record.year)"/>-->
        </a-tooltip>
      </template>
      <template v-if="record.published_date && record.is_active">
        <a-divider type="vertical" />
        <a-tooltip>
          <template #title><span>Unpublish</span></template>
          <CloseSquareFilled :style="{ fontSize: '18px' }" />
          <!--            <a-icon type="close-square" theme="filled" :style="{fontSize: '18px'}" @click="onUnpublish(record.id)"/>-->
        </a-tooltip>
      </template>
      <template v-if="record.files.length">
        <a-divider type="vertical" />
        <a-tooltip>
          <template #title><span>View Archived</span></template>
          <UnorderedListOutlined :style="{ fontSize: '18px' }" />
          <!--            <a-icon type="unordered-list" :style="{fontSize: '18px'}" @click="viewUploadedList(record)"/>-->
        </a-tooltip>
      </template>
    </template>
  </a-table>
</template>
<script>
import { computed, defineComponent, createVNode } from "vue"
import { useStore } from 'vuex'
import moment from 'moment'
import {
  EditOutlined,
  FilePdfOutlined,
  FileDoneOutlined,
  CloseSquareFilled,
  UnorderedListOutlined,
  ExclamationCircleOutlined,
} from "@ant-design/icons-vue"

import { Modal } from "ant-design-vue"

export default defineComponent({
  components: {
    EditOutlined,
    FilePdfOutlined,
    FileDoneOutlined,
    CloseSquareFilled,
    UnorderedListOutlined,
  },
  props: {
    columns: {
      type: Array,
      default: () => { return [] },
    },
    dataList: {
      type: Array,
      default: () => { return [] },
    },
    form: { type: String, default: '' },
  },
  emits: ['publish', 'view-pdf'],
  setup(props, { emit }) {
    const store = useStore()

    // COMPUTED
    const mainStore = computed(() => store.getters.mainStore)

    // METHODS
    const deactivate = (id) => {
      Modal.confirm({
        title: () => 'Are you sure you want to deactivate this?',
        icon: () => createVNode(ExclamationCircleOutlined),
        content: () => 'You won\'t be able to revert this!',
        okText: 'Yes',
        cancelText: 'No',
        onOk() {
          const payload = {
            id: id,
          }
          store.dispatch(props.form + '/DEACTIVATE', { payload: payload })
        },
        onCancel() {},
      });
    }

    const handlePublish = data => {
      Modal.confirm({
        title: () => 'Are you sure you want to publish this?',
        icon: () => createVNode(ExclamationCircleOutlined),
        content: () => 'You won\'t be able to revert this!',
        okText: 'Yes',
        cancelText: 'No',
        onOk() {
          emit('publish', data)
        },
        onCancel() {},
      });
    }

    const viewPdf = data => {
      emit('view-pdf', data)
    }

    return {
      moment,
      mainStore,

      deactivate,
      handlePublish,
      viewPdf,
    }
  },
})
</script>
