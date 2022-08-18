<template>
  <a-spin :spinning="loading">
   <a-select v-model:value="year" placeholder="Select year" style="width: 200px" @change="yearOnchange">
    <template v-for="(y, i) in years" :key="i">
      <a-select-option :value="y">
        {{ y }}
      </a-select-option>
    </template>
    </a-select>

    <div class="mt-5">
      <a-form layout="vertical"
              ref="formRef"
              :model="formState"
              :rules="rules"
              v-if="isCreate">
        <div class="row">
          <div class="col-lg-3">
            <a-form-item ref='name' label="Sub Category Name" name="name">
              <a-select v-model:value="formState.name"  v-if="checked"  >
                <a-select-option v-for="previous in prevSubCategories"
                                 :value="previous.name"
                                 :key="previous.id"
                                 :label="previous.name">
                  {{ previous.name }}
                </a-select-option>
              </a-select>
              <a-input v-model:value="formState.name" v-else />
            </a-form-item>
          </div>

          <div class="col-lg-3">
            <a-form-item ref="category_id" label="Functions" name="category_id">
              <a-select v-model:value="formState.category_id" @change="onFunctionsChange">
                <a-select-option v-for="func in functions"
                                 :value="func.id.toString()"
                                 :key="func.id"
                                 :label="func.name">
                  {{ func.name }}
                </a-select-option>
              </a-select>
            </a-form-item>
          </div>

          <div class="col-lg-2">
            <a-form-item ref='parentId' label="Parent Sub Category" name="parentId">
              <a-tree-select
                v-model:value="formState.parentId"
                style="width: 100%"
                :dropdown-style="{ maxHeight: '400px', overflow: 'auto' }"
                :tree-data="parentSubs"
                :field-names="normalizer"
                allow-clear
                tree-default-expand-all
              >
              </a-tree-select>
            </a-form-item>
          </div>
          <div class="col-lg-2">
            <a-form-item ref='ordering' label="Ordering" name="ordering">
              <a-input-number onpaste="return event.charCode >= 48 && event.charCode <= 57" onkeypress="return event.charCode >= 48 && event.charCode <= 57" :min="1" :disabled="orderingDisabled" v-model:value="frmOrdering" style="width: 100%" />
            </a-form-item>
          </div>
          <div class="col-lg-2">
            <a-form-item ref='' label=" " name="">
              <a-input-number onpaste="return event.charCode >= 48 && event.charCode <= 57" onkeypress="return event.charCode >= 48 && event.charCode <= 57" :min="1" :disabled="orderingChildDisabled" v-model:value="frmOrderingChild" style="width: 100%" />
            </a-form-item>
          </div>
        </div>
        <div class="form-actions mt-0">
          <a-button style="width: 150px;" type="primary" class="mr-3"   @click="onSubmit">Add</a-button>
           <a-checkbox v-model:checked="checked" v-if="prevSubCategories.length"   @change="resetForm" >
                Add {{ year - 1}} Sub Categories
            </a-checkbox>
          <a-button type="link" v-if="prevSubCategories.length" @click="changePreviousModal"></a-button>
        </div>
      </a-form>
    </div>

    <sub-categories-table :sub-category-list="subCategories" :is-delete="isDelete"  :is-create="isCreate" @delete="onDelete"/>
  </a-spin>
</template>
<script>
import {computed, defineComponent, reactive, ref, toRaw, createVNode, onMounted, watch} from 'vue'
import { useStore } from 'vuex'
import { ExclamationCircleOutlined } from '@ant-design/icons-vue'
import { Modal } from 'ant-design-vue'
import SubCategoriesTable from './partials/lists'
import { usePermission } from '@/services/functions/permission'

