<template>
  <a-spin :spinning="loading">
   <a-select v-model:value="year" placeholder="Select year" style="width: 200px" @change="fetchFunctions">
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
              :rules="rules">
        <div class="row">
          <div class="col-lg-4">
            
             <a-form-item ref='name' label="Sub Category Name" name="name">
               <a-select v-model:value="formState.name"  v-if="checked"  >
                <a-select-option v-for="categories in categories"
                                 :value="categories.name"
                                 :key="categories.id"
                                 :label="categories.name">
                  {{ categories.name }}
                </a-select-option>
              </a-select>
               <a-input v-model:value="formState.name" v-else />
            </a-form-item>
            
             
            
          </div>
          <div class="col-lg-4">
            <a-form-item ref="category_id" label="Functions" name="category_id">
              <a-select v-model:value="formState.category_id" @change="onFunctionsChange">
                <a-select-option v-for="func in functions"
                                 :value="func.id"
                                 :key="func.id"
                                 :label="func.name">
                  {{ func.name }}
                </a-select-option>
              </a-select>
            </a-form-item>
          </div>
          <div class="col-lg-4">
            <a-form-item ref='parentId' label="Parent Sub Category" name="parentId">
              <a-tree-select
                v-model:value="formState.parentId"
                style="width: 100%"
                :dropdown-style="{ maxHeight: '400px', overflow: 'auto' }"
                :tree-data="parentSubs"
                :replace-fields="normalizer"
                allow-clear
                tree-default-expand-all
              >
              </a-tree-select>
            </a-form-item>
          </div>
        </div>
        <div class="form-actions mt-0">
          <a-button style="width: 150px;" type="primary" class="mr-3" @click="onSubmit">Add</a-button>
           <a-checkbox v-model:checked="checked" v-if="previousCategories.length" @change="isCheck" >
                Add {{ year - 1}} Sub Categories
                </a-checkbox>
          <a-button type="link" v-if="previousCategories.length" @click="changePreviousModal"></a-button>
        </div>
      </a-form>
    </div>

    <sub-categories-table :sub-category-list="subCategories" @delete="onDelete"/>
  </a-spin>
</template>
<script>
import { computed, defineComponent, reactive, ref, toRaw, createVNode, onMounted} from 'vue'
import { useStore } from 'vuex'
import { ExclamationCircleOutlined } from '@ant-design/icons-vue'
import { Modal } from 'ant-design-vue'
import SubCategoriesTable from './partials/lists'

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
    const previousCategories = computed(() => store.getters['formManager/manager'].previousCategories)
    const categories = computed(() => store.getters['formManager/manager'].previousCategories)
    
    const parentSubs = computed(() => {
      const parents = subCategories.value.filter((i) => {
        return i.category_id === parseInt(formState.category_id)
      })
      parents.forEach(item => {
        item.selectable = true
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
    const formState = reactive({
      name: '',
      category_id: undefined,
      parentId: null,
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
    }
    const normalizer = {
      title: 'name',
      value: 'id',
    }

    // EVENTS
    onMounted(() => {
       store.commit('formManager/SET_STATE', { previousCategories: [] })
      fetchFunctions(year.value)
    })


    const onDelete = key => {
      store.dispatch('formManager/DELETE_SUB_CATEGORY', { payload: { id: key, year: year.value }  })
    }

    const fetchFunctions = year => {
      resetForm()
      store.dispatch('formManager/FETCH_FUNCTIONS', { payload: { year: year, isPrevious: false }})
      fetchSubCategories(year)
    }

    const fetchSubCategories = year => {
        store.dispatch('formManager/FETCH_SUB_CATEGORIES', { payload: { year: year, isPrevious: false }})
        store.dispatch('formManager/FETCH_SUB_CATEGORIES', { payload: { year: year -1 , isPrevious: true }})
    }

    const onFunctionsChange = () => {
      formState.parentId = null
    }

    const onSubmit = () => {
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
    };

    const resetForm = () => {
      formRef.value.resetFields();
    };

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
      previousCategories,
      categories,
      
      checked,
      formRef,
      formState,
      rules,
      normalizer,
      
      isCheck,
      onDelete,
      fetchFunctions,
      onFunctionsChange,
      onSubmit,
      resetForm,
      changePreviousModal,
    };
  },
});
</script>
