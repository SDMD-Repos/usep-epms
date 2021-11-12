<template>
  <div>
    <a-tree-select
      v-model:value="values.office"
      style="width: 100%"
      :dropdown-style="{ maxHeight: '400px', overflow: 'auto' }"
      :tree-data="departments"
      placeholder="Select office"
      tree-node-filter-prop="title"
      show-search
      allow-clear
      label-in-value
      :disabled="action === 'view'"
      @change="handleChairOfficeChange($event, 'oic')"
    />
    <a-select v-model:value="values.chairman"
              style="width: 100%"
              :dropdown-style="{ maxHeight: '400px', overflow: 'auto' }"
              placeholder="Select Personnel"
              option-filter-prop="children"
              :filter-option="filterOption"
              :disabled="loading || action === 'view'"
              :loading="loading"
              show-search
              allow-clear
              label-in-value
              @change="handleChairIdChange">
      <a-select-option v-for="o in personnel" :key="o.value" :value="o.value">
        {{ o.title }}
      </a-select-option>
    </a-select>
  </div>
</template>
<script>
import { toRef, reactive } from 'vue'
import { message } from 'ant-design-vue'

export default {
  props: {
    value: {
      type: Object,
      default: () => { return undefined },
    },
    chairOffice: {
      type: Object,
      default: () => { return undefined },
    },
    departments: {
      type: Array,
      default: () => { return [] },
    },
    personnel: {
      type: Array,
      default: () => { return [] },
    },
    loading: Boolean,
    members: {
      type: Array,
      default: () => { return [] },
    },
    action: {
      type: String,
      default: '',
    },
  },
  emits: ['get-personnel-list', 'update-chair-id', 'change'],
  setup(props, { emit }) {
    // DATA
    const chairman = toRef(props, 'value')
    const office = toRef(props, 'chairOffice')
    const membersList = toRef(props, 'members')
    const values = reactive({
      chairman: chairman,
      office: office,
    })

    // METHODS
    const filterOption = (input, option) => {
      return (
        option.componentOptions.children[0].text.toLowerCase().indexOf(input.toLowerCase()) >= 0
      )
    }

    const handleChairOfficeChange = () => {
      emit('get-personnel-list', office)
    }

    const handleChairIdChange = () => {
      if (typeof chairman.value !== 'undefined') {
        const exists = membersList.value.some(function(field) {
          return field.id.value === chairman.value.key
        })
        if (exists) {
          message.error('This name is already on the list of members')
          chairman.value = undefined
        }
      }
      emit('update-chair-id', { field: 'chairId', values: chairman })
      triggerChange({ chairman })
    }

    const triggerChange = changedValue => {
      // Should provide an event to pass value to Form.
      emit('change', Object.assign({}, this.$data, changedValue))
    }

    return {
      values,

      filterOption,
      handleChairOfficeChange,
      handleChairIdChange,
    }
  },
}
</script>
