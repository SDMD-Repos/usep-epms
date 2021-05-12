<template>
  <span>
    <a-col :xs="{ span: 9 }" :lg="{ span: 8 }">
      <a-tree-select
        v-model="officeId"
        style="width: 100%"
        :dropdown-style="{ maxHeight: '400px', overflow: 'auto' }"
        :tree-data="officeList"
        placeholder="Select office"
        show-search
        allow-clear
        tree-data-simple-mode
        @change="handleOfficeIdChange"
      />
    </a-col>
    <a-col :xs="{ span: 9, offset: 1 }" :lg="{ span: 8, offset: 1 }">
      <a-tree-select
        v-model="personnelId"
        style="width: 100%"
        :dropdown-style="{ maxHeight: '400px', overflow: 'auto' }"
        :tree-data="list"
        placeholder="Select Personnel"
        show-search
        allow-clear
        tree-data-simple-mode
        @change="handlePersonnelIdChange"
      />
    </a-col>
    <a-col :xs="{ span: 3, offset: 1 }" :lg="{ span: 2, offset: 1 }">
      <a-icon v-if="count > 1 && id === 'new'" type="minus-circle"
              :style="{ fontSize: '25px', padding: '2px', cursor: 'pointer'}"
              @click="deleteRow(index)"/>
      <a-icon v-if="(index+1) === count"
              type="plus-circle"
              theme="filled"
              :style="{ fontSize: '25px', paddingTop: '2px', cursor: 'pointer'}"
              @click="addRow"/>
    </a-col>
  </span>
</template>
<script>
import { mapState } from 'vuex'
import * as hris from '@/services/hris'

export default {
  name: 'signatory-input',
  props: {
    value: {
      type: Object,
    },
    index: {
      required: true,
    },
    count: {
      required: true,
    },
    officeList: {
      required: true,
    },
  },
  computed: {
    ...mapState({
      personnelList: state => state.external.personnel,
    }),
  },
  data() {
    const value = this.value || []
    return {
      id: value.id || 'new',
      officeId: value.officeId || undefined,
      personnelId: value.personnelId || undefined,
      list: value.list || [],
      normalizer: {
        title: 'label',
        value: 'id',
      },
    }
  },
  watch: {
    value(val = {}) {
      this.id = val.id || 'new'
      this.officeId = val.officeId || undefined
      this.personnelId = val.personnelId || undefined
      this.list = val.list || []
    },
  },
  created() {
    if (this.personnelId && !this.list.length) {
      this.getPersonnelList(this.officeId)
    }
  },
  methods: {
    handleOfficeIdChange(e) {
      const officeId = this.officeId
      this.personnelId = undefined
      this.list = []
      if (officeId) {
        this.getPersonnelList(officeId)
      }
    },
    getPersonnelList(officeId) {
      const id = officeId.split('_')
      this.$store.commit('external/SET_STATE', {
        loading: true,
      })
      const getMainOffices = hris.getPersonnelByOffice
      getMainOffices(id[1]).then(response => {
        if (response) {
          const { personnel } = response
          this.list = personnel
        }
        this.triggerChange({ officeId })
        this.$store.commit('external/SET_STATE', {
          loading: false,
        })
      })
    },
    handlePersonnelIdChange() {
      const personnelId = this.personnelId
      this.triggerChange({ personnelId })
    },
    triggerChange(changedValue) {
      this.$emit('change', Object.assign({}, this.$data, changedValue))
    },
    addRow() {
      this.$emit('add-signatory')
    },
    deleteRow(index) {
      this.$emit('delete-signatory', index)
    },
  },
}
</script>

<style>
.spin-content {
  padding: 30px;
}
</style>
