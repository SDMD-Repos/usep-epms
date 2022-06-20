<template>
  <a-modal v-model:visible="isVisible" :title="`${year-1} programs`" ok-text="Add to list"
           @ok="addPreviousPrograms" @cancel="closeModal" width="700px">
    <a-select v-model:value="selectedFunction" placeholder="Select Function" style="width: 200px">
      <a-select-option v-for="(y) in functions" :value="y.id" :label="y.name" :key="y.id">
        {{ y.name }}
      </a-select-option>
    </a-select>
    <div class="mt-5">
      <a-table class="ant-table-striped" :columns="columns" :data-source="list" size="small" bordered
               :row-selection="{ selectedRowKeys: selectedRowKeys, onChange: onSelectChange }" :pagination="false"
               :row-class-name="(record, index) => (index % 2 === 1 ? 'table-striped' : null)"
      ></a-table>
    </div>
  </a-modal>
</template>
<script>
import { computed, createVNode, defineComponent, onMounted, reactive, ref, toRefs, watch } from "vue"
import { Modal } from "ant-design-vue";
import { ExclamationCircleOutlined } from "@ant-design/icons-vue";
import { useStore } from "vuex";



export default defineComponent({
  name: "ProgramsPreviousList",
  props: {
    visible: Boolean,
    year: { type: Number, default: new Date().getFullYear() },
    list: { type: Array, default: () => { return [] }},
  },
  emits: ['close-modal', 'save-programs'],
  setup(props, { emit }) {
    const store = useStore()
    const isVisible = ref(false)
    const selectedFunction = ref(null)
    const functions = computed(() => store.getters['formManager/manager'].functions)
    const state = reactive({
      selectedRowKeys: [],
    })
    const columns = [
      { title: 'Name', dataIndex: 'name', key: 'name' },
      { title: `Previous Function (${props.year})`, dataIndex: 'category.name', key: 'category_id', disabled: 'disabled' },
      { title: 'Percentage', dataIndex: 'percentage' },
    ]

    watch(() => [props.visible] , ([visible]) => {
      isVisible.value = visible
    })

    // EVENTS
    onMounted(() => {
      store.dispatch('formManager/FETCH_FUNCTIONS', { payload: { year: props.year, isPrevious: false }})
    })

    // METHODS
    const onSelectChange = selectedRowKeys => {
      state.selectedRowKeys = selectedRowKeys;
    }

    const addPreviousPrograms = () => {
      if(selectedFunction.value) {
        Modal.confirm({
          title: () => 'Are you sure you want to add this in to the list?',
          icon: () => createVNode(ExclamationCircleOutlined),
          content: () => '',
          okText: 'Yes',
          cancelText: 'No',
          onOk() {
            emit('save-programs', [state.selectedRowKeys,selectedFunction.value])
            resetModalData()
          },
          onCancel() {},
        });
      }else{
        Modal.error({
          title: () => 'Unable to add program/s in to the list',
          content: () => 'Please select a function',
        })
      }
    }

    const closeModal = () => {
      resetModalData()
      emit('close-modal')
    }

    const resetModalData = () => {
      state.selectedRowKeys = []
      selectedFunction.value = null
    }

    return {
      columns,
      selectedFunction,
      isVisible,
      ...toRefs(state),
      functions,
      onSelectChange,
      addPreviousPrograms,
      closeModal,
    }
  },
})
</script>
