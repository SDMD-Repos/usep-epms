<template>
  <div>
    <a-select v-model:value="year" placeholder="Select year" style="width: 200px" @change="fetchPrograms">
      <template v-for="(y, i) in years" :key="i">
        <a-select-option :value="y">
          {{ y }}
        </a-select-option>
      </template>
    </a-select>

    <a-spin :spinning="loading">
      <a-form layout="vertical"
              ref="formRef"
              :model="formState"
              :rules="rules">
        <div class="row">
          <div class="col-lg-6">
            <a-form-item ref="name" label="Program Name" name="name">
              <a-input v-model:value="formState.name" />
            </a-form-item>
          </div>
          <div class="col-lg-4">
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
          </div>
          <div class="col-lg-2">
            <a-form-item ref='percentage' label="Percentage" name="percentage">
              <a-input-number v-model:value="formState.percentage" :min="1" :max="100" style="width: 100%"/>
            </a-form-item>
          </div>
        </div>
        <template #title>
          <a-button type="primary" class="mr-3" @click="openModal('create', null)">
            <template #icon><PlusOutlined /></template>
            New Program
          </a-button>
          <a-button type="link" v-if="previousPrograms.length" @click="openPreviousPrograms">Add {{ year - 1}} Programs</a-button>
        </template>
      </a-form>
      <programs-previous-list :visible="isPreviousViewed" :year="year" :list="previousPrograms"
                              @save-measures="onMultipleSave" @close-modal="closePreviousPrograms" />
      <programs-table :year="year" />
    </a-spin>
  </div>
</template>
<script>
import { computed, defineComponent, reactive, ref, toRaw, createVNode, onMounted } from 'vue'
import { useStore } from 'vuex'
import { ExclamationCircleOutlined } from '@ant-design/icons-vue'
import { Modal } from 'ant-design-vue'
import ProgramsTable from './partials/lists'
import ProgramsPreviousList from './partials/previousList'

export default defineComponent({
  components: {
    ProgramsTable,
    ProgramsPreviousList,
  },
  setup() {
    const store = useStore()
    const year = ref(new Date().getFullYear())
    const functions = computed(() => store.getters['formManager/functions'])
    const loading = computed(() => store.getters['formManager/manager'].loading)
    const programsList = computed(() => store.getters['formManager/manager'].programs)
    const previousPrograms = computed(() => store.getters['formManager/manager'].previousPrograms)
    const formRef = ref()
    const formState = reactive({
      name: '',
      year: year.value,
      category_id: undefined,
      percentage: null,
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
      percentage: [
        {
          type: 'integer',
          message: 'This field must be a number',
          trigger: 'blur',
        },
      ],
    }
    const normalizer = {
      title: 'name',
      value: 'id',
    }

    onMounted(() => {
      store.dispatch('formManager/FETCH_FUNCTIONS')
      fetchPrograms(year.value)
    })

    // METHODS

    const fetchPrograms = async selectedYear => {
      await store.dispatch('formManager/FETCH_PROGRAMS', { payload : { year: selectedYear, isPrevious: false }})
      await store.dispatch('formManager/FETCH_PROGRAMS', { payload : { year: (selectedYear - 1), isPrevious: true }})
    }

    const onSubmit = () => {
      formRef.value
        .validate()
        .then(() => {
          Modal.confirm({
            title: () => 'Are you sure you want to create this program?',
            icon: () => createVNode(ExclamationCircleOutlined),
            content: () => '',
            onOk() {
              store.dispatch('formManager/CREATE_PROGRAM', { payload: toRaw(formState) })
              resetForm()
            },
            onCancel() {},
          });
        })
        .catch(error => {
          console.log('error', error);
        });
    };

    const openPreviousPrograms = () => {
      isPreviousViewed.value = true
    }

    const closePreviousPrograms = () => {
      isPreviousViewed.value = false
    }

    const resetForm = () => {
      formRef.value.resetFields();
    };

    return {
      formRef,
      formState,
      rules,
      normalizer,
      functions,
      loading,
      years,
      year,
      onSubmit,
      openPreviousPrograms,
      resetForm,
      fetchPrograms,

    };
  },
});
</script>
