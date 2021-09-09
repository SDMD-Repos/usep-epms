<template>
  <div>
    <a-tree-select
      v-model="officeObj"
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
    <a-select v-model="chairObj"
              style="width: 100%"
              :dropdown-style="{ maxHeight: '400px', overflow: 'auto' }"
              placeholder="Select Personnel"
              option-filter-prop="children"
              :filter-option="filter"
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

export default {
  props: {
    value: Object,
    chairOffice: Object,
    departments: Array,
    personnel: Array,
    loading: Boolean,
    filter: Function,
    members: Array,
    action: String,
  },
  data() {
    const value = this.value || undefined
    const personnel = this.personnel
    const loading = this.loading
    const chairOffice = this.chairOffice || undefined
    return {
      chairObj: value || undefined,
      officeObj: chairOffice,
      personnelList: personnel,
      isLoading: loading,
    }
  },
  watch: {
    value(val) {
      this.chairObj = val || undefined
    },
    personnel(val) {
      this.personnelList = val
    },
    loading(val) {
      this.isLoading = val
    },
    chairOffice(val) {
      this.officeObj = val
    },
  },
  methods: {
    handleChairOfficeChange() {
      const { officeObj } = this
      // this.triggerChange({ chairOffice })
      this.$emit('get-personnel-list', officeObj)
    },
    handleChairIdChange() {
      const { chairObj } = this
      // console.log(chairObj)
      if (typeof chairObj !== 'undefined') {
        const exists = this.members.some(function(field) {
          return field.id.value === chairObj.key
        })
        if (exists) {
          this.$message.error('This name is already on the list of members')
          this.chairObj = undefined
        }
      }
      this.$emit('update-chair-id', { field: 'chairId', values: this.chairObj })
      this.triggerChange({ chairObj })
    },
    triggerChange(changedValue) {
      // Should provide an event to pass value to Form.
      this.$emit('change', Object.assign({}, this.$data, changedValue))
    },
  },
}
</script>
