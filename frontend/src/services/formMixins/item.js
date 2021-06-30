import { getFormColumns } from '../formColumns'

const getDetailSettings = () => {
  return {
    open: false,
    okText: '',
    modalTitle: '',
    updateId: null,
    type: 'pi',
    parentDetails: undefined,
  }
}

const detailSettings = getDetailSettings()

export default {
  props: {
    itemSource: Array,
    functionId: String,
    drawer: String,
    categories: Array,
    targetsBasisList: Array,
    counter: Number,
  },
  data() {
    const itemSource = this.itemSource
    const drawer = this.drawer
    const counter = this.counter
    return {
      getFormColumns,
      dataSource: itemSource,
      opened: drawer,
      drawerConfig: detailSettings,
      count: counter,
    }
  },
  methods: {
    openModal(action) {
      const { drawerConfig } = this
      this.$emit('update-drawer-status', this.functionId)
      drawerConfig.open = true
      if (action === 'Add') {
        drawerConfig.okText = action
        drawerConfig.modalTitle = 'Add New'
        drawerConfig.updateId = null
        drawerConfig.type = 'pi'
      } else if (action === 'Update') {
        drawerConfig.okText = action
        drawerConfig.modalTitle = 'Update Details'
      } else if (action === 'newsub') {
        drawerConfig.okText = 'Add Sub PI'
        drawerConfig.modalTitle = 'New Sub PI'
        drawerConfig.updateId = null
        drawerConfig.type = 'sub'
      }
    },
    changeModalState(createNew) {
      Object.assign(this.drawerConfig, getDetailSettings())
      this.resetForm()
      if (createNew) {
        this.openModal('Add')
      } else {
        this.$emit('update-drawer-status', '')
      }
    },
    updateTableItem(details) {
      const newData = [...this.dataSource]
      const { drawerConfig } = this
      if (drawerConfig.type === 'pi') {
        if (!details.formData.isHeader) {
          const { targetsBasis } = details.formData
          if (targetsBasis !== '' && typeof targetsBasis !== 'undefined' && this.targetsBasisList.indexOf(targetsBasis) === -1) {
            this.$emit('add-targets-basis-list', targetsBasis)
          }
        }
        Object.assign(newData[details.updateId], details.formData)
      } else {
        const { parentDetails } = this.drawerConfig
        const parentIndex = newData.findIndex(i => i.key === parentDetails.key)
        const { children } = newData[parentIndex]
        Object.assign(children[details.updateId], details.formData)
      }
      this.changeModalState(0)
    },
    handleDelete(key, type) {
      let deletedData = null
      if (type === 'pi') {
        const recordKey = this.dataSource.findIndex((record, i) => record.key === key)
        deletedData = this.dataSource[recordKey]
        this.dataSource.splice(recordKey, 1)
      } else {
        const source = [...this.dataSource]
        source.forEach((item, index) => {
          if (typeof item.children !== 'undefined') {
            const recordKey = item.children.findIndex(i => i.key === key)
            if (recordKey !== -1) {
              deletedData = item.children[recordKey]
              item.children.splice(recordKey, 1)
              if (!item.children.length) {
                delete item.children
              }
              return
            }
            console.log(recordKey)
          }
        })
        this.$emit('update-data-source', source)
      }
      if (deletedData) {
        const { id } = deletedData
        if (id.toString().indexOf('new') === -1) {
          this.$emit('add-deleted-id', id)
        }
      }
    },
  },
}
