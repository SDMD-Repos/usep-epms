<template>
  <a-modal v-model:visible="isVisible" :title="`${year-1} ratings`" ok-text="Add to list"
           width="75%"
           @ok="addPrevious" @cancel="closeModal">
    <a-table class="ant-table-striped" :columns="columns" :data-source="list" size="small" bordered
             :row-selection="{ selectedRowKeys: selectedRowKeys, onChange: onSelectChange }"
             :pagination="false" >

      <template #bodyCell="{ column, record }">
        <template v-if="column.key === 'average_point_score'">
          {{ record.aps_from }} - {{ record.aps_to }}
        </template>
      </template>

    </a-table>
  </a-modal>
</template>
<script>
import { defineComponent, ref, watch, reactive, toRefs, createVNode, inject, computed } from "vue"
import { ExclamationCircleOutlined } from '@ant-design/icons-vue'
import { Modal } from "ant-design-vue";

export default defineComponent({
  name: "RatingsPreviousList",
  components: {},
  props: {
    visible: Boolean,
    year: { type: Number, default: new Date().getFullYear() },
    tableColumns: { type: Array, default: () => { return [] }},
    list: { type: Array, default: () => { return [] }},
    currentList: { type: Array, default: () => { return [] }},
  },
  emits: ['close-modal', 'save-ratings'],
  setup(props, { emit }) {
    const isVisible = ref(false)
    const state = reactive({
      selectedRowKeys: [],
    })

    watch(() => [props.visible] , ([visible]) => {
      isVisible.value = visible
    })

    const columns = computed(() => {
      const { tableColumns } = props
      return tableColumns.splice(0,tableColumns.length-2)
    })

    // METHODS
    const onSelectChange = selectedRowKeys => {
      state.selectedRowKeys = selectedRowKeys;
    }

    const addPrevious = async () => {
      if(state.selectedRowKeys.length > 0) {
        let error = ''
        for await (const [index, key] of state.selectedRowKeys.entries()) {
          const find = props.currentList.filter(i => {
            const rating = props.list.filter(i => i.id === key)[0]
            return rating.rating.numerical_rating === i.rating.numerical_rating
          })

          if(find.length) {
            let comma = ''
            if(index) { comma = ", " }

            error += comma + find[0].rating.numerical_rating
          }
        }

        if(error === '') {
          Modal.confirm({
            title: () => 'Are you sure you want to add this in to the list?',
            icon: () => createVNode(ExclamationCircleOutlined),
            content: () => '',
            okText: 'Yes',
            cancelText: 'No',
            onOk() {
              emit('save-ratings', state.selectedRowKeys)
              state.selectedRowKeys = []
            },
            onCancel() {},
          });
        }else {
          _message.error('Duplicate entry. Unable to add ( ' + error + ' ) Numerical Rating/s for the year ' + props.year)
        }
      } else {
        _message.error("No rating/s selected. Please select at least one (1)")
      }

    }

    const closeModal = () => {
      state.selectedRowKeys = []
      emit('close-modal')
    }

    return {
      isVisible,
      ...toRefs(state),
      columns,

      addPrevious,
      closeModal,
      onSelectChange,
    }
  },
})
</script>
<style lang="scss">
@import "@/components/Manager/style.module.scss";
</style>
