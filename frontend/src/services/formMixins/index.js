export default {
  props: ['formId'],
  data() {
    return {
      year: new Date().getFullYear(),
      dataSource: [],
      targetsBasisList: [],
      isFinalized: false,
      enableForm: false,
      editMode: false,
      deletedIds: [],
      activeKey: '0',
    }
  },
  computed: {
    years() {
      const now = new Date().getFullYear()
      const min = 10
      const lists = []
      for (let i = now; i >= (now - min); i--) {
        lists.push(i)
      }
      return lists
    },
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
