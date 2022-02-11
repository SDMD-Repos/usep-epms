<template>
  <div>
    <indicator-table :year="year" :form-id="formId" :function-id="functionId" :form-table-columns="modifiedTableColumns" :item-source="itemSource" />
  </div>
</template>
<script>
import { defineComponent, ref, onMounted } from "vue"
import IndicatorTable from '@/components/Tables/Forms/Main'
import { formTableColumns } from "@/services/columns"

export default defineComponent({
  name: 'OpcrVpItems',
  components: { IndicatorTable },
  props: {
    year: { type: Number, default: new Date().getFullYear() },
    formId: { type: String, default: "" },
    functionId: { type: String, default: "" },
    itemSource: { type: Array, default: () => { return [] }},
  },
  setup() {
    const modifiedTableColumns = ref()

    onMounted(() => {
      modifyColumns()
    })

    // METHODS
    const modifyColumns = () => {
      let columns = JSON.parse(JSON.stringify(formTableColumns))
      const remarksIndex = columns.findIndex(i => i.key === 'remarks')
      columns[remarksIndex].title = "Remarks"
      const deleteKeys = ['subCategory', 'cascadingLevel']
      columns = [...columns.filter(i => deleteKeys.indexOf(i.key) === -1)]
      const addendum = {
        title: '#',
        key: 'count',
        dataIndex: 'count',
        className: 'column-count',
        width: 60,
      }
      columns.splice(0, 0, addendum)
      modifiedTableColumns.value = columns
    }

    return {
      modifiedTableColumns,
    }
  },
})
</script>
