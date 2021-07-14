<template>
  <span>
    <a-col :xs="{ span: 20 }" :lg="{ span: 20 }">
      <a-checkbox :checked="isCustom" @change="handleCustomChange">
        Custom
      </a-checkbox>
      <template v-if="!isCustom">
        <a-tree-select
          v-model="officeId"
          style="width: 100%"
          :dropdown-style="{ maxHeight: '400px', overflow: 'auto' }"
          :tree-data="officeList"
          placeholder="Select office"
          show-search
          allow-clear
          tree-data-simple-mode
          label-in-value
          @change="handleOfficeIdChange"
        />
        <a-tree-select
          v-model="personnelId"
          style="width: 100%"
          :dropdown-style="{ maxHeight: '400px', overflow: 'auto' }"
          :tree-data="list"
          placeholder="Select Personnel"
          show-search
          allow-clear
          tree-data-simple-mode
          label-in-value
          @change="handlePersonnelIdChange"
        />
        <a-select v-model="position"
                  style="width: 100%"
                  show-search
                  placeholder="Select Personnel's Position"
                  allow-clear
                  @change="handlePositionChange">
          <a-select-option v-for="position in positionList" :key="position" :value="position">
            {{ position }}
          </a-select-option>
        </a-select>
      </template>
      <template v-else>
        <a-input v-model="officeId"
                 style="width: 100%" placeholder="Office Name" @change="handleOfficeIdChange"/>
        <a-input v-model="personnelId"
                 style="width: 100%" placeholder="Personnel Name" @change="handlePersonnelIdChange"/>
        <a-input v-model="position"
                 style="width: 100%" placeholder="Personnel's Position" @change="handlePositionChange"/>
      </template>
    </a-col>
    <template v-if="allowMultiple">
      <a-col :xs="{ span: 3, offset: 1 }" :lg="{ span: 3, offset: 1 }" style="margin-top: 85px;">
        <a-icon v-if="count > 1 && id === 'new'" type="minus-circle"
                :style="{ fontSize: '25px', padding: '2px', cursor: 'pointer'}"
                @click="deleteRow(index)"/>
        <a-icon v-if="(index+1) === count"
                type="plus-circle"
                theme="filled"
                :style="{ fontSize: '25px', paddingTop: '2px', cursor: 'pointer'}"
                @click="addRow"/>
      </a-col>
    </template>
  </span>
</template>
<script>
import * as hris from '@/services/hris'

export default {
  name: 'signatory-input',
  props: {
    value: Object,
    index: {
      required: true,
    },
    count: {
      required: true,
    },
    officeList: {
      required: true,
    },
    positionList: Array,
    formName: String,
    formActive: String,
  },
  data() {
    const value = this.value || []
    return {
      id: value.id || 'new',
      officeId: value.officeId || undefined,
      personnelId: value.personnelId || undefined,
      position: value.position || undefined,
      list: value.list || [],
      isCustom: value.isCustom || false,
    }
  },
  computed: {
    allowMultiple() {
      const { formName, formActive } = this
      if (formName === 'cpcr' && formActive === 'reviewed_by') {
        return true
      } else {
        return false
      }
    },
  },
  watch: {
    value(val = {}) {
      this.id = val.id || 'new'
      this.officeId = val.officeId || undefined
      this.personnelId = val.personnelId || undefined
      this.position = val.position || undefined
      this.list = val.list || []
      this.isCustom = val.isCustom || false
    },
  },
  created() {
    if ((this.personnelId && !this.list.length) && !this.isCustom) {
      this.getPersonnelList(this.officeId)
    }
  },
  methods: {
    handleOfficeIdChange() {
      const officeId = this.officeId
      if (officeId && !this.isCustom) {
        this.personnelId = undefined
        this.list = []
        this.getPersonnelList(officeId)
      } else {
        this.triggerChange({ officeId })
      }
    },
    getPersonnelList(officeId) {
      const id = officeId.value
      this.$store.commit('external/SET_STATE', {
        loading: true,
      })
      const getPersonnelByOffice = hris.getPersonnelByOffice
      getPersonnelByOffice(id).then(response => {
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
    handlePositionChange() {
      const position = this.position
      this.triggerChange({ position })
    },
    handleCustomChange() {
      this.isCustom = !this.isCustom
      const { isCustom } = this
      this.triggerChange({ isCustom })
      this.officeId = undefined
      this.handleOfficeIdChange()
      this.personnelId = undefined
      this.handlePersonnelIdChange()
      this.position = undefined
      this.handlePositionChange()
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
