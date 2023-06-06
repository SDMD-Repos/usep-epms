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
<!--          <a-tree-select
            v-model:value="member.officeId"
            style="width: 100%"
            :dropdown-style="{ maxHeight: '400px', overflow: 'auto' }"
            :tree-data="officeList"
            placeholder="Select office"
            tree-node-filter-prop="title"
            show-search
            allow-clear
            label-in-value
            :disabled="actionType === 'view'"
            @change="(value, label, extra) => { getPersonnelList(value, label, extra, 'member') }"
          />
          <a-input-group compact class="mt-2">
            <a-tree-select
              v-model:value="member.id"
              style="width: 88%"
              :dropdown-style="{ maxHeight: '400px', overflow: 'auto' }"
              :tree-data="memberList"
              placeholder="Select Personnel"
              tree-node-filter-prop="title"
              show-search
              allow-clear
              label-in-value
              :disabled="actionType === 'view'"
            />
            <a-button type="primary" class="mr-3" :disabled="!member.id" @click="addMember">
              <template #icon><UserAddOutlined /></template>
            </a-button>
          </a-input-group>
          <div class="mt-3">
            <a-list item-layout="horizontal" :data-source="form.members">
              <template #renderItem="{ item, index }">
                <a-list-item>
                  <template #actions v-if="actionType !== 'view'">
                    <a @click="deleteMember(index)" >
                      delete
                    </a>
                  </template>
                  {{ item.id.label }}
                </a-list-item>
              </template>
            </a-list>
          </div>-->
        </a-form-item>
      </a-form>
    </a-spin>
  </a-modal>
</template>

<script>
import {ref, defineComponent, watch, computed} from "vue";
// import { UserAddOutlined } from "@ant-design/icons-vue";

export default defineComponent({
  name: 'FormModalOffice',
  components: { /*UserAddOutlined*/ },
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
    // DATA
    let isVisible = ref()
    let form = ref()
    let formLoading = ref(false)

    const labelCol = { span: 6 }
    const wrapperCol = { span: 16 }

    // COMPUTED
    const years = computed(() => {
      const now = new Date().getFullYear()
      const max = 10
      const lists = []
      for (let i = now; i <= (now + max); i++) {
        lists.push(i)
      }
      return lists
    })

    // EVENTS
    watch(() => [props.visible, props.formState] , ([visible, formState]) => {
      isVisible.value = visible
      form.value = formState
    })

    return {
      isVisible,
      form,
      formLoading,
      labelCol,
      wrapperCol,

      years,
    }
  },
})
</script>
