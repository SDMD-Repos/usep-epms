<template>
  <div>
    <a-select v-model:value="year" placeholder="Select year" style="width: 200px" @change="fetchSettings">
      <template v-for="(y, i) in years" :key="i">
        <a-select-option :value="y">
          {{ y }}
        </a-select-option>
      </template>
    </a-select>

    <div class="mt-5">
      <form-table :year="year" :is-create="createFieldPermission"  @handle-save="handleSave"/>
    </div>
  </div>
</template>
<script>
import { defineComponent, ref, computed, onMounted } from "vue"
import { useStore } from "vuex";
import FormTable from './partials/formTable'


export default defineComponent({
  name: 'FieldsManager',
  components: { FormTable },
  setup() {
    const store = useStore()

    // DATA
    const year = ref(new Date().getFullYear())

    // COMPUTED
    const years = computed(() => {
      const max = new Date().getFullYear() + 1
      const min = 10
      const lists = []
      for (let i = max; i >= (max - min); i--) {
        lists.push(i)
      }
      return lists
    })
    const createFieldPermission = computed(() => store.getters['system/permission'].createFieldPermission)


    // EVEMTS
    onMounted(() => {
      fetchSettings(year.value)

       const fieldPermissions = [
        "manager",
        "m-form", 
        "mf-fields", 
      ]
      // store.dispatch('system/CHECK_CREATE_FIELD_PERMISSION', { payload: fieldPermissions })
       store.dispatch('system/CHECK_MANAGER_PERMISSION', { payload: {permission: fieldPermissions, name:'createFieldPermission'} })

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

    return {
      year,
      years,
      createFieldPermission,
      fetchSettings,
      handleSave,
    }
  },
})
</script>
