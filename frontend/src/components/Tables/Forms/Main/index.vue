<template>
  <div>
    <a-table :columns="formTableColumns" :data-source="filteredSource" size="middle" bordered
             :scroll="{ x: scrollX, y: 600 }">
      <template #title>
        <a-button type="primary" :disabled="disabledNew === false ? disabledNew : !allowEdit" @click="openDrawer('Add')">New</a-button>
      </template>

      <template #footer v-if="filteredSource && filteredSource.length && formId === `aapcr`">
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

      <template #headerCell="{ column }">
        <template v-if="column.key === 'target'"> {{ year }} </template>
      </template>

      <template #bodyCell="{ column, record }">
        <template v-if="column.key === 'subCategory'">
          {{ (record.type === 'pi' && (typeof record.subCategory !== 'undefined' && record.subCategory !== null)) ? record.subCategory.label : ''}}
        </template>

        <template v-if="column.key === 'count'">
          {{ record.count }}
        </template>

        <template v-if="column.key === 'isHeader'">
          <div v-if="record.type === 'pi'">
            <CheckCircleFilled v-if="record.isHeader" :style="{ fontSize: '18px', color: '#2b5c17' }" />
            <CloseCircleFilled v-else :style="{ fontSize: '18px', color: '#eb2f2f' }" />
          </div>
        </template>

        <template v-if="column.key === 'measures'">
          <ul class="form-ul-list" role="list">
            <li v-for="measure in record.measures" :key="measure.key">
              <span v-if="!measure.option.displayAsItems && !measure.option.display_as_items">
                {{ measure.label.children || measure.label }}
              </span>
              <span v-else >
                <template v-for="item in measure.option.items" :key="item.id">
                  <span>{{ item.rate }} - {{ item.description }}</span> <br />
                </template>
              </span>
            </li>
          </ul>
        </template>

        <template v-if="column.key === 'budget'">
          {{ $filters.numbersWithCommasDecimal(record.budget) }}
        </template>

        <template v-if="column.key === 'cascadingLevel'">
          <div v-if="!record.isHeader">
            {{ record.cascadingLevel.label.children || record.cascadingLevel.label}}
          </div>
        </template>

        <template v-if="column.key === 'implementing'">
          <ul class="form-ul-list">
            <li v-for="office in record.implementing" :key="office.key">
              {{ office.label }}
            </li>
          </ul>
        </template>

        <template v-if="column.key === 'supporting'">
          <ul class="form-ul-list">
            <li v-for="office in record.supporting" :key="office.key">
              {{ office.label }}
            </li>
          </ul>
        </template>

        <template v-if="column.key === 'operation'">
          <EditFilled v-if="!record.isHeader || (record.isHeader && !record.isCascaded)" @click="handleEdit(record)" />
          <template v-if="record.type === 'pi'">
            <a-divider v-if="!record.isHeader || (record.isHeader && !record.isCascaded)" type="vertical" />
            <PlusCircleFilled @click="handleAddSub(record)"/>
          </template>
          <a-divider type="vertical" v-if="allowedAction(record)"/>
          <a-popconfirm
            title="Are you sure you want to delete this?"
            @confirm="handleDelete(record)"
            ok-text="Yes" cancel-text="No"
            v-if="allowedAction(record)"
          >
            <DeleteFilled />
          </a-popconfirm>
        </template>
      </template>
    </a-table>
  </div>
</template>
<script>
import { defineComponent, ref, inject, computed } from 'vue'
import { Modal } from 'ant-design-vue'
import {
  CheckCircleFilled, CloseCircleFilled, EditFilled, PlusCircleFilled, DeleteFilled, PlusOutlined,
} from '@ant-design/icons-vue'

export default defineComponent({
  name: "IndicatorListTable",
  components: { CheckCircleFilled, CloseCircleFilled, EditFilled, PlusCircleFilled, DeleteFilled, PlusOutlined },
  props: {
    year: { type: Number, default: new Date().getFullYear() },
    formId: { type: String, default: "" },
    functionId: { type: Number, default: null },
    itemSource: { type: Array, default: () => { return [] }},
    budgetList: { type: Array, default: () => { return [] }},
    mainCategory: { type: Object, default: () => { return {} }},
    disabledNew: { type: Boolean, default: () => { return true }},
    formTableColumns: { type: Array, default: () => { return [] }},
    allowEdit: { type: Boolean, default: false },
  },
  emits: ['open-drawer', 'delete-item', 'add-sub-item', 'edit-item', 'add-budget-list-item'],
  setup(props, { emit }) {
    const _message = inject('a-message')

    // DATA
    const categoryBudget = ref(null)
    const scrollX = ref('calc(2600px + 50%)')
    let filteredSource

    //COMPUTED
    switch (props.formId) {
      case 'aapcr':
        filteredSource = computed(()=> {
          return props.itemSource.filter(i => i.program === props.mainCategory.key)
        })
        break;
      case 'vpopcr':
        filteredSource = computed(()=> {
          const source = props.itemSource.filter(i => i.category === props.functionId)
          source.forEach((x, y) => {
            source[y].count = y + 1
          })
          return source
        })
        break;
      case 'opcrtemplate':
      case 'opcr':
        filteredSource = computed(()=> {
          const source = props.itemSource.filter(i => i.program === props.mainCategory.key)
          source.forEach((x, y) => {
            source[y].count = y + 1
          })
          return source
        })
        scrollX.value = 'calc(1400px + 50%)'
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

    const handleAddSub = data => {
      emit('add-sub-item', data)
    }

    const handleDelete = data => {
      emit('delete-item', data)
    }

    const allowedAction = data => {
      let allow = false
      if (props.formId === 'aapcr' || (props.formId === 'vpopcr' && !data.isCascaded) || props.formId === 'opcrtemplate') {
        allow = true
      }
      return allow
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
        _message.success('Saved!', 2)
      }
    }

    const resetBudget = () => {
      categoryBudget.value = null
    }

    return {
      scrollX,
      categoryBudget,

      filteredSource,
      programBudget,

      openDrawer,
      handleEdit,
      handleAddSub,
      handleDelete,
      allowedAction,
      saveProgramBudget,
    }
  },
})
</script>
<style lang="scss">
@import "@/components/Tables/Forms/style.module.scss";
</style>
