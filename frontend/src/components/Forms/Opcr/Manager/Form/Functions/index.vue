<template>
  <a-spin :spinning="loading">
    <div class="categories_display_name">
      <a-select v-model:value="year" placeholder="Select year" style="width: 200px" >
        <template v-for="(y, i) in years" :key="i">
          <a-select-option :value="y">
            {{ y }}
          </a-select-option>
        </template>
      </a-select>

      <div class="mt-4" />

      <functions-form :year="year" :form-id="formId" @handle-save="saveDisplayName" @handle-clear="clearDisplayName"/>
    </div>
  </a-spin>
</template>
<script>
import { defineComponent, reactive, ref, toRaw, createVNode, computed } from 'vue'
import { useStore } from 'vuex'
import { ExclamationCircleOutlined } from '@ant-design/icons-vue'
import { Modal } from 'ant-design-vue'
import FunctionsForm from './partials/form'

export default defineComponent({
  name: "OpcrFunctionsManager",
  components: { FunctionsForm },
  props: {
    formId: { type: String, default: '' },
  },
  setup(props) {
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

    // METHODS
    const saveDisplayName = data => {
      const params = {
        year: year.value,
        formId: props.formId,
        categoryId: data.id,
        name: data.modifiedName,
      }

      store.dispatch('formManager/SAVE_FORM_CATEGORY', { payload: params })
    }

    const clearDisplayName = data => {
      const params = {
        id: data.form_category.id,
        year: year.value,
        formId: props.formId,
      }

      store.dispatch('formManager/DELETE_FORM_CATEGORY', { payload: params })
    }

    return {
      year,
      loading,
      years,

      saveDisplayName,
      clearDisplayName,
    };
  },
});
</script>
<style scoped>
.categories_display_name {
  padding: calc(8px + 1.5625vw);
  /*text-align: center;*/
}

/* Mobile */
@media (min-width:400px) {
  .categories_display_name {
    padding: calc(14px + 1.5625vw);
  }
}

/* Tablet */
@media (min-width:600px) {
  .categories_display_name {
    padding: calc(20px + 1.5625vw);
  }
}

</style>
