<template>
  <div>
    <a-table :columns="formTableColumns" size="middle" bordered
             :scroll="{ x: 'calc(2600px + 50%)', y: 600 }" >
      <template #title>
        <a-button type="primary">New</a-button>
      </template>

      <template #subCategory="{ record }">
        {{ (record.type === 'pi' && record.subCategory !== null) ? record.subCategory.label : ''}}
      </template>
    </a-table>
  </div>
</template>
<script>
import { defineComponent } from "vue"
import { formTableColumns } from '@/services/columns'

export default defineComponent({
  name: "IndicatorListTable",
  props: {
    formId: { type: String, default: "" },
  },
  setup() {
    return {
      formTableColumns,
    }
  },
})
</script>
<style lang="scss">
@import "@/components/Tables/Forms/style.module.scss";
</style>
