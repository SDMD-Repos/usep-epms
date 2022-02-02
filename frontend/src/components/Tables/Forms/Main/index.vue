<template>
  <div>
    <a-table :columns="formTableColumns" :data-source="itemSource" size="middle" bordered
             :scroll="{ x: 'calc(2600px + 50%)', y: 600 }" >
      <template #title>
        <a-button type="primary" :disabled="Object.keys(mainCategory).length === 0" @click="openDrawer('Add')">New</a-button>
      </template>

      <template #subCategory="{ record }">
        {{ (record.type === 'pi' && record.subCategory !== null) ? record.subCategory.label : ''}}
      </template>

      <template #isHeader="{ record }">
        <div v-if="record.type === 'pi'">
          <CheckCircleFilled v-if="record.isHeader" :style="{ fontSize: '18px', color: '#2b5c17' }" />
          <CloseCircleFilled v-else :style="{ fontSize: '18px', color: '#eb2f2f' }" />
        </div>
      </template>

      <template #targetYearColumn>
        {{ year }}
      </template>

      <template #measures="{ record }">
        <ul class="form-ul-list">
          <li v-for="measure in record.measures" :key="measure.key">
            {{ measure.label.children }}
          </li>
        </ul>
      </template>

<!--      <template #budget="{ record }">-->
<!--        {{ $filters.numbersWithCommasDecimal(record.budget) }}-->
<!--      </template>-->
    </a-table>
  </div>
</template>
<script>
import { defineComponent } from "vue"
import { formTableColumns } from '@/services/columns'
import { CheckCircleFilled, CloseCircleFilled } from '@ant-design/icons-vue'

export default defineComponent({
  name: "IndicatorListTable",
  components: { CheckCircleFilled, CloseCircleFilled },
  props: {
    year: { type: Number, default: new Date().getFullYear() },
    formId: { type: String, default: "" },
    itemSource: { type: Array, default: () => { return [] }},
    mainCategory: { type: Object, default: () => { return {} }},
  },
  emits: ['open-drawer'],
  setup(props, { emit }) {

    //METHODS
    const openDrawer = action => {
      emit('open-drawer', action)
    }

    return {
      formTableColumns,

      openDrawer,
    }
  },
})
</script>
<style lang="scss">
@import "@/components/Tables/Forms/style.module.scss";
</style>
