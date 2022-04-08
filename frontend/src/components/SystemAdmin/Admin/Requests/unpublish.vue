<template>
  <div>
    <a-select v-model:value="status" style="width: 200px" @change="fetchUnpublishList">
      <a-select-option value="pending">Pending</a-select-option>
      <a-select-option value="declined">Declined</a-select-option>
      <a-select-option value="verified">Verified</a-select-option>
    </a-select>
    <a-table class="mt-4" :columns="columns" :data-source="unpublishList" :loading="loading">
      <template #count="{ index }">
        <span>{{ index + 1}}</span>
      </template>

      <template #formType="{ record }">
        {{ record.form.abbreviation }}
      </template>

      <template #status="{ record }">
        <span :class="getRequestStatusClass(record.status)" class="font-size-12 badge mr-2"
              :style="record.status === 'pending' ? { cursor: 'pointer'} : {}">
          <a-popconfirm
            title="Choose an action"
            ok-text="Verify"
            cancel-text="Decline"
            @confirm="changeRequest(record.id, 'verified')"
            @cancel="changeRequest(record.id, 'declined')"
            v-if="record.status === 'pending'"
          >
            {{ record.status }}
          </a-popconfirm>
          <span v-else>{{ record.status }}</span>
        </span>
      </template>

      <template #details="{ record }">
        <a-tooltip placement="top">
          <template #title>
            <span>
              <b> Requested: </b> <br />
              {{ moment(record.requested_date).format(dateFormat) }} by {{ record.requested_by }}
            </span>
            <span v-if="record.status !== 'pending'">
              <br />
              <b> {{ record.status === 'verified' ? 'Verified: ' : 'Declined: ' }} </b> <br />
              {{ moment(record.changed_date).format(dateFormat) }} by {{ record.changed_by }}
            </span>
          </template>
          <InfoCircleOutlined :style="{ fontSize: '18px' }"/>
        </a-tooltip>
      </template>
    </a-table>
  </div>
</template>
<script>
import { defineComponent, onMounted, ref, computed } from "vue"
import { useStore } from 'vuex'
import moment from 'moment'
import { InfoCircleOutlined } from '@ant-design/icons-vue'

const columns = [
  {
    title: '#',
    key: 'count',
    slots: { customRender: 'count' },
  },
  {
    title: 'Form Type',
    key: 'formType',
    slots: { customRender: 'formType' },
  },
  {
    title: 'Document Name',
    dataIndex: 'document_name',
    key: 'document_name',
  },
  {
    title: 'Reason',
    dataIndex: 'remarks',
    key: 'remarks',
  },
  {
    title: 'Status',
    key: 'status',
    slots: { customRender: 'status' },
  },
  {
    title: 'Details',
    key: 'operation',
    className: 'column-action',
    width: 100,
    slots: { customRender: 'details' },
  },
]
export default defineComponent({
  name: "UnpublishRequests",
  components: { InfoCircleOutlined },
  setup() {
    const store = useStore()

    // DATA
    const status = ref('pending')

    // COMPUTED
    const dateFormat = computed(() => store.getters.mainStore.dateFormat)
    const unpublishList = computed(() => store.getters['requests/unpublish'])
    const loading = computed(() => store.getters['requests/stateRequests'].loading)

    // EVENTS
    onMounted(() => {
      fetchUnpublishList(status.value)
    })

    // METHODS
    const fetchUnpublishList = status => {
      store.dispatch('requests/FETCH_UNPUBLISH_LIST', { payload: { status: status }})
    }

    const getRequestStatusClass = status => {
      switch (status) {
        case 'pending':
          return 'badge-warning'
        case 'verified':
          return 'badge-success'
        case 'declined':
          return 'badge-danger'
      }
    }

    const changeRequest = (id, status) => {
      store.dispatch('requests/UPDATE_REQUEST_STATUS', { payload: { id: id, status: status }})
    }

    return {
      moment,
      columns,

      status,

      dateFormat,
      loading,
      unpublishList,

      fetchUnpublishList,
      getRequestStatusClass,
      changeRequest,
    }
  },
})
</script>
