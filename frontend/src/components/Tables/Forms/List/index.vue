<template>
  <a-table :columns="columns" :data-source="dataList" :loading="{ spinning: loading, tip: loadingTip }" bordered>
    <template #bodyCell="{ column, record }">
      <template v-if="column.key === 'dateCreated'">
        {{ dayjs(record.created_at).format(dateFormat) }}
      </template>

      <template v-if="column.key === 'datePublished'">
        {{ record.published_date ? dayjs(record.published_date).format(dateFormat) : 'Unpublished' }}
      </template>

      <template v-if="column.key === 'status'">
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
          <template v-if="record.status && record.status.filter(i => i.status === 'pending').length">
            <a-tooltip title="Cancel request">
              <span class="font-size-12 badge badge-warning" :style="{cursor: 'pointer'}"
                    @click="cancelUnpublishRequest(record)">
                Pending unpublish request
              </span>
            </a-tooltip>
          </template>
        </div>
      </template>

      <template v-if="column.key === 'operation'">
        <template v-if="(accessOfficeId === record.office_id) || hasFormAccess">
            <template v-if="record.is_active && !record.published_date">
              <a-tooltip>
                <template #title><span>Update</span></template>
                <EditOutlined :style="{ fontSize: '18px' }" @click="handleUpdate(record.id)"/>
              </a-tooltip>
              <a-divider type="vertical" v-if="form !== 'opcrtemplate'" />
            </template>
            <a-tooltip v-if="form !== 'opcrtemplate'" title="View PDF">
              <FilePdfOutlined :style="{ fontSize: '18px' }" @click="viewPdf(record)"/>
            </a-tooltip>
            <template v-if="record.finalized_date && !record.published_date && record.is_active">
              <a-divider type="vertical" />
              <a-tooltip title="Publish">
                <FileDoneOutlined :style="{ fontSize: '18px' }" @click="handlePublish(record)"/>
              </a-tooltip>
            </template>
            <template v-if="record.published_date && record.is_active &&
                            (record.status && (!record.status.filter(i => i.status === 'pending').length) || form === 'opcrtemplate')"
            >
              <a-divider type="vertical" v-if="form !== 'opcrtemplate'" />
              <a-tooltip title="Request to unpublish">
                <CloseSquareFilled :style="{ fontSize: '18px' }" @click="onUnpublish(record)" />
              </a-tooltip>
            </template>
            <template v-if="record.status && record.status.length && record.status.filter(i => i.status === 'verified').length">
              <a-divider type="vertical" />
              <a-tooltip title="View Archived">
                <UnorderedListOutlined :style="{ fontSize: '18px' }" @click="openUnpublishedForms(record)"/>
              </a-tooltip>
            </template>
        </template>
        <template v-else>
          <a-tooltip v-if="form !== 'opcrtemplate'" title="View PDF">
            <FilePdfOutlined :style="{ fontSize: '18px' }" @click="viewPdf(record)"/>
          </a-tooltip>
        </template>
      </template>
    </template>
  </a-table>
</template>
<script>
import { defineComponent, createVNode, ref, computed } from "vue"
import { useStore } from 'vuex'
import dayjs from 'dayjs'
import {
  EditOutlined, FilePdfOutlined, FileDoneOutlined, CloseSquareFilled, UnorderedListOutlined, ExclamationCircleOutlined,
} from "@ant-design/icons-vue"
import { Modal } from "ant-design-vue"

export default defineComponent({
  name: "FormListTable",
  components: {
    EditOutlined, FilePdfOutlined, FileDoneOutlined, CloseSquareFilled, UnorderedListOutlined,
  },
  props: {
    columns: { type: Array, default: () => { return [] } },
    dataList: { type: Array, default: () => { return [] } },
    form: { type: String, default: '' },
    loading: Boolean,
    accessOfficeId: { type: Number, default: null },
    hasFormAccess: { type: Boolean, default: false },
  },
  emits: [
    'update-form', 'publish', 'view-pdf', 'unpublish', 'view-unpublished-forms', 'cancel-unpublish-request',
  ],
  setup(props, { emit }) {
    const store = useStore()

    const loadingTip = ref("")

    // COMPUTED
    const dateFormat = computed(() => store.getters.mainStore.dateFormat)

    // METHODS
    const handleUpdate = id => {
      loadingTip.value = 'Please wait...'
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
          loadingTip.value = 'Please wait...'
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
          loadingTip.value = 'Publishing document. Please wait...'
          emit('publish', data)
        },
        onCancel() {},
      });
    }

    const viewPdf = data => {
      loadingTip.value = 'Please wait...'
      emit('view-pdf', { data: data })
    }

    const onUnpublish = data => {
      Modal.confirm({
        title: () => 'Are you sure you want to unpublish this?',
        icon: () => createVNode(ExclamationCircleOutlined),
        content: () => 'You won\'t be able to revert this!',
        okText: 'Yes',
        cancelText: 'No',
        onOk() {
          loadingTip.value = 'Please wait...'
          emit('unpublish', data)
        },
        onCancel() {},
      });
    }

    const cancelUnpublishRequest = data => {
      Modal.confirm({
        title: () => 'Are you sure you want to cancel your request?',
        icon: () => createVNode(ExclamationCircleOutlined),
        content: () => 'You won\'t be able to revert this!',
        okText: 'Yes',
        cancelText: 'No',
        onOk() {
          loadingTip.value = 'Please wait...'
          emit('cancel-unpublish-request', { data: data, form: props.form } )
        },
        onCancel() {},
      });
    }

    const openUnpublishedForms = details => {
      emit('view-unpublished-forms', details)
    }

    return {
      loadingTip,

      dayjs,
      dateFormat,

      handleUpdate,
      deactivate,
      handlePublish,
      viewPdf,
      onUnpublish,
      cancelUnpublishRequest,
      openUnpublishedForms,
    }
  },
})
</script>
