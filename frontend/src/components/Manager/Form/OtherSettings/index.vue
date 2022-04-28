<template>
  <div>
    <a-spin :spinning="loading">
      <a-select v-model:value="year" placeholder="Select year" style="width: 200px" @change="fetchSettings">
        <template v-for="(y, i) in years" :key="i">
          <a-select-option :value="y">
            {{ y }}
          </a-select-option>
        </template>
      </a-select>

      <div class="mt-5">
        <fields-table :year="year" :is-create="isCreate"  @handle-save="handleSave"/>
        <br />
        <functions-table :year="year" :is-create="isCreate" @handle-save="updateFunctionProgram" />
      </div>
    </a-spin>
  </div>
</template>
<script>
import { defineComponent, ref, onMounted, computed } from "vue"
import { useStore } from "vuex";
import FieldsTable from './partials/fieldsTable'
import functionsTable from './partials/functionsTable'
import { usePermission } from '@/services/functions/permission'

export default defineComponent({
  name: 'OtherSettingsManager',
  components: { FieldsTable, functionsTable },
  setup() {
    const store = useStore()

    // DATA
    const year = ref(new Date().getFullYear())

    // COMPUTED
    const loading = computed(() => store.getters['formManager/manager'].loading)

    const years = computed(() => {
      const max = new Date().getFullYear() + 1
      const min = 10
      const lists = []
      for (let i = max; i >= (max - min); i--) {
        lists.push(i)
      }
      return lists
    })

    const permission ={
                        listCreate: ["manager", "m-form", "mf-fields" ],
                      }
    const {
        // DATA
      isCreate,
        // METHODS
    } = usePermission(permission)
    
    // EVEMTS
    onMounted(() => {
      fetchSettings(year.value)
    })

    //METHODS
    const fetchSettings = value => {
      store.dispatch('formManager/FETCH_FORM_FIELDS', { payload: { year: value }})
      store.dispatch('formManager/FETCH_FUNCTIONS', { payload: { year: value }})
    }

    const handleSave = data => {
      const { code, id } = data

      switch (code) {
        case 'implementing':
        case 'supporting':
          const params = {
            year: year.value,
            fieldId: id,
            setting: data.settings.settings,
          }

          if(!data.isUpdate) {
            store.dispatch('formManager/SAVE_FORM_FIELD_SETTINGS', { payload: { ...params }})
          } else {
            params.settingId = data.settings.settingId
            store.dispatch('formManager/UPDATE_FORM_FIELD_SETTINGS', { payload: { ...params }})
          }

          break;
        default:
          console.log("Try looking up for a hint.")
      }
    }

    const updateFunctionProgram = data => {
      store.dispatch('formManager/UPDATE_PROGRAM_FUNCTION',
        { payload: { id: data.id, defaultProgram: data.defaultProgram, year: year.value } })
    }

    return {
      year,

      loading,
      years,
      isCreate,

      fetchSettings,
      handleSave,
      updateFunctionProgram,
    }
  },
})
</script>
