import { TreeSelect, Modal } from 'ant-design-vue'
import { mapState } from 'vuex'
const SHOW_PARENT = TreeSelect.SHOW_PARENT

export default {
  props: {
    drawerId: String,
    drawerConfig: Object,
    formObject: Object,
    targetsBasisList: Array,
    categories: Array,
  },
  computed: {
    ...mapState({
      subCategoryList: state => state.formManager.subCategories,
      measuresList: state => state.formManager.measures,
      cascadingList: state => state.formManager.cascadingLevels,
      mainOfficesChildrenList: state => state.external.mainOfficesChildren,
    }),
  },
  data() {
    const drawerConfig = this.drawerConfig
    const formObject = this.formObject
    return {
      formItemLayout: {
        labelCol: { span: 6 },
        wrapperCol: { span: 14 },
      },
      SHOW_PARENT,
      isSubmmiting: false,
      config: drawerConfig,
      normalizer: {
        title: 'name',
        value: 'id',
      },
      form: formObject,
      cachedOffice: {
        implementing: [],
        supporting: [],
      },
      storedOffices: {
        implementing: [],
        supporting: [],
      },
    }
  },
  watch: {
    drawerConfig(val) {
      this.config = val
    },
    formObject(val) {
      this.form = val
    },
  },
  methods: {
    onOfficeChange() {
      const args = [...arguments] /* 0 - value, 1 - label, 2 - extra, 3 - field */
      const extra = args[2]
      const field = args[3]
      this.storedOffices[field] = []
      const { allCheckedNodes } = extra
      if (typeof allCheckedNodes !== 'undefined' && allCheckedNodes.length > 0) {
        allCheckedNodes.forEach(item => {
          const { dataRef } = (typeof item.node !== 'undefined') ? item.node.data.props : item.data.props
          this.storedOffices[field].push(dataRef)
        })
      }
    },
    filterBasisOption(input, option) {
      return (
        option.componentOptions.children[0].text.toUpperCase().indexOf(input.toUpperCase()) >= 0
      )
    },
    changeNullValue(value, label) {
      if (typeof value === 'undefined' || value === 0) {
        this.form[label] = null
      }
    },
    validateFields() {
      this.isSubmmiting = !this.isSubmmiting
      const { form } = this
      const tempImplementing = this.mappedOfficeList(form.implementing, 'implementing')
      const tempSupporting = this.mappedOfficeList(form.supporting, 'supporting')
      form.implementing = tempImplementing
      form.supporting = tempSupporting
      const self = this
      setTimeout(() => {
        this.$refs[this.drawerId].validate(valid => {
          if (valid) {
            if (form.options.supporting.length) {
              Modal.confirm({
                title: 'The Supporting Office was not saved',
                content: 'Data will be lost if you proceed. Do you still want to continue?',
                okText: 'Yes',
                cancelText: 'No',
                onOk() {
                  self.saveForm()
                },
                onCancel() {
                  self.isSubmmiting = !self.isSubmmiting
                },
              })
            } else {
              self.saveForm()
            }
          } else {
            console.log('errror')
            self.isSubmmiting = !self.isSubmmiting
            return false
          }
        })
      }, 500)
    },
    saveForm() {
      const { config, form } = this
      const messageKey = 'updatable'
      let msgContent = ''
      const self = this
      for (var office in this.storedOffices) {
        self.storedOffices[office] = []
      }
      if (config.updateId === null) {
        this.$emit('add-table-item', form)
        msgContent = 'Added!'
      } else {
        this.$emit('update-table-item', { formData: form, updateId: config.updateId })
        msgContent = 'Updated!'
      }

      this.$emit('reset-form')
      if (config.type !== 'pi') {
        const { parentDetails } = config
        this.$emit('add-sub-pi', parentDetails.key)
      }
      this.$message.success({ content: msgContent, messageKey, duration: 2 })
        .then(() => {
          self.isSubmmiting = !self.isSubmmiting
        })
    },
    resetFormData(createNew) {
      this.$emit('close-modal', createNew)
    },
    saveOfficeList(field) {
      const { form, cachedOffice, storedOffices } = this
      const list = storedOffices[field]
      form[field] = this.mappedOfficeList(list, field)
      form.options[field] = []
      storedOffices[field] = []
      if (cachedOffice[field].length) {
        cachedOffice[field] = []
      }
    },
    updateOfficeList(field) {
      const { form, cachedOffice, storedOffices } = this
      form.options[field] = form[field]
      cachedOffice[field] = form[field]
      storedOffices[field] = form[field]
      form[field] = []
    },
    deleteOfficeItem(field, index) {
      const { form } = this
      Modal.confirm({
        title: 'Are you sure you want to delete this?',
        content: '',
        okText: 'Yes',
        cancelText: 'No',
        onOk() {
          form[field].splice(index, 1)
        },
        onCancel() {},
      })
    },
    mappedOfficeList(list, field) {
      const cascadeTo = field === 'implementing' ? 'core_functions' : 'support_functions'
      const { cachedOffice } = this
      return list.map(item => {
        const container = {}
        let tempCascadeTo = ''
        container.value = item.value
        container.label = typeof item.title !== 'undefined' ? item.title : item.label
        if (typeof item.children !== 'undefined') {
          container.children = true
        } else {
          container.acronym = item.acronym
          container.pId = item.pId
        }
        const hasCached = cachedOffice[field].filter(i => i.value === item.value)
        if (hasCached.length) {
          tempCascadeTo = hasCached[0].cascadeTo
        } else if (typeof (item.cascadeTo) !== 'undefined' && item.cascadeTo) {
          tempCascadeTo = item.cascadeTo
        } else {
          tempCascadeTo = cascadeTo
        }
        container.cascadeTo = tempCascadeTo
        return container
      })
    },
  },
}
