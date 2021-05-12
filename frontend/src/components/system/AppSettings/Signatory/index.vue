<template>
  <div>
    <a-spin :spinning="loading" tip="Fetching data in HRIS...">
      <a-select v-model="year" placeholder="Select year" style="width: 200px" @change="fetchSignatories">
        <template v-for="(y, i) in years">
          <a-select-option :value="y" :key="i">
            {{ y }}
          </a-select-option>
        </template>
      </a-select>
      <div class="mt-4">
        <a-collapse v-model="activeKey" accordion>
          <a-collapse-panel v-for="(position, key) in positionList" :key="`${key}`" :header="position.name">
            <div class="spin-content">
              <a-form :form="form" @submit="handleSubmit($event, position.id)" v-if="displayInput && formActive === position.id">
                <template v-for="(data, i) in details">
                  <a-row :key="i">
                    <a-col :xs="{ span: 2, offset: 1 }" :lg="{ span: 2, offset: 1 }" style="padding-top: 10px">
                      # {{ i + 1 }}
                    </a-col>
                    <a-col :xs="{ span: 20, offset: 1 }" :lg="{ span: 20 }">
                      <a-form-item :key="i">
                        <signatory-input
                          v-decorator="[`signatories[${i}]`,
                                {
                                  initialValue: { id: 'new', officeId: undefined, personnelId: undefined, list: [] },
                                  rules: [{ validator: checkFields }]
                                },
                          ]"
                          :index="i"
                          :count="details.length"
                          :office-list="mainOfficesList"
                          @add-signatory="addSignatory"
                          @delete-signatory="deleteSignatory"/>
                      </a-form-item>
                    </a-col>
                  </a-row>
                </template>
                <a-form-item>
                  <a-row type="flex" justify="center" style="padding-top: 10px">
                    <a-col :xs="{ span: 4 }" :lg="{ span: 1 }">
                      <a-button @click="changeFormDisplay">
                        Cancel
                      </a-button>
                    </a-col>
                    <a-col :xs="{ span: 4, offset: 1 }" :lg="{ span: 3 }">
                      <a-button type="primary" html-type="submit">
                        Save Changes
                      </a-button>
                    </a-col>
                  </a-row>
                </a-form-item>
              </a-form>
              <table-signatories :key="`${key}`" :year="year" :form-id="formId"
                                 :list="filterBySignatory(position.id)"
                                 v-else/>
            </div>
            <a-icon slot="extra"
                    :type="filterBySignatory(position.id).length ? 'edit' : 'user-add'"
                    @click="handleClick($event, key, position.id)" v-if="!displayInput"/>
          </a-collapse-panel>
        </a-collapse>
      </div>
    </a-spin>
  </div>
</template>

<script>
import { mapState } from 'vuex'
import SignatoryInput from './partials/inputs'
import TableSignatories from './partials/list'
import { Modal } from 'ant-design-vue'

export default {
  name: 'AapcrSignatoryForm',
  props: ['formName'],
  components: {
    SignatoryInput,
    TableSignatories,
  },
  computed: {
    ...mapState({
      positionList: state => state.formSettings.positions,
      signatoryList: state => state.formSettings.signatories,
      mainOfficesList: state => state.external.mainOffices,
      loading: state => state.external.loading,
    }),
    years() {
      const now = new Date().getFullYear()
      const min = 10
      const lists = []
      for (let i = now; i >= (now - min); i--) {
        lists.push(i)
      }
      return lists
    },
  },
  data() {
    const formName = this.formName
    return {
      year: new Date().getFullYear(),
      formId: formName,
      activeKey: '0',
      details: [],
      displayInput: 0,
      formActive: '',
      edit: false,
    }
  },
  watch: {
    formName(val = '') {
      this.formId = val
    },
  },
  beforeCreate() {
    this.form = this.$form.createForm(this, { name: 'signatory_form' })
  },
  mounted() {
    this.$store.dispatch('external/FETCH_MAIN_OFFICES')
    this.$store.dispatch('formSettings/FETCH_ALL_POSITIONS')
    this.fetchSignatories()
  },
  methods: {
    fetchSignatories() {
      const data = {
        year: this.year,
        formId: this.formId,
      }
      this.$store.dispatch('formSettings/FETCH_YEAR_SIGNATORIES', { payload: data })
    },
    filterBySignatory(position) {
      return this.signatoryList.filter((i) => {
        return i.position_id === position
      })
    },
    changeActivekey(key) {
      this.activeKey = key
    },
    handleClick(event, index, positionId) {
      event.stopPropagation()
      const data = this.filterBySignatory(positionId)
      this.formActive = ''
      if (this.activeKey === index.toString()) {
        this.formActive = positionId
        this.changeFormDisplay()
        if (!data.length) {
          this.addSignatory()
        } else {
          this.edit = true
          const { form } = this
          this.details = data
          const newValues = []
          data.forEach((item, count) => {
            const values = {
              officeId: undefined,
              personnelId: undefined,
              list: [],
            }
            newValues.push({
              id: item.id,
              officeId: item.officeId,
              personnelId: item.personnelId,
              list: [],
            })
            form.getFieldDecorator(`signatories[${count}]`, { initialValue: values })
          })
          form.setFieldsValue({
            signatories: newValues,
          })
        }
      }
    },
    addSignatory() {
      if (this.details.length < 3) {
        const data = {
          officeId: undefined,
          personnelId: undefined,
        }
        this.details.push(data)

        console.log(this.form)
      } else {
        Modal.error({
          title: 'Up to 3 signatories are only allowed to be added',
          content: '',
        })
      }
    },
    deleteSignatory(index) {
      const { form } = this
      const signatories = form.getFieldValue('signatories')
      console.log(signatories)
      form.setFieldsValue({
        signatories: signatories.filter((i, k) => k !== index),
      })
      this.details.splice(index, 1)
    },
    checkFields(rule, value, callback) {
      if (typeof value.officeId === 'undefined' || value.officeId === '') {
        callback(new Error('Please select an office'))
      } else if (typeof value.personnelId === 'undefined' || value.personnelId === '') {
        callback(new Error('Please select a personnel'))
      } else {
        callback()
      }
    },
    handleSubmit(e, positionId) {
      e.preventDefault()
      const that = this
      this.form.validateFields((err, values) => {
        if (!err) {
          Modal.confirm({
            title: 'Are you sure you want to save your changes?',
            content: '',
            onOk() {
              const newValues = []
              Object.keys(values).forEach(function (key) {
                values[key].forEach(item => {
                  const addNew = {
                    id: item.id,
                    officeId: item.officeId,
                    personnelId: item.personnelId,
                  }
                  newValues.push(addNew)
                })
              })
              const data = {
                positionId: positionId,
                year: that.year,
                formId: that.formId,
                signatories: newValues,
              }
              if (!that.edit) {
                that.$store.dispatch('formSettings/SAVE_POSITION_SIGNATORIES', { payload: data })
              } else {
                that.$store.dispatch('formSettings/UPDATE_POSITION_SIGNATORIES', { payload: data })
              }
              that.form.resetFields()
              that.changeFormDisplay()
            },
            onCancel() {},
          })
        }
      })
    },
    changeFormDisplay() {
      this.details = []
      this.displayInput = !this.displayInput
    },
  },
}
</script>
