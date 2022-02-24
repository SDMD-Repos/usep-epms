<template>
  <a-spin :spinning="loading">
    <a-select v-model:value="year" placeholder="Select year" style="width: 200px" @change="fetchPreviousFunctions">
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
          <div class="col-lg-5">
            <a-form-item ref="name" label="Function Name" name="name">
              <a-input v-model:value="formState.name" />
            </a-form-item>
          </div>

          <div class="col-lg-2">
            <a-form-item ref='percentage' label="Percentage" name="percentage">
              <a-input-number v-model:value="formState.percentage" :min="1" :max="100" style="width: 100%"/>
            </a-form-item>
          </div>
        </div>
        <div class="form-actions mt-0">
          <a-button style="width: 150px;" type="primary" class="mr-3" @click="onSubmit">Add</a-button>
          <a-button type="link" v-if="previousFunctions.length" @click="changePreviousModal">Add {{ year - 1}} functions</a-button>
        </div>
      </a-form>
    </div>

    <functions-table :year="year" />

    <previous-list :visible="isPreviousViewed" :year="year" :list="previousFunctions"
                   @close-modal="changePreviousModal" @save-functions="onMultipleSave"/>
  </a-spin>
</template>
<script>
import {computed, defineComponent, reactive, ref, toRaw, createVNode, onMounted} from 'vue'
import { useStore } from 'vuex'
import { ExclamationCircleOutlined } from '@ant-design/icons-vue'
import { Modal } from 'ant-design-vue'
import FunctionsTable from './partials/lists'
import PreviousList from './partials/previousList'

export default defineComponent({
  name: "FunctionsManager",
  components: {
    FunctionsTable,
    PreviousList,
  },
  setup() {
    const store = useStore()

    // DATA
    const year = ref(new Date().getFullYear())
    const formRef = ref()
    const isPreviousViewed = ref(false)

    const formState = reactive({
      name: '',
      percentage: null,
      year: year.value,
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

    // COMPUTED
    const loading = computed(() => store.getters['formManager/manager'].loading)
    const previousFunctions = computed(() => store.getters['formManager/manager'].previousFunctions)

    const years = computed(() => {
      const max = new Date().getFullYear() + 1
      const min = 10
      const lists = []
      for (let i = max; i >= (max - min); i--) {
        lists.push(i)
      }
      return lists
    })

    // EVENTS
    onMounted(() => {
      store.commit('formManager/SET_STATE', { previousFunctions: [] })
    })

    // METHODS
    const fetchPreviousFunctions = year => {
      resetForm()
      store.dispatch('formManager/FETCH_FUNCTIONS', { payload: { year: (year - 1), isPrevious: true }})
    }

    const onSubmit = () => {
      formRef.value
        .validate()
        .then(() => {
          Modal.confirm({
            title: () => 'Are you sure you want to create this function?',
            icon: () => createVNode(ExclamationCircleOutlined),
            content: () => '',
            onOk() {
              formState.year = year.value
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
    }

    const changePreviousModal = () => {
      isPreviousViewed.value = !isPreviousViewed.value
    }

    const onMultipleSave = keys => {
      const saveKeys = previousFunctions.value.filter(item => {
        return keys.indexOf(item.key) !== -1
      })

      saveKeys.forEach(item => {
        const data = {
          name: item.name,
          year: year.value,
          percentage: item.percentage,
        }
        store.dispatch('formManager/CREATE_FUNCTION', { payload: data })
      })
      changePreviousModal()
    }

    return {
      year,
      formRef,
      formState,
      rules,
      normalizer,
      isPreviousViewed,

      loading,
      years,
      previousFunctions,

      fetchPreviousFunctions,
      onSubmit,
      resetForm,
      changePreviousModal,
      onMultipleSave,
    };
  },
});
</script>
