<template>
  <div>
    <a-spin :spinning="loading" tip="Fetching data in HRIS...">
      <div>
        <a-select v-model="year" placeholder="Select year" style="width: 200px" @change="fetchSignatories">
          <template v-for="(y, i) in years">
            <a-select-option :value="y" :key="i">
              {{ y }}
            </a-select-option>
          </template>
        </a-select>
      </div>
      <div class="mt-2" v-if="formId === 'vpopcr'">
        <a-select v-model="office" placeholder="Select office" style="width: 450px" @change="fetchSignatories">
          <template v-for="(y, i) in vpOfficesList">
            <a-select-option :value="y.id" :key="i">
              {{ y.title }}
            </a-select-option>
          </template>
        </a-select>
      </div>
      <div class="mt-4">
        <a-collapse v-model="activeKey" accordion>
          <a-collapse-panel v-for="(type, key) in signatoryTypes" :key="`${key}`" :header="type.name">
            <div class="spin-content">
              <a-form :form="form" @submit="handleSubmit($event, type.id)" v-if="displayInput && formActive === type.id">
                <template v-for="(data, i) in details">
                  <a-row :key="i" type="flex" justify="space-around" align="middle">
                    <a-col :xs="{ span: 24, offset: 1 }" :lg="{ span: 12 }">
                      <a-form-item :key="i">
                        <signatory-input
                          v-decorator="[`signatories[${i}]`,
                                {
                                  initialValue: {
                                    id: 'new',
                                    officeId: undefined,
                                    personnelId: undefined,
                                    position: undefined,
                                    list: [],
                                    isCustom: false,
                                  },
                                  rules: [{ validator: checkFields }]
                                },
                          ]"
                          :index="i"
                          :count="details.length"
                          :office-list="mainOfficesChildrenList"
                          :position-list="positionList"
                          :form-name="formName"
                          :form-active="formActive"
                          @add-signatory="addSignatory"
                          @delete-signatory="deleteSignatory" />
                      </a-form-item>
                      <a-divider type="horizontal" />
                    </a-col>
                  </a-row>
                </template>
                <a-form-item>
                  <a-row type="flex" justify="center" class="mt-5">
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
              <table-signatories :key="`${key}`" :year="year" :form-id="formId" :office-id="office"
                                 :list="filterBySignatory(type.id)" :loading="!loading && loadingList"
                                 v-else/>
            </div>
            <template v-if="formId !== 'vpopcr' || formId === 'vpopcr' && typeof office !== 'undefined'">
              <a-icon slot="extra"
                      :type="filterBySignatory(type.id).length ? 'edit' : 'user-add'"
                      @click="handleClick($event, key, type.id)" v-if="!displayInput"/>
            </template>
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
      signatoryTypes: state => state.formSettings.signatoryTypes,
      signatoryList: state => state.formSettings.signatories,
      mainOfficesChildrenList: state => state.external.mainOfficesChildren,
      vpOfficesList: state => state.external.vpOffices,
      positionList: state => state.external.positionList,
      loading: state => state.external.loading,
      loadingList: state => state.formSettings.loading,
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
      office: undefined,
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
    this.onLoad()
    this.fetchSignatories()
  },
  methods: {
    onLoad() {
      let params = {
        selectable: {
          allColleges: true,
          mains: true,
        },
        isAcronym: false,
      }
      params = encodeURIComponent(JSON.stringify(params))
      this.$store.dispatch('external/FETCH_MAIN_OFFICES_CHILDREN', { payload: params })
      this.$store.dispatch('external/FETCH_VP_OFFICES', { payload: { officesOnly: 1 } })
      this.$store.dispatch('formSettings/FETCH_ALL_SIGNATORY_TYPES')
      this.$store.dispatch('external/FETCH_ALL_POSITIONS')
    },
    fetchSignatories() {
      const { formId } = this
      const data = {
        year: this.year,
        formId: formId,
      }
      this.$store.commit('formSettings/SET_STATE', {
        signatories: [],
      })
      if (formId === 'vpopcr') {
        const { office } = this
        if (typeof office !== 'undefined') {
          data.officeId = office
          this.$store.dispatch('formSettings/FETCH_YEAR_SIGNATORIES', { payload: data })
        }
      } else {
        this.$store.dispatch('formSettings/FETCH_YEAR_SIGNATORIES', { payload: data })
      }
    },
    filterBySignatory(type) {
      return this.signatoryList.filter((i) => {
        return i.type_id === type
      })
    },
    changeActivekey(key) {
      this.activeKey = key
    },
    handleClick(event, index, typeId) {
      event.stopPropagation()
      const data = this.filterBySignatory(typeId)
      this.formActive = ''
      if (this.activeKey === index.toString()) {
        this.formActive = typeId
        this.changeFormDisplay()
        if (!data.length) {
          this.addSignatory()
          this.edit = false
        } else {
          this.edit = true
          const { form } = this
          this.details = data
          const newValues = []
          data.forEach((item, count) => {
            const values = {
              officeId: undefined,
              personnelId: undefined,
              position: undefined,
              list: [],
              isCustom: false,
            }
            let officeIdValue = item.office_id ? item.office_id : item.office_name
            if (item.office_id) {
              officeIdValue = {
                value: parseInt(item.office_id),
                label: item.office_name,
              }
            }
            let personnelIdValue = item.personnel_id ? item.personnel_id : item.personnel_name
            if (item.personnel_id) {
              personnelIdValue = {
                value: item.personnel_id,
                label: item.personnel_name,
              }
            }
            newValues.push({
              id: item.id,
              officeId: officeIdValue,
              personnelId: personnelIdValue,
              position: item.position,
              list: [],
              isCustom: !item.office_id && !item.personnel_id,
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
          position: undefined,
          isCustom: false,
        }
        this.details.push(data)
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
      form.setFieldsValue({
        signatories: signatories.filter((i, k) => k !== index),
      })
      this.details.splice(index, 1)
    },
    checkFields(rule, value, callback) {
      if ((typeof value.officeId === 'undefined' || value.officeId === '') && !value.isCustom) {
        callback(new Error('Please select an office'))
      } else if (typeof value.personnelId === 'undefined' || value.personnelId === '') {
        if (value.isCustom) {
          callback(new Error('Please input the personnel\'s name'))
        } else {
          callback(new Error('Please select a personnel'))
        }
      } else if (typeof value.position === 'undefined' || value.position === '') {
        if (value.isCustom) {
          callback(new Error('Please input the personnel\'s position'))
        } else {
          callback(new Error('Please select the personnel\'s position'))
        }
      } else {
        callback()
      }
    },
    handleSubmit(e, typeId) {
      e.preventDefault()
      const self = this
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
                    position: item.position,
                    isCustom: item.isCustom,
                  }
                  newValues.push(addNew)
                })
              })
              const data = {
                typeId: typeId,
                year: self.year,
                formId: self.formId,
                signatories: newValues,
              }
              if (self.formId === 'vpopcr') {
                data.officeId = self.office
              }
              if (!self.edit) {
                self.$store.dispatch('formSettings/SAVE_POSITION_SIGNATORIES', { payload: data })
              } else {
                self.$store.dispatch('formSettings/UPDATE_POSITION_SIGNATORIES', { payload: data })
              }
              self.form.resetFields()
              self.changeFormDisplay()
            },
            onCancel() {},
          })
        }
      })
    },
    changeFormDisplay() {
      this.details = []
      this.displayInput = !this.displayInput
      this.edit = !this.edit
    },
  },
}
</script>
