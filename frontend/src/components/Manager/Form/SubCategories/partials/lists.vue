<template>
  <a-table bordered :data-source="subCategoryList" :columns="columns">
    <template #bodyCell="{ column, text, record }">
      <template v-if="column.dataIndex === 'ordering'">
        <div class="editable-cell">
          <div v-if="editableData[record.key]" class="editable-cell-input-wrapper">
            <div class="row">
              <div col-lg-4>
                <a-input-number v-if="editableData[record.key].parent_id" onpaste="return event.charCode >= 48 && event.charCode <= 57"
                                onkeypress="return event.charCode >= 48 && event.charCode <= 57"
                                :min="1" :disabled="true" v-model:value="editableData[record.key].orderingParent" style="width: 50%;" />
              </div>
              <div col-lg-4>
                <a-input-number v-if="editableData[record.key].parent_id" onpaste="return event.charCode >= 48 && event.charCode <= 57"
                                onkeypress="return event.charCode >= 48 && event.charCode <= 57"
                                v-model:value="editableData[record.key].ordering"
                                @pressEnter="update(record.key)" style="width: 50%;"/>
                <a-input-number v-else onpaste="return event.charCode >= 48 && event.charCode <= 57"
                                onkeypress="return event.charCode >= 48 && event.charCode <= 57"
                                v-model:value="editableData[record.key].ordering"
                                @pressEnter="update(record.key)" style="width: 50%"/>
              </div>
              <div col-lg-4>
                <check-outlined class="editable-cell-icon-check" @click="update(record.key)" />
                <close-outlined class="editable-cell-icon-close" @click="cancelUpdate(record.key)" />
              </div>
            </div>
          </div>
          <div v-else class="editable-cell-text-wrapper">
            {{ text || ' ' }}
            <edit-outlined v-if="isCreate" class="editable-cell-icon" @click="edit(record.key)" />
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
import { WarningOutlined } from '@ant-design/icons-vue'
import { CheckOutlined, EditOutlined, CloseOutlined } from '@ant-design/icons-vue';
import { cloneDeep } from 'lodash'
import {Modal} from "ant-design-vue";

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
    WarningOutlined,
    CloseOutlined,
  },
  props: {
    subCategoryList: {
      required: true,
      type: Array,
    },
    isDelete : Boolean,
    isCreate : Boolean,
    allAccess: Boolean,
  },
  emits: ['delete','fetch-details'],
  setup(props, { emit } ) {
    const store = useStore()
    const loading = computed( () => store.getters['formManager/manager'].loading)
    const editableData = reactive({});
    const previousInput = ref([]);

    // METHODS
    const onDelete = key => {
      emit('delete', key)
    }

    const fetchDetails  = () => {
      emit('fetch-details')
    }

    const onUpdate = obj => {
      store.dispatch('formManager/UPDATE_SUB_CATEGORY', { payload: obj })
    }

    const isObjectEmpty = (obj) => {
      return (obj && Object.keys(obj).length === 0)
    }

    const getSubcategory = (key) => {
      let subCat = {}
      if (isObjectEmpty(props.subCategoryList))
        return
      for (let i of props.subCategoryList){
        if (i.key === key) subCat = i
        if (isObjectEmpty(i.children) === false){
          for (let ii of i.children){
            if (ii.key === key) subCat = ii
          }
        }
      }
      return subCat
    }
    const edit = key => {
      let subCategory = getSubcategory(key)
      previousInput.value[key] = cloneDeep(subCategory)
      if (subCategory.parent_id){
        subCategory.orderingParent = subCategory.ordering.toString().split('.')[0]
        subCategory.ordering = subCategory.ordering.toString().split('.')[1]
        editableData[key] = subCategory
      }
      else{
        editableData[key] = subCategory
      }
    };

    const update = key => {
      let subCategory = getSubcategory(key)
      if (subCategory.ordering){
        let isValid = true
        if (subCategory.parent_id !== null){
          if (subCategory.ordering.toString() === previousInput.value[key].ordering.toString().split('.')[1]) return
            let parentSubCategory = getSubcategory(subCategory.parent_id)
            if(parseFloat(parentSubCategory.ordering + "." + subCategory.ordering) !== parseFloat(previousInput.value[key].ordering)){
              if (parentSubCategory.children && Object.keys(parentSubCategory.children).length > 0){
                for (let obj of parentSubCategory.children){
                  if (parseInt(obj.id) !== parseInt(subCategory.id) && parseFloat(parentSubCategory.ordering+"."+subCategory.ordering) === parseFloat(obj.ordering)){
                    isValid = false
                    break
                  }
                }
                subCategory.ordering = parentSubCategory.ordering + "." + subCategory.ordering
              }
            }
        }else{
          if(subCategory.ordering === previousInput.value[key].ordering) return

          for (let obj of props.subCategoryList){
            if (parseFloat(subCategory.ordering) === parseFloat(obj.ordering) && subCategory.id !== obj.id && parseInt(subCategory.category_id) === parseInt(obj.category_id)){
              isValid = false
              break
            }
          }

        }
        if (isValid){
          Object.assign(subCategory, editableData[key])
          delete editableData[key]
          onUpdate(subCategory)
        }else{
          if (subCategory.parent_id){
            subCategory.ordering = previousInput.value[key].ordering.toString().split('.')[1]
          }else{
            subCategory.ordering = previousInput.value[key].ordering
          }

          Modal.error({
            title: () => 'Unable to save the form',
            content: () => 'The Ordering for this Sub Category has already been used',
          })
        }
      }
    };
    const cancelUpdate = key => {
      delete editableData[key]
      let subCategory = getSubcategory(key)
      subCategory.ordering = previousInput.value[key].ordering
    }
    return {
      columns,
      loading,
      onDelete,

      editableData,
      edit,
      update,
      cancelUpdate,
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
  .editable-cell-icon-check,.editable-cell-icon-close {
    position: absolute;
    width: 20px;
    cursor: pointer;
    right: 0;
  }

  .editable-cell-icon-check{
    margin-right: 25px;
  }

  .editable-cell-icon {
    margin-top: 4px;
  }

  .editable-cell-icon-check,.editable-cell-icon-close {
    line-height: 35px;
  }

  .editable-cell-icon:hover,
  .editable-cell-icon-check:hover,.editable-cell-icon-close:hover {
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
