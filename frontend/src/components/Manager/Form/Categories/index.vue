<template>
  <a-spin :spinning="loading">
    <a-form layout="vertical"
            ref="formRef"
            :model="formState"
            :rules="rules">
      <div class="row">
        <div class="col-lg-5">
          <a-form-item ref="name" label="Function Name" name="name">
            <a-input v-model:value="formState.name" />
          </a-form-item>
        </div>
<!--        <div class="col-lg-4">
          <a-form-item ref="category_id" label="Functions" name="category_id">
            <a-select v-model:value="formState.category_id" >
              <a-select-option v-for="func in functions"
                               :value="func.id"
                               :key="func.id"
                               :label="func.name">
                {{ func.name }}
              </a-select-option>
            </a-select>
          </a-form-item>
        </div>-->
        <div class="col-lg-2">
          <a-form-item ref='percentage' label="Percentage" name="percentage">
            <a-input-number v-model:value="formState.percentage" :min="1" :max="100" style="width: 100%"/>
          </a-form-item>
        </div>
      </div>
      <div class="form-actions mt-0">
        <a-button style="width: 150px;" type="primary" class="mr-3" @click="onSubmit">Add</a-button>
      </div>
    </a-form>
    <categories-table />
  </a-spin>
</template>
<script>
import { computed, defineComponent, reactive, ref, toRaw, createVNode, onMounted } from 'vue'
import { useStore } from 'vuex'
import { ExclamationCircleOutlined } from '@ant-design/icons-vue'
import { Modal } from 'ant-design-vue'
import CategoriesTable from './partials/lists'

export default defineComponent({
  components: {
    CategoriesTable,
  },
  setup() {
    const store = useStore()
    const loading = computed(() => store.getters['formManager/manager'].loading)
    const formRef = ref()
    const formState = reactive({
      name: '',
      percentage: null,
    })
    const rules = {
      name: [
        {
          required: true,
          message: 'This field is required',
          trigger: 'blur',
        },
      ],
      percentage: [
        {
          type: 'number',
          message: 'This field must be a number',
          trigger: 'blur',
        },
      ],
    }
    const normalizer = {
      title: 'name',
      value: 'id',
    }

    // onMounted(() => {
    //   store.dispatch('formManager/FETCH_PROGRAMS')
    //   store.dispatch('formManager/FETCH_FUNCTIONS')
    // })

    // METHODS
    const onSubmit = () => {
      formRef.value
        .validate()
        .then(() => {
          Modal.confirm({
            title: () => 'Are you sure you want to create this function?',
            icon: () => createVNode(ExclamationCircleOutlined),
            content: () => '',
            onOk() {
              store.dispatch('formManager/CREATE_FUNCTION', { payload: toRaw(formState) })
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
      formRef,
      formState,
      rules,
      normalizer,
      loading,
      onSubmit,
      resetForm,
    };
  },
});
</script>
