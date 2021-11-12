<template>
  <a-modal v-model:visible="isVisible"
           :title="modalTitle"
           :closable="false"
           :mask-closable="false"
           :ok-text="okText"
           width="45%"
           @ok="okAction"
           @cancel="onClose">
    <a-spin :spinning="formLoading">
      <a-form :label-col="labelCol"
              :wrapper-col="wrapperCol">
        <a-form-item label="Name" v-bind="validateInfos.name">
          <a-input v-model:value="form.name" />
        </a-form-item>

        <a-form-item label="Has OIC?">
          <a-switch v-model:checked="form.hasChair" />
        </a-form-item>

        <template v-if="form.hasChair">
          <a-form-item v-bind="validateInfos.chairId">
            <template #label>
              <span class="required-indicator">Officer-in-Charge</span>
            </template>
            <!-- Custom component for Officer-In-Charge field -->
            <a-tree-select
              v-model:value="form.chairOffice"
              style="width: 100%"
              :dropdown-style="{ maxHeight: '400px', overflow: 'auto' }"
              :tree-data="officeList"
              placeholder="Select office"
              tree-node-filter-prop="title"
              show-search
              allow-clear
              label-in-value
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
                allow-clear
                label-in-value
                @change="validate('chairId', { trigger: 'blur' }).catch(() => {})"
              />
            </div>

<!--            <office-personnel-fields-->
<!--              v-model:value="form.chairId"-->
<!--              :chair-office="form.chairOffice"-->
<!--              :departments="officeList"-->
<!--              :personnel="oicList"-->
<!--              :members="form.members"-->
<!--              :action="actionType"-->
<!--              @get-personnel-list="getPersonnelList($event, 'oic')"-->
<!--              @update-chair-id="updateFormDetail"-->
<!--            />-->
          </a-form-item>
        </template>

        <a-form-item label="Effective until" v-bind="validateInfos.effectivity">
          <a-select v-model:value="form.effectivity" placeholder="Select year" style="width: 200px" >
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
                    label-in-value>
            <a-select-option v-for="list in supervisingList"
                             :value="list.value"
                             :key="list.value">
              {{ list.title }}
            </a-select-option>
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
            @change="getPersonnelList($event, 'member')"
          />
          <a-input-group compact class="mt-2">
            <a-tree-select
              v-model:value="member.id"
              style="width: 90%"
              :dropdown-style="{ maxHeight: '400px', overflow: 'auto' }"
              :tree-data="memberList"
              placeholder="Select Personnel"
              tree-node-filter-prop="title"
              show-search
              allow-clear
              label-in-value
            />
            <a-button type="primary" class="mr-3" :disabled="!member.id" @click="addMember">
              <template #icon><UserAddOutlined /></template>
            </a-button>
          </a-input-group>
          <div class="mt-3">
            <a-list item-layout="horizontal" :data-source="form.members">
              <template #renderItem="{ item, index }">
                <a-list-item>
                  <template #actions>
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
import { defineComponent, computed, toRef, ref, reactive } from 'vue'
import { UserAddOutlined } from '@ant-design/icons-vue'
import { Form, message } from 'ant-design-vue'
import * as hris from '@/services/hris'
// import officePersonnelFields from './officePersonnelFields'
const useForm = Form.useForm
export default defineComponent({
  name: 'FormModal',
  components: {
    UserAddOutlined,
    // officePersonnelFields,
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
  },
  emits: ['change-action', 'close-modal'],
  setup(props, { emit }) {
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
    let memberList = ref([])
    let oicList = ref([])
    const isVisible = toRef(props, 'visible')
    const form = toRef(props, 'formState')
    let checkIfEmpty = async (rule, value) => {
      console.log(value)
      if (value === '' || typeof value === 'undefined' || value.length === 0) {
        return Promise.reject('This field is required')
      } else {
        return Promise.resolve()
      }
    }

    const rules = reactive({
      name: [
        { required: true, message: 'This field is required', trigger: 'change' },
        { whitespace: true, message: 'Please input a valid Group name', trigger: 'change' },
        { min: 3, message: 'Length should be at least 3 characters', trigger: 'change' },
      ],
      effectivity: [
        { required: true, message: 'Please pick a date' },
      ],
      supervising: [
        { required: true, message: 'Please choose one' },
      ],
      chairId: [
        { validator: checkIfEmpty, trigger: 'change' },
      ],
    })

    const { validate, validateInfos } = useForm(form, rules);

    const labelCol = { span: 6 }
    const wrapperCol = { span: 16 }

    const member = reactive({
      officeId: undefined,
      id: undefined,
    })

    let formLoading = ref(false)

    // METHODS
    const okAction = () => {
      emit('change-action', 'update')
    }

    const onClose = () => {
      emit('close-modal')
    }

    const getPersonnelList = (data, field) => {
      const id = data.value
      formLoading.value = true
      const getPersonnelByOffice = hris.getPersonnelByOffice
      getPersonnelByOffice(id).then(response => {
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

    const addMember = () => {
      let isChair = false
      const details = { ...member }
      console.log(details)
      const ifExists = form.value.members.some(function(field) {
        return field.id.value === details.id.value
      })
      if (form.value.chairId !== null && typeof form.value.chairId !== 'undefined') {
        isChair = form.value.chairId.key === details.id.value
      }
      if (!ifExists && !isChair) {
        details.status = 'new'
        form.value.members.push(details)
        member.id = undefined
      } else if (isChair && !ifExists) {
        message.error('This name is already selected as Officer-in-Charge')
      } else {
        message.error('Name was already on the list. Please choose a different name', 3)
      }
    }

    const deleteMember = index => {
      // const details = form.value.members[index]
      form.value.members.splice(index, 1)
      // if (this.actionType === 'update' && (typeof (member.status) === 'undefined' || member.status !== 'new')) {
      //   this.form.deleted.push(member.dataId)
      // }
    }

    const updateFormDetail = data => {
      form.value[data.field] = data.values
    }

    const hithere = () => {
      console.log('sss')
    }

    return {
      years,

      memberList,
      oicList,
      groupRef,
      isVisible,
      form,
      rules,
      labelCol,
      wrapperCol,
      member,
      formLoading,

      validate,
      validateInfos,

      okAction,
      onClose,
      getPersonnelList,
      addMember,
      deleteMember,
      updateFormDetail,
      hithere,
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
