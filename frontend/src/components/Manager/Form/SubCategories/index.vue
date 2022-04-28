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
          <div class="col-lg-4">
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

          <div class="col-lg-4">
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
          <a-button style="width: 150px;" type="primary" class="mr-3"   @click="onSubmit">Add</a-button>
           <a-checkbox v-model:checked="checked" v-if="prevSubCategories.length"   @change="resetForm" >
                Add {{ year - 1}} Sub Categories
            </a-checkbox>
          <a-button type="link" v-if="prevSubCategories.length" @click="changePreviousModal"></a-button>
        </div>
      </a-form>
    </div>

    <sub-categories-table :sub-category-list="subCategories" :is-delete="isDelete"  @delete="onDelete"/>
  </a-spin>
</template>
<script>
import { computed, defineComponent, reactive, ref, toRaw, createVNode, onMounted} from 'vue'
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

    const permission ={
                      listCreate: ["manager","m-form", "mf-subcat","mfs-create"],
                      listDelete: ["manager","m-form", "mf-subcat","mfs-delete"],
                    }
    const {
        // DATA
      isCreate,isDelete,
        // METHODS
    } = usePermission(permission)

    // EVENTS

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
    };
  },
});
</script>
