<template>
  <a-table bordered :data-source="subCategoryList" :columns="columns">
    <template #bodyCell="{ column, text, record }">
      <template v-if="column.dataIndex === 'ordering'">
        <div class="editable-cell">
          <div v-if="editableData[record.key]" class="editable-cell-input-wrapper">
            <a-input onpaste="return event.charCode >= 48 && event.charCode <= 57"
                     onkeypress="return event.charCode >= 48 && event.charCode <= 57"
                     v-model:value="editableData[record.key].ordering"
                     @pressEnter="update(record.key)" />
            <check-outlined class="editable-cell-icon-check" @click="update(record.key)" />
          </div>
          <div v-else class="editable-cell-text-wrapper">
            {{ text || ' ' }}
            <edit-outlined class="editable-cell-icon" @click="edit(record.key)" />
          </div>
        </div>
      </template>
      <template v-if="column.key === 'operation'">
        <a-popconfirm
          title="Are you sure you want to delete this?"
          @confirm="onDelete(record.key)"
          ok-text="Yes"
          cancel-text="No"
          v-if="isDelete"
        >
          <template #icon><warning-outlined /></template>
          <a type="primary">Delete</a>
        </a-popconfirm>
      </template>
    </template>
  </a-table>
</template>

<script>
import { defineComponent, computed, reactive, ref } from 'vue';
import { useStore } from 'vuex'
// import { WarningOutlined } from '@ant-design/icons-vue'
import { CheckOutlined, EditOutlined } from '@ant-design/icons-vue';
import { cloneDeep } from 'lodash';
const columns = [
  { title: 'Name', dataIndex: 'name', key: 'name' },
  { title: 'Function', dataIndex: ['category', 'name'], key: 'function' },
  { title: 'Ordering', dataIndex: 'ordering', key: 'ordering' },
  { title: 'Action', dataIndex: 'operation', key: 'operation' },
]

export default defineComponent({
  name: 'SubCategoriesTable',
  components: {
    CheckOutlined,
    EditOutlined,
  },
  props: {
    subCategoryList: {
      required: true,
      type: Array,
    },
    isDelete : Boolean,
    allAccess: Boolean,
  },
  emits: ['delete'],
  setup(props, { emit } ) {
    const store = useStore()
    const loading = computed( () => store.getters['formManager/manager'].loading)

    const editableData = reactive({});

    // METHODS
    const onDelete = key => {
      emit('delete', key)
    }

    const onUpdate = obj => {
      store.dispatch('formManager/UPDATE_SUB_CATEGORY', { payload: obj })
    }

    const isObjectEmpty = (obj) => {
      return (typeof obj !== 'undefined' && Object.keys(obj).length === 0)
    }

    const getSubcategory = (key) => {
      let subCat = {}
      if (!isObjectEmpty(props.subCategoryList))
        for (let i of props.subCategoryList){
          if (i.key === key) subCat = i
          if (!isObjectEmpty(i.children)){
            for (let ii of i.children){
              if (ii.key === key) subCat = ii
            }
          }
        }
      return subCat
    }
    const edit = key => {
      editableData[key] = getSubcategory(key);
    };

    const update = key => {
      if (getSubcategory(key).ordering){
        Object.assign(getSubcategory(key), editableData[key]);
        delete editableData[key];
        onUpdate(getSubcategory(key))
      }

    };

    return {
      columns,
      loading,
      onDelete,

      editableData,
      edit,
      update,
    }
  },
})
</script>

<style lang="less">
.editable-cell {
  position: relative;
  .editable-cell-input-wrapper,
  .editable-cell-text-wrapper {
    padding-right: 24px;
  }

  .editable-cell-text-wrapper {
    padding: 5px 24px 5px 5px;
  }

  .editable-cell-icon,
  .editable-cell-icon-check {
    position: absolute;
    right: 0;
    width: 20px;
    cursor: pointer;
  }

  .editable-cell-icon {
    margin-top: 4px;
    display: none;
  }

  .editable-cell-icon-check {
    line-height: 35px;
  }

  .editable-cell-icon:hover,
  .editable-cell-icon-check:hover {
    color: #108ee9;
  }

  .editable-add-btn {
    margin-bottom: 8px;
  }
}
.editable-cell:hover .editable-cell-icon {
  display: inline-block;
}
</style>
