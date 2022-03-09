<template>
 <a-spin :spinning="loading">
    <a-select v-model:value="year" placeholder="Select year" style="width: 200px" @change="fetchData">
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
          <div class="form-actions mt-0">
            <a-button style="width: 150px;" type="primary" class="mr-3" @click="onSubmit" :disabled="!isCreate && !allAccess">Add</a-button>
            <a-button type="link" v-if="previousPrograms.length" :disabled="!isCreate && !allAccess" @click="changePreviousModal">Add {{ year - 1}} Programs</a-button>
          </div>
        </a-form>

        <programs-table :year="year" :is-delete="isDelete" :all-access="allAccess" />
        <previous-list :visible="isPreviousViewed" :year="year" :list="previousPrograms"
                       @close-modal="changePreviousModal" @save-programs="onMultipleSave"/>
    </div>
     </a-spin>
</template>
<script>
import { defineComponent, reactive, ref, toRaw, createVNode, onMounted, computed } from 'vue'
import { useStore } from 'vuex'
import { ExclamationCircleOutlined } from '@ant-design/icons-vue'
import { Modal } from 'ant-design-vue'
import ProgramsTable from './partials/lists'
import PreviousList from './partials/previousList'
import { usePermissionProgram } from '@/services/functions/permission/programs'

export default defineComponent({
  name: "ProgramsManager",
  components: {
    ProgramsTable,
    PreviousList,
  },
  setup() {
    const store = useStore()
    const year = ref(new Date().getFullYear())
    const functions = computed(() => store.getters['formManager/functions'])
    const loading = computed(() => store.getters['formManager/manager'].loading)
    const previousPrograms = computed(() => store.getters['formManager/manager'].previousPrograms)

    const formRef = ref()
    const isPreviousViewed = ref(false)
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

    const {
      // DATA
      isDelete, isCreate, allAccess,
      // METHODS

    } = usePermissionProgram()
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

    // EVENTS
    onMounted(() => {
      store.commit('formManager/SET_STATE', { previousPrograms: [] })
      fetchData(year.value)
    })

    // METHODS
    const fetchData = async selectedYear => {
      resetForm()
      await store.dispatch('formManager/FETCH_FUNCTIONS', { payload: { year: selectedYear, isPrevious: false }})
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
              formState.year = year.value
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

    const resetForm = () => {
      formRef.value.resetFields();
    };

    const changePreviousModal = () => {
      isPreviousViewed.value = !isPreviousViewed.value
    }

    const onMultipleSave = keys => {
      const saveKeys = previousPrograms.value.filter(item => {
        return keys.indexOf(item.key) !== -1
      })

      saveKeys.forEach(item => {
        const data = {
          name: item.name,
          year: year.value,
          category_id: item.category_id,
          percentage: item.percentage,
        }
        store.dispatch('formManager/CREATE_PROGRAM', { payload: data })
      })
      changePreviousModal()
    }

    return {
      formRef,
      formState,
      rules,
      normalizer,
      functions,
      loading,
      years,
      year,
      isCreate,
      allAccess,
      isDelete,
      onSubmit,
      resetForm,
      fetchData,
      changePreviousModal,
      previousPrograms,
      isPreviousViewed,
      onMultipleSave,
    };
  },
});
</script>