export default defineComponent({
  name: "SubCategoriesManager",
  components: {
    SubCategoriesTable,
  },
  setup() {
    const store = useStore()
    const year = ref(new Date().getFullYear())

    // COMPUTED
    const functions = computed(() => store.getters['formManager/functions'])
    const subCategories = computed(() => store.getters['formManager/subCategories'])
    const loading = computed(() => store.getters['formManager/manager'].loading)
    const prevSubCategories = computed(() => store.getters['formManager/manager'].prevSubCategories)

    const parentSubs = computed(() => {
      const parents = subCategories.value.filter((i) => {
        return i.category_id === parseInt(formState.category_id)
      })
      parents.forEach(item => {
        item.selectable = true
        if(typeof item.children !== 'undefined' && item.children.length > 0) {
          item.children.forEach(i => { i.selectable = false })
        }
      })
      return parents
    })
    const years = computed(() => {
      const max = new Date().getFullYear() + 1
      const min = 10
      const lists = []
      for (let i = max; i >= (max - min); i--) {
        lists.push(i)
      }
      return lists
    })
    const checked = ref(false);
    const isPreviousViewed = ref(false)

    // DATA
    const formRef = ref()
    const frmOrdering = ref();
    const frmOrderingChild = ref();
    const orderingDisabled = ref(false);
    const orderingChildDisabled = ref(false);

    const formState = reactive({
      name: '',
      category_id: undefined,
      parentId: null,
      ordering: null,
      year : year.value,
    })
    const rules = {
      name: [
        {
          required: true,
          message: 'This field is required',
          trigger: 'blur',
        },
      ],
      category_id: [
        {
          required: true,
          message: 'This field is required',
          trigger: 'blur',
        },
      ],
      ordering: [
        {
          required: true,
          message: 'This field is required',
          trigger: 'blur',
        },
      ],
    }
    const normalizer = {
      label: 'name',
      value: 'id',
    }

    const permission = {
      listCreate: ["manager","m-form", "mf-subcat","mfs-create"],
      listDelete: ["manager","m-form", "mf-subcat","mfs-delete"],
    }

    const { isCreate,isDelete } = usePermission(permission)

    // EVENTS
    watch(() => [frmOrdering.value,frmOrderingChild.value] , ([frmOrdering,frmOrderingChild]) => {
      formState.ordering = frmOrderingChild ? (frmOrdering + "." + frmOrderingChild) : frmOrdering
    })

    watch(() => [formState.category_id,formState.parentId] , ([category_id,parentId]) => {
      if (category_id){
        let order = 1
        let filteredParentSubCategories = subCategories.value.filter(datum => (datum.category_id === parseInt(category_id) && !datum.parent_id))
        if (filteredParentSubCategories && Object.keys(filteredParentSubCategories).length){
          for (let i=0; i < filteredParentSubCategories.length; i++){
            if (filteredParentSubCategories[i].ordering > order){
              order = filteredParentSubCategories[i].ordering
            }
          }
          order++
        }
        frmOrdering.value = order
        if (parentId){
          orderingDisabled.value = true
          orderingChildDisabled.value = false
          let filteredSubCategories = filteredParentSubCategories.filter(datum => datum.id === parseInt(parentId))
          if (filteredSubCategories && Object.keys(filteredSubCategories).length > 0){
            frmOrdering.value = filteredSubCategories[0].ordering ? filteredSubCategories[0].ordering : 1
            frmOrderingChild.value = filteredSubCategories[0].children && Object.keys(filteredSubCategories[0].children).length > 0 ? (filteredSubCategories[0].children.length + 1) : 1
          }
        }else{
          orderingDisabled.value = false
          orderingChildDisabled.value = true
          frmOrderingChild.value = null
        }
      }
    })

    onMounted(() => {
      store.commit('formManager/SET_STATE', { prevSubCategories: [] })
      fetchData(year.value)
    })

    const onDelete = key => {
      store.dispatch('formManager/DELETE_SUB_CATEGORY', { payload: { id: key, year: year.value }  })
    }

    const yearOnchange = year => {
      resetForm()
      checked.value = false;
      fetchData(year)
    }

    const fetchData = year => {
      store.dispatch('formManager/FETCH_FUNCTIONS', { payload: { year: year, isPrevious: false }})
      store.dispatch('formManager/FETCH_SUB_CATEGORIES', { payload: { year: year, isPrevious: false }})
      store.dispatch('formManager/FETCH_SUB_CATEGORIES', { payload: { year: year - 1 , isPrevious: true }})
    }

    const onFunctionsChange = () => {
      formState.parentId = null
    }

    const onSubmit = () => {
      let isValid = true
      if (subCategories.value && Object.keys(subCategories.value).length > 0){
        for (let obj of subCategories.value){
          if (parseInt(formState.category_id) === obj.category_id && formState.ordering === obj.ordering){
            isValid = false
            break
          }
          if (obj.children && Object.keys(obj.children).length > 0){
            for (let cObj of obj.children){
              if (parseInt(formState.category_id) === cObj.category_id && formState.ordering === cObj.ordering){
                isValid = false
                break
              }
            }
          }
        }
      }

      if (isValid){
        formRef.value
          .validate()
          .then(() => {
            Modal.confirm({
              title: () => 'Are you sure you want to create this sub category?',
              icon: () => createVNode(ExclamationCircleOutlined),
              content: () => '',
              onOk() {
                formState.year = year.value
                formState.parentId = typeof formState.parentId === 'undefined' ? null : formState.parentId
                store.dispatch('formManager/CREATE_SUB_CATEGORY', { payload: toRaw(formState) })
                resetForm()
              },
              onCancel() {},
            });
          })
          .catch(error => {
            console.log('error', error);
          });
      }else
        Modal.error({
          title: () => 'Unable to save the form',
          content: () => 'The Ordering for this Sub Category has already been used',
        })
    };

    const resetForm = () => {
      if (formRef.value){
        formRef.value.resetFields()
      }
      orderingDisabled.value = false
      orderingChildDisabled.value = false
      frmOrdering.value = null
      frmOrderingChild.value = null
    }

    const changePreviousModal = () => {
      isPreviousViewed.value = !isPreviousViewed.value
    }

    const isCheck = () => {
      formRef.value.resetFields();
    };

    return {
      year,
      functions,
      subCategories,
      loading,
      years,
      parentSubs,
      isPreviousViewed,
      prevSubCategories,
      isCreate,
      isDelete,
      checked,
      formRef,
      formState,
      rules,
      normalizer,

      isCheck,
      onDelete,
      yearOnchange,
      onFunctionsChange,
      onSubmit,
      resetForm,
      changePreviousModal,

      frmOrdering,
      frmOrderingChild,
      orderingDisabled,
      orderingChildDisabled,
    };
  },
});
</script>
