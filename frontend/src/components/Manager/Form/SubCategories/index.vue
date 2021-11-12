<template>
  <a-spin :spinning="loading">
      <a-form layout="vertical"
              ref="formRef"
              :model="formState"
              :rules="rules">
        <div class="row">
          <div class="col-lg-4">
            <a-form-item ref="name" label="Sub Category Name" name="name">
              <a-input v-model:value="formState.name" />
            </a-form-item>
          </div>
          <div class="col-lg-4">
            <a-form-item ref="category_id" label="Functions" name="category_id">
              <a-select v-model:value="formState.category_id">
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
        </div>
      </a-form>
      <sub-categories-table :sub-category-list="subCategories" @delete="onDelete"/>
  </a-spin>
</template>
<script>
import { computed, defineComponent, reactive, ref, toRaw, createVNode, onMounted } from 'vue'
import { useStore } from 'vuex'
import { ExclamationCircleOutlined } from '@ant-design/icons-vue'
import { Modal } from 'ant-design-vue'
import SubCategoriesTable from './partials/lists'

export default defineComponent({
  components: {
    SubCategoriesTable,
  },
  setup() {
    const store = useStore()

    // COMPUTED
    const functions = computed(() => store.getters['formManager/functions'])
    const subCategories = computed(() => store.getters['formManager/subCategories'])
    const loading = computed(() => store.getters['formManager/manager'].loading)
    const parentSubs = computed(() => {
      const parents = subCategories.value.filter((i) => {
        return i.category_id === formState.category_id
      })
      parents.forEach(item => {
        item.selectable = true
      })
      return parents
    })

    // DATA
    const formRef = ref()
    const formState = reactive({
      name: '',
      category_id: undefined,
      parentId: null,
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
      store.dispatch('formManager/FETCH_SUB_CATEGORIES')
      store.dispatch('formManager/FETCH_FUNCTIONS')
    })

    // METHODS
    const onDelete = key => {
      store.dispatch('formManager/DELETE_SUB_CATEGORY', { payload: key })
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

    return {
      functions,
      subCategories,
      loading,
      parentSubs,

      formRef,
      formState,
      rules,
      normalizer,

      onSubmit,
      resetForm,
      onDelete,
    };
  },
});
</script>
