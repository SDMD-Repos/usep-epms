<template>
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
          <a-button style="width: 150px;" type="primary" class="mr-3" @click="onSubmit">Add</a-button>
          <a-button type="link" v-if="previousOtherPrograms.length" @click="changePreviousModal">Add {{ year - 1}} Programs</a-button>
        </div>
      </a-form>

      <programs-table :year="year" :form_id="form_id"/>

      <previous-list :visible="isPreviousViewed" :year="year" :list="previousOtherPrograms"
                     @close-modal="changePreviousModal" @save-programs="onMultipleSave"/>
    </a-spin>
  </div>
</template>
<script>
import { defineComponent, reactive, ref, toRaw, createVNode, onMounted, computed } from 'vue'
import { useStore } from 'vuex'
import { ExclamationCircleOutlined } from '@ant-design/icons-vue'
import { Modal } from 'ant-design-vue'
import ProgramsTable from './partials/lists'
import PreviousList from './partials/previousList'

export default defineComponent({
  name: "OtherProgramsManager",
  components: {
    ProgramsTable,
    PreviousList,
  },
  setup() {
    const form_id = 'opcr'
    const store = useStore()
    const year = ref(new Date().getFullYear())
    const functions = computed(() => store.getters['formManager/functions'])
    const loading = computed(() => store.getters['formManager/manager'].loading)
    const programs = computed(() => store.getters['formManager/manager'].programs)
    const otherPrograms = computed(() => store.getters['formManager/manager'].otherPrograms)
    const allPrograms = computed(() => {
      const programData = addArrayColumn(programs.value,"is_other",false) && typeof addArrayColumn(programs.value) != 'undefined' ? addArrayColumn(programs.value) : []
      const otherProgramData = addArrayColumn(otherPrograms.value,"is_other",true) && typeof addArrayColumn(otherPrograms.value) != 'undefined' ? addArrayColumn(otherPrograms.value) : []
      return programData.concat(otherProgramData)
    })
    const previousOtherPrograms = computed(() => store.getters['formManager/manager'].previousOtherPrograms)

    const formRef = ref()
    const isPreviousViewed = ref(false)
    const formState = reactive({
      name: '',
      year: year.value,
      category_id: undefined,
      percentage: null,
      form_id:form_id,
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

    // EVENTS
    onMounted(() => {
      store.commit('formManager/SET_STATE', { previousOtherPrograms: [] })
      store.dispatch('formManager/FETCH_FUNCTIONS', { payload: { year: year.value, isPrevious: false }})
      fetchAllPrograms(year.value)
    })

    // METHODS
    const fetchAllPrograms = async selectedYear => {
      await fetchPrograms(selectedYear)
      await fetchOtherPrograms(selectedYear)



      // await store.commit('formManager/SET_STATE', { allPrograms: allProgramData })
    }

    const addArrayColumn = (data,index,value) => {
      console.log(data)
      return data.map(x => Object.assign({}, x, { index: value }))
    }

    const fetchPrograms = async selectedYear => {
      await store.dispatch('formManager/FETCH_PROGRAMS', { payload : { form_id: form_id, year: selectedYear, isPrevious: false }})
      await store.dispatch('formManager/FETCH_PROGRAMS', { payload : { form_id: form_id, year: (selectedYear - 1), isPrevious: true }})
    }

    const fetchOtherPrograms = async selectedYear => {
      await store.dispatch('formManager/FETCH_OTHER_PROGRAMS', { payload : { form_id: form_id, year: selectedYear, isPrevious: false }})
      await store.dispatch('formManager/FETCH_OTHER_PROGRAMS', { payload : { form_id: form_id, year: (selectedYear - 1), isPrevious: true }})
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
              store.dispatch('formManager/CREATE_OTHER_PROGRAM', { payload: toRaw(formState) })
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
      const saveKeys = previousOtherPrograms.value.filter(item => {
        return keys.indexOf(item.key) !== -1
      })

      saveKeys.forEach(item => {
        const data = {
          name: item.name,
          year: year.value,
          category_id: item.category_id,
          percentage: item.percentage,
          form_id: form_id,
        }
        store.dispatch('formManager/CREATE_OTHER_PROGRAM', { payload: data })
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
      onSubmit,
      resetForm,
      fetchAllPrograms,
      changePreviousModal,
      previousOtherPrograms,
      isPreviousViewed,
      onMultipleSave,
      PreviousList,
      ProgramsTable,
      form_id,

      allPrograms,
      otherPrograms,
      programs,
    };
  },
});
</script>
