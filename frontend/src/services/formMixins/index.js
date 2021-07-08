export default {
  props: ['formId'],
  data() {
    return {
      isFinalized: false,
      enableForm: false,
      editMode: false,
      deletedIds: [],
    }
  },
  methods: {
    updateSourceCount(data) {
      this.counter = data
    },
    updateDataSource(data) {
      this.dataSource = data
    },
    updateDrawerStatus(data) {
      this.drawer = data
    },
    addTargetsBasisItem(data) {
      this.targetsBasisList.push(data)
    },
    addDeletedId(data) {
      this.deletedIds.push(data)
    },
  },
}
