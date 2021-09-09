<template>
  <div>
    <a-modal v-model="isOpen"
             :title="modalTitle"
             :closable="false"
             :mask-closable="false"
             :ok-text="okText"
             width="45%"
             @ok="okAction"
             @cancel="onClose">
      <a-form-model ref="groupForm"
                    :model="form"
                    :rules="rules"
                    :label-col="labelCol"
                    :wrapper-col="wrapperCol">

        <a-form-model-item label="Name" ref="name" prop="name">
          <a-input v-model="form.name" :disabled="actionType === 'view'"/>
        </a-form-model-item>

        <a-form-model-item label="Has OIC" ref="hasChair" prop="hasChair">
          <a-switch v-model="form.hasChair" :disabled="actionType === 'view'" />
        </a-form-model-item>

        <template v-if="form.hasChair">
          <a-form-model-item ref='chairId' prop="chairId">
            <span slot="label">
              <p class="required-indicator">*</p> Officer-in-Charge
            </span>
            <!-- Custom component for Officer-In-Charge field -->
            <office-personnel-fields
              v-model="form.chairId"
              :chair-office="form.chairOffice"
              :departments="officeList"
              :personnel="oicList"
              :loading="loadOicList"
              :filter="filterOption"
              :members="form.members"
              :action="actionType"
              @get-personnel-list="getPersonnelList($event, 'oic')"
              @update-chair-id="updateFormDetail"
            />
          </a-form-model-item>
        </template>

        <a-form-model-item label="Effective until" ref="effectivity" prop="effectivity">
          <a-date-picker v-model='form.effectivity'
                         :disabled-date="disabledDate"
                         value-format="YYYY-MM-DD"
                         :disabled="actionType === 'view'"/>
        </a-form-model-item>

        <a-form-model-item ref="members" prop="members">
          <span slot="label">
            <p class="required-indicator">*</p> Members
          </span>
          <a-tree-select
            v-model="member.officeId"
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
          <a-input-group compact>
            <a-tree-select
              v-model="member.id"
              style="width: 92%"
              :dropdown-style="{ maxHeight: '400px', overflow: 'auto' }"
              :tree-data="memberList"
              :disabled="loadMemberList || actionType === 'view'"
              placeholder="Select Personnel"
              tree-node-filter-prop="title"
              show-search
              allow-clear
              label-in-value
            />
            <a-button icon="user-add" :loading="loadMemberList" :disabled="!member.id" @click="addMember"/>
          </a-input-group>
          <div class="mt-3">
            <a-list item-layout="horizontal" :data-source="form.members">
              <a-list-item slot="renderItem" slot-scope="item, index">
                <a slot="actions" @click="deleteMember(index)" v-if="actionType !== 'view'">
                  delete
                </a>
                {{ item.id.label }}
              </a-list-item>
            </a-list>
          </div>
        </a-form-model-item>
      </a-form-model>
    </a-modal>
  </div>
</template>
<script>
import * as hris from '@/services/hris'
import officePersonnelFields from './officePersonnelFields'

export default {
  props: {
    open: Boolean,
    modalTitle: String,
    okText: String,
    actionType: String,
    officeList: Array,
    formObject: Object,
    dateFormatter: Function,
  },
  data() {
    const open = this.open
    const formObject = this.formObject
    const checkIfEmpty = (rule, value, callback) => {
      if (value === '' || typeof value === 'undefined' || value.length === 0) {
        callback(new Error('This field is required'))
      } else {
        this.$refs.groupForm.validateField(rule.field)
        callback()
      }
    }
    return {
      isOpen: open,
      labelCol: { span: 6 },
      wrapperCol: { span: 16 },
      form: formObject,
      rules: {
        name: [
          { required: true, message: 'This field is required', trigger: 'change' },
          { whitespace: true, message: 'Please input a valid Group name', trigger: 'change' },
          { min: 3, message: 'Length should be at least 3 characters', trigger: 'change' },
        ],
        effectivity: [{ required: true, message: 'Please pick a date', trigger: 'change' }],
        chairId: [{ validator: checkIfEmpty, trigger: 'change' }],
      },
      oicList: [],
      memberList: [],
      member: {
        officeId: undefined,
        id: undefined,
      },
      loadOicList: false,
      loadMemberList: false,
    }
  },
  watch: {
    open(val) {
      this.isOpen = val
    },
    formObject(val) {
      this.form = val
    },
  },
  methods: {
    disabledDate(current) {
      return current && current < this.dateFormatter().subtract(1, 'days').endOf('day')
    },
    filterOption(input, option) {
      return (
        option.componentOptions.children[0].text.toLowerCase().indexOf(input.toLowerCase()) >= 0
      )
    },
    onClose() {
      this.member = {
        officeId: undefined,
        id: undefined,
      }
      this.$emit('close-modal')
    },
    getPersonnelList(data, field) {
      if (field === 'oic') {
        this.form.chairOffice = data
        this.oicList = []
        this.form.chairId = undefined
        if (typeof data !== 'undefined') {
          this.loadOicList = true
        }
      } else {
        this.memberList = []
        this.member.id = undefined
        if (typeof data !== 'undefined') {
          this.loadMemberList = true
        }
      }
      if (typeof data !== 'undefined') {
        const id = data.value
        const getPersonnelByOffice = hris.getPersonnelByOffice
        getPersonnelByOffice(id).then(response => {
          if (response) {
            const { personnel } = response
            if (field === 'oic') {
              this.oicList = personnel
              this.loadOicList = false
            } else {
              this.memberList = personnel
              this.loadMemberList = false
            }
          }
        })
      }
    },
    updateFormDetail(data) {
      this.form[data.field] = data.values
    },
    addMember() {
      const member = { ...this.member }
      const { form } = this
      let isChair = false
      const ifExists = form.members.some(function(field) {
        return field.id.value === member.id.value
      })
      if (form.chairId !== null && typeof form.chairId !== 'undefined') {
        isChair = form.chairId.key === member.id.value
      }
      if (!ifExists && !isChair) {
        member.status = 'new'
        this.form.members.push(member)
        this.member.id = undefined
      } else if (isChair && !ifExists) {
        this.$message.error('This name is already selected as Officer-in-Charge')
      } else {
        this.$message.error('Name was already on the list. Please choose a different name', 3)
      }
    },
    deleteMember(index) {
      const member = this.form.members[index]
      this.form.members.splice(index, 1)
      // this.itemsText.splice(index, 1)
      if (this.actionType === 'update' && (typeof (member.status) === 'undefined' || member.status !== 'new')) {
        this.form.deleted.push(member.dataId)
      }
    },
    okAction() {
      if (this.actionType === 'view') {
        this.$emit('change-action', 'update')
      } else {
        this.validateFields()
      }
    },
    validateFields() {
      const self = this
      const { form } = this
      this.$refs.groupForm.validate(valid => {
        if (valid) {
          if (form.members.length < 1) {
            self.$message.error('The member\'s list should not be empty')
          } else {
            self.member = {
              officeId: undefined,
              id: undefined,
            }
            self.$emit('submit-form')
          }
        }
      })
    },
  },
  components: {
    officePersonnelFields,
  },
}
</script>
<style scoped>
.required-indicator {
  display: inline-block;
  margin-right: 4px;
  color: #f00;
  font-size: 15px;
  font-family: SimSun, sans-serif;
  line-height: 1;
}
</style>
