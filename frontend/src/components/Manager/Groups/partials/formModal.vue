<template>
  <a-modal v-model:visible="isVisible"
           :title="modalTitle"
           :closable="false"
           :mask-closable="false"
           :ok-text="okText"
           :ok-button-props="{disabled: !isEdit }"
           width="45%"
           @ok="okAction"
           @cancel="onClose">
    <a-spin :spinning="formLoading">
      <a-form :label-col="labelCol"
              :wrapper-col="wrapperCol">
        <a-form-item label="Name" v-bind="validateInfos.name">
          <a-input v-model:value="form.name" :disabled="actionType === 'view'" />
        </a-form-item>

        <a-form-item label="Has OIC?">
          <a-switch v-model:checked="form.hasChair" :disabled="actionType === 'view'" />
        </a-form-item>

        <template v-if="form.hasChair">
          <a-form-item v-bind="validateInfos.chairId">
            <template #label>
              <span class="required-indicator">Officer-in-Charge</span>
            </template>
            <a-tree-select
              v-model:value="form.chairOffice"
              style="width: 100%"
              :dropdown-style="{ maxHeight: '400px', overflow: 'auto' }"
              :tree-data="officeList"
              placeholder="Select office"
              tree-node-filter-prop="title"
              show-search
              label-in-value
              :disabled="actionType === 'view'"
              @change="getPersonnelList($event, 'oic')"
            />
            <div class="mt-2">
              <a-tree-select
                v-model:value="form.chairId"
                :dropdown-style="{ maxHeight: '400px', overflow: 'auto' }"
                :tree-data="oicList"
                placeholder="Select Personnel"
                tree-node-filter-prop="title"
                show-search
                label-in-value
                :disabled="actionType === 'view'"
                @change="validate('chairId', { trigger: 'blur' }).catch(() => {})"
              />
            </div>
          </a-form-item>
        </template>

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
            <span class="required-indicator">Members</span>
          </template>
          <a-tree-select
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
            @change="getPersonnelList($event, 'member')"
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
          </div>
        </a-form-item>
      </a-form>
    </a-spin>
  </a-modal>
</template>
<script>
import { defineComponent, watch, ref, reactive, inject, computed } from 'vue'
import { UserAddOutlined } from '@ant-design/icons-vue'
import { getPersonnelByOffice } from '@/services/api/hris'

export default defineComponent({
  name: 'FormModal',
  components: {
    UserAddOutlined,
  },
  props: {
    visible: Boolean,
    modalTitle: {
      type: String,
      default: '',
    },
    okText: {
      type: String,
      default: '',
    },
    actionType: {
      type: String,
      default: '',
    },
    formState: {
      type: Object,
      default: () => {
        return {
          id: null,
          name: '',
          hasChair: false,
          chairOffice: undefined,
          chairId: undefined,
          effectivity: new Date().getFullYear(),
          supervising: undefined,
          members: [],
          deleted: [],
        }
      },
    },
    officeList: {
      type: Array,
      default: () => { return [] },
    },
    supervisingList: {
      type: Array,
      default: () => { return [] },
    },
    validate: {
      type: Function,
      default: () => {},
    },
    validateInfos: {
      type: Object,
      default: () => { return {} },
    },
    isEdit: Boolean,
    isDelete: Boolean,
  },
  emits: ['change-action', 'close-modal', 'submit-form'],
  setup(props, { emit }) {

    const _message = inject('a-message')

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

    // DATA
    const groupRef = ref()

    let isVisible = ref()
    let form = ref()

    let memberList = ref([])
    let oicList = ref([])
    let formLoading = ref(false)

    const labelCol = { span: 6 }
    const wrapperCol = { span: 16 }

    const memberIntial = () => ({
      officeId: undefined,
      id: undefined,
    })

    const member = reactive(memberIntial())

    // EVENTS
    watch(() => [props.visible, props.formState] , ([visible, formState]) => {
      isVisible.value = visible
      form.value = formState
    })

    // METHODS
    const okAction = () => {
      if (props.actionType === 'view') {
        emit('change-action', 'update')
      } else {
        validateFields()
      }
    }

    const validateFields = () => {
      props.validate()
        .then(() => {
          if (form.value.members.length < 1) {
            _message.error('The member\'s list should not be empty')
          } else {
            Object.assign(member, memberIntial())
            emit('submit-form')
          }
        })
        .catch(err => {
          console.log('error', err);
        });
    }

    const onClose = () => {
      emit('close-modal')
    }

    const getPersonnelList = async (data, field) => {
      if (field === 'oic') {
        oicList.value = []
        if(typeof form.value !== 'undefined') {
          form.value.chairId = undefined
        }
      } else {
        memberList.value = []
        member.id = undefined
      }
      if (typeof data !== 'undefined') {
        const id = data.value
        formLoading.value = true
        await getPersonnelByOffice(id, 1).then(response => {
          if (response) {
            const { personnel } = response
            formLoading.value = false
            if (field === 'oic') {
              oicList.value = personnel
            } else {
              memberList.value = personnel
            }
          }
        })
      }
    }

    const addMember = () => {
      let isChair = false
      const details = { ...member }
      const ifExists = form.value.members.some(function(field) {
        return field.id.value === details.id.value
      })
      if (form.value.chairId !== null && typeof form.value.chairId !== 'undefined') {
        isChair = form.value.chairId.value === details.id.value
      }
      if (!ifExists && !isChair) {
        details.status = 'new'
        form.value.members.push(details)
        member.id = undefined
      } else if (isChair && !ifExists) {
        _message.error('This name is already selected as Officer-in-Charge')
      } else {
        _message.error('Name was already on the list. Please choose a different name', 3)
      }
    }

    const deleteMember = index => {
      const data = form.value.members[index]
      form.value.members.splice(index, 1)
      if (props.actionType === 'update' && (typeof (data.status) === 'undefined' || data.status !== 'new')) {
        form.value.deleted.push(data.dataId)
      }
    }

    const updateFormDetail = data => {
      form.value[data.field] = data.values
    }

    return {
      years,

      memberList,
      oicList,
      groupRef,
      isVisible,
      form,
      labelCol,
      wrapperCol,
      member,
      formLoading,

      okAction,
      onClose,
      getPersonnelList,
      addMember,
      deleteMember,
      updateFormDetail,
    }
  },
})
</script>
<style scoped>
.required-indicator::before {
  display: inline-block;
  margin-right: 4px;
  color: #f5222e;
  font-size: 14px;
  font-family: SimSun, sans-serif;
  line-height: 1;
  content: '*';
}
</style>
