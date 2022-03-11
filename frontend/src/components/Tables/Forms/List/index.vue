<template>
  <a-table :columns="columns" :data-source="dataList" :loading="loading" bordered>
    <template #dateCreated="{ record }">
      {{ moment(record.created_at).format(dateFormat) }}
    </template>

    <template #datePublished="{ record }">
      {{ record.published_date ? moment(record.published_date).format(dateFormat) : 'Unpublished' }}
    </template>

    <template #status="{ record }">
      <div>
        <span v-if="record.is_active" class="font-size-12 badge badge-success mr-2" :style="{cursor: 'pointer'}" @click="deactivate(record.id)">
          <a-tooltip>
            <template #title><span>Click to deactivate</span></template>
            Active
          </a-tooltip>
        </span>
        <span v-else class="font-size-12 badge badge-primary">
          Inactive
        </span>
        <span v-if="record.status.filter(i => i.status === 'pending').length" class="font-size-12 badge badge-warning">
          Pending unpublish request
        </span>
      </div>
    </template>

    <template #operation="{ record }">
      <template v-if="record.is_active && !record.published_date">
        <a-tooltip>
          <template #title><span>Update</span></template>
          <EditOutlined :style="{ fontSize: '18px' }" @click="handleUpdate(record.id)"/>
        </a-tooltip>
        <a-divider type="vertical" />
      </template>
      <a-tooltip>
        <template #title><span>View PDF</span></template>
        <FilePdfOutlined :style="{ fontSize: '18px' }" @click="viewPdf(record)"/>
      </a-tooltip>
      <template v-if="record.finalized_date && !record.published_date && record.is_active">
        <a-divider type="vertical" />
        <a-tooltip>
          <template #title><span>Publish</span></template>
          <FileDoneOutlined :style="{ fontSize: '18px' }" @click="handlePublish(record)"/>
        </a-tooltip>
      </template>
      <template v-if="record.published_date && record.is_active && (!record.status.filter(i => i.status === 'pending').length)">
        <a-divider type="vertical" />
        <a-tooltip>
          <template #title><span>Request to unpublish</span></template>
          <CloseSquareFilled :style="{ fontSize: '18px' }" @click="onUnpublish(record.id)" />
        </a-tooltip>
      </template>
      <template v-if="record.files.length">
        <a-divider type="vertical" />
        <a-tooltip>
          <template #title><span>View Archived</span></template>
          <UnorderedListOutlined :style="{ fontSize: '18px' }" @click="openUploadedList(record)"/>
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
  name: "FormListTable",
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
    loading: Boolean,
  },
  emits: ['update-form', 'publish', 'view-pdf', 'unpublish', 'view-uploaded-list'],
  setup(props, { emit }) {
    const store = useStore()

    // COMPUTED
    const dateFormat = computed(() => store.getters.mainStore.dateFormat)

    // METHODS
    const handleUpdate = id => {
      emit('update-form', id)
    }

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

    const onUnpublish = id => {
      Modal.confirm({
        title: () => 'Are you sure you want to unpublish this?',
        icon: () => createVNode(ExclamationCircleOutlined),
        content: () => 'You won\'t be able to revert this!',
        okText: 'Yes',
        cancelText: 'No',
        onOk() {
          emit('unpublish', id)
        },
        onCancel() {},
      });
    }

    const openUploadedList = details => {
      emit('view-uploaded-list', details)
    }

    return {
      moment,
      dateFormat,

      handleUpdate,
      deactivate,
      handlePublish,
      viewPdf,
      onUnpublish,
      openUploadedList,
    }
  },
})
</script>
