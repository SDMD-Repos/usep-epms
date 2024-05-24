<template>
  <a-list bordered :data-source="budgetList">
    <template #renderItem="{ item, index }">
      <a-list-item v-if="item.editable">
        <a-list-item-meta description="">
          <template #title>
            <a-row :gutter="0">
              <a-col :xs="{ span: 12 }" :sm="{ span: 12 }" :md="{ span: 8 }" :lg="{ span: 5 }">
                {{ item.mainCategory.label.children || item.mainCategory.label }} :
              </a-col>
              <a-col :xs="{ span: 9, offset: 1 }" :sm="{ span: 8 }" :md="{ span: 8 }" :lg="{ span: 5 }">
                <a-input-number v-model:value="item.categoryBudget" style="width: 100%"
                                :formatter="value => `${value}`.replace(/\B(?=(\d{3})+(?!\d))/g, ',')"
                                :parser="value => value.replace(/\$\s?|(,*)/g, '')"
                                :min="0" @pressEnter="updateBudget(index)"/>
              </a-col>
            </a-row>
          </template>
        </a-list-item-meta>
        <template #actions>
          <a @click="updateBudget(index)">update</a>
          <a @click="cancelEdit(index)">cancel</a>
        </template>
      </a-list-item>
      <a-list-item v-else>
        <span>{{ item.mainCategory.label.children || item.mainCategory.label }} - <b>₱ {{ $filters.numbersWithCommas(item.categoryBudget) }}</b></span>
        <template #actions v-if="editingKey === ''">
          <a @click="editBudget(index)">edit</a>
          <a @click="deleteBudget(index)">delete</a>
        </template>
      </a-list-item>
    </template>
  </a-list>
</template>
<script>
import { createVNode, defineComponent, ref, inject } from "vue"
import { Modal } from "ant-design-vue"
import { ExclamationCircleOutlined } from "@ant-design/icons-vue"

export default defineComponent({
  name: "BudgetListComponent",
  props: {
    budgetList: { type: Array, default: () => { return [] }},
  },
  emits: ['delete-budget-item'],
  setup(props, { emit }) {
    const _message = inject('a-message')

    // DATA
    const editingKey = ref('')
    const budgetStorage = ref({})

    // METHODS
    const editBudget = index => {
      const target = props.budgetList[index]
      if (target) {
        Object.assign(budgetStorage.value, props.budgetList[index])
        editingKey.value = index
        target.editable = true
      }
    }

    const updateBudget = index => {
      const target = props.budgetList[index]
      if (target) {
        editingKey.value = ''
        delete target.editable
        budgetStorage.value = {}
      }
    }

    const cancelEdit = index => {
      const target = props.budgetList[index]
      if (target) {
        editingKey.value = ''
        target.categoryBudget = budgetStorage.value.categoryBudget
        delete target.editable
        budgetStorage.value = {}
      }
    }

    const deleteBudget = index => {
      Modal.confirm({
        title: () => 'Are you sure you want to delete this?',
        icon: () => createVNode(ExclamationCircleOutlined),
        content: () => '',
        okText: 'Yes',
        cancelText: 'No',
        onOk() {
          emit('delete-budget-item', index)
          _message.success('Deleted!', 2)
        },
        onCancel() {},
      })
    }

    return {
      editingKey,

      editBudget,
      updateBudget,
      cancelEdit,
      deleteBudget,
    }
  },
})
</script>
