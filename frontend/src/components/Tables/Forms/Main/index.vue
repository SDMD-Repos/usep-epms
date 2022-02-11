<template>
  <div>
    <a-table :columns="formTableColumns" :data-source="filteredSource" size="middle" bordered
             :scroll="{ x: 'calc(2600px + 50%)', y: 600 }" >
      <template #title>
        <a-button type="primary" :disabled="Object.keys(mainCategory).length === 0" @click="openDrawer('Add')">New</a-button>
      </template>

      <template #footer v-if="filteredSource.length && formId === `aapcr`">
        <a-row :gutter="0">
          <a-col :xs="{ span: 5 }" :sm="{ span: 5 }" :md="{ span: 5 }" :lg="{ span: 2}">
            <label>Budget: </label>
          </a-col>
          <template v-if="!programBudget.length">
            <a-col :xs="{ span: 12}" :sm="{ span: 12 }" :md="{ span: 8 }" :lg="{ span: 5 }">
              <a-input-number v-model:value="categoryBudget" style="width: 100%"
                              :formatter="value => `${value}`.replace(/\B(?=(\d{3})+(?!\d))/g, ',')"
                              :parser="value => value.replace(/\$\s?|(,*)/g, '')"
                              :min="0" @pressEnter="saveProgramBudget"/>
            </a-col>
            <a-col :span="2">
              <a-button type="primary" @click="saveProgramBudget">
                <template #icon><PlusOutlined /></template>
              </a-button>
            </a-col>
          </template>
          <a-col :xs="{ span: 12 }" :sm="{ span: 12 }" :lg="{ span: 4 }" v-else>
            <label><b>₱ {{ $filters.numbersWithCommas(programBudget[0].categoryBudget) }}</b></label>
          </a-col>
        </a-row>
      </template>

      <template #subCategory="{ record }" v-if="formId === `aapcr`">
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
            {{ measure.label.children || measure.label }}
          </li>
        </ul>
      </template>

      <template #budget="{ record }">
        {{ $filters.numbersWithCommasDecimal(record.budget) }}
      </template>

      <template #cascadingLevel="{ record }" v-if="formId === `aapcr`">
        <div v-if="!record.isHeader">
          {{ record.cascadingLevel.label.children || record.cascadingLevel.label}}
        </div>
      </template>

      <template #implementing="{ record }">
        <ul class="form-ul-list">
          <li v-for="office in record.implementing" :key="office.key">
            {{ office.label }}
          </li>
        </ul>
      </template>

      <template #supporting="{ record }">
        <ul class="form-ul-list">
          <li v-for="office in record.supporting" :key="office.key">
            {{ office.label }}
          </li>
        </ul>
      </template>

      <template #action="{ record }">
        <EditFilled @click="handleEdit(record)"/>
        <a-divider type="vertical" />
        <template v-if="record.type === 'pi'">
          <PlusCircleFilled @click="handleAddSub(record.key)"/>
          <a-divider type="vertical" />
        </template>
        <a-popconfirm
          title="Are you sure you want to delete this?"
          @confirm="handleDelete(record)"
          ok-text="Yes"
          cancel-text="No"
        >
          <DeleteFilled />
        </a-popconfirm>
      </template>
    </a-table>
  </div>
</template>
<script>
import { defineComponent, ref, computed } from 'vue'
import { CheckCircleFilled, CloseCircleFilled, EditFilled, PlusCircleFilled, DeleteFilled, PlusOutlined } from '@ant-design/icons-vue'
import { message, Modal } from 'ant-design-vue'

export default defineComponent({
  name: "IndicatorListTable",
  components: { CheckCircleFilled, CloseCircleFilled, EditFilled, PlusCircleFilled, DeleteFilled, PlusOutlined },
  props: {
    year: { type: Number, default: new Date().getFullYear() },
    formId: { type: String, default: "" },
    functionId: { type: String, default: "" },
    itemSource: { type: Array, default: () => { return [] }},
    budgetList: { type: Array, default: () => { return [] }},
    mainCategory: { type: Object, default: () => { return {} }},
    formTableColumns: { type: Array, default: () => { return [] }},
  },
  emits: ['open-drawer', 'delete-item', 'add-sub-item', 'edit-item', 'add-budget-list-item'],
  setup(props, { emit }) {

    // DATA
    const categoryBudget = ref(null)
    let filteredSource

    //COMPUTED
    switch (props.formId) {
      case 'aapcr':
        filteredSource = computed(()=> {
          return props.itemSource.filter(i => i.program === props.mainCategory.key)
        })
        break;
      case 'opcrvp':
        filteredSource = computed(()=> {
          return props.itemSource.filter(i => i.category === props.functionId)
        })
        break;
    }

    const programBudget = computed(() => {
      return props.budgetList.filter(i => i.mainCategory.key === props.mainCategory.key)
    })

    //METHODS
    const openDrawer = action => {
      emit('open-drawer', { action: action })
    }

    const handleEdit = data => {
      emit('edit-item',data)
    }

    const handleAddSub = key => {
      emit('add-sub-item', key)
    }

    const handleDelete = data => {
      emit('delete-item', data)
    }

    const saveProgramBudget = async () => {
      if(!categoryBudget.value) {
        Modal.error({
          title: () => 'No data was saved',
          content: () => 'Please input a valid amount',
        })
      }else {
        await emit('add-budget-list-item', { mainCategory: props.mainCategory, categoryBudget: categoryBudget.value })
        await resetBudget()
        message.success('Saved!', 2)
      }
    }

    const resetBudget = () => {
      categoryBudget.value = null
    }

    return {
      categoryBudget,

      filteredSource,
      programBudget,

      openDrawer,
      handleEdit,
      handleAddSub,
      handleDelete,
      saveProgramBudget,
    }
  },
})
</script>
<style lang="scss">
@import "@/components/Tables/Forms/style.module.scss";
</style>
