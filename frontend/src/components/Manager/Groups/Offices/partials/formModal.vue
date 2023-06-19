<template>
  <a-modal v-model:visible="isVisible" :title="modalTitle" :closable="false"
           :mask-closable="false" width="45%">
    <a-spin :spinning="formLoading">
      <a-form :label-col="labelCol"
              :wrapper-col="wrapperCol">
        <a-form-item label="Name" v-bind="validateInfos.name">
          <a-input v-model:value="form.name" :disabled="actionType === 'view'" />
        </a-form-item>

        <a-form-item label="Effective until" v-bind="validateInfos.effectivity">
          <a-select v-model:value="form.effectivity" placeholder="Select year" style="width: 200px" :disabled="actionType === 'view'">
            <template v-for="(y, i) in years" :key="i">
              <a-select-option :value="y">
                {{ y }}
              </a-select-option>
            </template>
          </a-select>
        </a-form-item>

        <a-form-item label="Supervising Office" v-bind="validateInfos.supervising">
          <a-select v-model:value="form.supervising"
                    placeholder="Select Supervising Office"
                    option-label-prop="title"
                    :options="supervisingList"
                    :disabled="actionType === 'view'"
                    label-in-value>
            <template #option="{ title }">
              {{ title }}
            </template>
          </a-select>
        </a-form-item>

        <a-form-item>
          <template #label>
            <span class="required-indicator">Offices</span>
          </template>
          <a-tree-select
            v-model:value="form.offices"
            style="width: 100%" placeholder="Select office/s" tree-node-filter-prop="title"
            :dropdown-style="{ maxHeight: '400px', overflow: 'auto' }" :tree-data="officeList"
            :show-checked-strategy="SHOW_PARENT" :max-tag-count="6"
            allow-clear tree-checkable label-in-value
            @change="(value, label, extra) => { onOfficeChange() }" />
        </a-form-item>
      </a-form>
    </a-spin>
  </a-modal>
</template>

<script>
import { ref, defineComponent, watch, onMounted, computed } from "vue";
import { TreeSelect } from 'ant-design-vue'
import { useStore } from "vuex";

export default defineComponent({
  name: 'FormModalOffice',
  props: {
    visible: Boolean,
    modalTitle: {
      type: String,
      default: '',
    },
    actionType: {
      type: String,
      default: '',
    },
    supervisingList: {
      type: Array,
      default: () => { return [] },
    },
    formState: {
      type: Object,
      default: () => {
        return {
          id: null,
          name: '',
          effectivity: new Date().getFullYear(),
          supervising: undefined,
          offices: [],
          deleted: [],
        }
      },
    },
    validateInfos: {
      type: Object,
      default: () => { return {} },
    },
  },
  setup(props) {
    const store = useStore()

    // STATIC DATA
    const SHOW_PARENT = TreeSelect.SHOW_PARENT

    // DATA
    const currentYear = ref(new Date().getFullYear())

    let isVisible = ref()
    let form = ref()
    let formLoading = ref(false)

    const labelCol = { span: 6 }
    const wrapperCol = { span: 16 }

    // COMPUTED
    const years = computed(() => {
      const now = currentYear.value
      const max = 10
      const lists = []
      for (let i = now; i <= (now + max); i++) {
        lists.push(i)
      }
      return lists
    })

    const officeList  = computed(() => store.getters['external/external'].officesAccountable)

    // EVENTS
    watch(() => [props.visible, props.formState] , ([visible, formState]) => {
      isVisible.value = visible
      form.value = formState
    })

    onMounted(() => {
      onLoad()
    })

    // METHODS
    const onLoad = () => {
      let params = {
        checkable: { allColleges: true, mains: true },
        groups: { included: true },
        isAcronym: true,
        currentYear: currentYear.value,
      }
      store.dispatch('external/FETCH_OFFICES_ACCOUNTABLE', { payload: params })
    }

    const onOfficeChange = () => {

    }

    return {
      SHOW_PARENT,
      isVisible,
      form,
      formLoading,
      labelCol,
      wrapperCol,

      years,
      officeList,

      onOfficeChange,
    }
  },
})
</script>
