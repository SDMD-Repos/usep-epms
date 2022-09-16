<template>
<div v-if="opcrFormPermission" >
  <a-select v-model:value="year" placeholder="Select year" style="width: 200px" @change="fetchAllPrograms">
    <template v-for="(y, i) in years" :key="i">
      <a-select-option :value="y">
        {{ y }}
      </a-select-option>
    </template>
  </a-select>
  <div class="mt-5">
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
                <a-select-option
                  v-for="func in functions" :value="func.id.toString()"
                  :key="func.id" :label="func.name">
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
          <a-button style="width: 150px;" type="primary" class="mr-3" @click="onSubmit">Add</a-button>
          <a-button type="link" v-if="previousPrograms.length" @click="changePreviousModal">Add {{ year - 1}} Programs</a-button>
        </div>
      </a-form>

      <programs-table :year="year" :form-id="formId" :all-programs="programs" />

      <previous-list :visible="isPreviousViewed" :year="year" :list="previousPrograms"
                     @close-modal="changePreviousModal" @save-programs="onMultipleSave"/>

    </a-spin>
  </div>
  </div>
   <div v-else><error403 /></div>
</template>
<script>
import { defineComponent, reactive, ref, toRaw, createVNode, onMounted, computed } from 'vue'
import { useStore } from 'vuex'
import { ExclamationCircleOutlined } from '@ant-design/icons-vue'
import { Modal } from 'ant-design-vue'
import { usePermission } from '@/services/functions/permission'
import ProgramsTable from './partials/lists'
import PreviousList from './partials/previousList'
import Error403 from '@/components/Errors/403'

export default defineComponent({
  name: "OtherProgramsManager",
  components: { ProgramsTable, PreviousList, Error403 },
  props: {
    formId: { type: String, default: null },
  },
  setup(props) {
    const store = useStore()

    // DATA
    const year = ref(new Date().getFullYear())
    const formRef = ref()
    const isPreviousViewed = ref(false)

    const formState = reactive({
      name: '',
      year: year.value,
      category_id: undefined,
      percentage: null,
      formId:props.formId,
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

    const normalizer = { title: 'name', value: 'id' }

    const permission = {
      listOpcr: [ "form", "f-opcr", "fo-manager" ],
    }

    const { opcrFormPermission } = usePermission(permission)

    // COMPUTED
    const functions = computed(() => store.getters['formManager/functions'])
    const loading = computed(() => store.getters['formManager/manager'].loading)

    const programs = computed(() => store.getters['formManager/manager'].programs)
    const previousPrograms = computed(() => store.getters['formManager/manager'].previousPrograms)

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
      store.commit('formManager/SET_STATE', { programs: [], previousPrograms: [] })
      if(opcrFormPermission.value){
          fetchAllPrograms(year.value)
      }
    })

    // METHODS
    const fetchAllPrograms = async selectedYear => {
      resetForm()
      await store.dispatch('formManager/FETCH_FUNCTIONS', { payload: { year: selectedYear, isPrevious: false }})
      await fetchPrograms(selectedYear)
    }

    const fetchPrograms = async selectedYear => {
      await store.dispatch('formManager/FETCH_PROGRAMS', { payload : { year: selectedYear, isPrevious: false, formId: props.formId }})
      await store.dispatch('formManager/FETCH_PROGRAMS', { payload : { year: (selectedYear - 1), isPrevious: true, formId: props.formId }})
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
              formState.form_id = props.formId
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
      const selectedProgram = keys[0]
      const selectedFunction = keys[1]

      const saveKeys = previousPrograms.value.filter(item => {
        return selectedProgram.indexOf(item.key) !== -1
      })

      saveKeys.forEach(item => {
        const data = {
          name: item.name,
          year: year.value,
          category_id: selectedFunction,
          percentage: item.percentage,
          formId: props.formId,
        }
        store.dispatch('formManager/CREATE_OTHER_PROGRAM', { payload: data })
      })
      changePreviousModal()
    }

    return {
      year,
      formRef,
      isPreviousViewed,
      formState,
      rules,
      normalizer,
      opcrFormPermission,

      functions,
      loading,
      programs,
      previousPrograms,
      years,

      onSubmit,
      resetForm,
      fetchAllPrograms,
      changePreviousModal,
      onMultipleSave,

    };
  },
});
</script>
