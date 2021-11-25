<template>
  <a-modal v-model:visible="isVisible"
           :title="modalTitle"
           :closable="false"
           :mask-closable="false"
           :ok-text="okText"
           width="700px"
           @ok="onOk"
           @cancel="onCancel">
    <a-spin :spinning="formLoading">
      <a-form ref="signatoryRef" layout="vertical" :model="form">
        <template v-for="(data, index) in form.signatories" :key="index">
          <a-form-item>
            <a-checkbox v-model:checked="data.isCustom" @change="handleCustomChange(index)">
              Custom
            </a-checkbox>
          </a-form-item>

          <a-form-item :name="['signatories', index, 'officeId']"
                       :rules="!data.isCustom ? rules.officeId : rules.officeIdInput">
            <a-tree-select
              v-model:value="data.officeId"
              v-if="!data.isCustom"
              style="width: 100%"
              :dropdown-style="{ maxHeight: '400px', overflow: 'auto' }"
              :tree-data="officeList"
              placeholder="Select office"
              tree-node-filter-prop="title"
              show-search
              allow-clear
              label-in-value
              @change="getPersonnelList($event, index)"
            />
            <a-input v-else
                     v-model:value="data.officeId"
                     style="width: 100%"
                     placeholder="Office Name" />
          </a-form-item>
          <a-form-item :name="['signatories', index, 'personnelId']"
                       :rules="!data.isCustom ? rules.personnelId : rules.personnelIdInput">
            <a-tree-select
              v-if="!data.isCustom"
              v-model:value="data.personnelId"
              style="width: 100%"
              :dropdown-style="{ maxHeight: '400px', overflow: 'auto' }"
              :tree-data="memberList"
              placeholder="Select Personnel"
              tree-node-filter-prop="title"
              show-search
              allow-clear
              label-in-value
              @change="(value, label, extra) => { getPersonnelPosition(value, label, extra, index) }"
            />
            <a-input v-else
                     v-model:value="data.personnelId"
                     style="width: 100%"
                     placeholder="Personnel Name"/>
          </a-form-item>
          <a-form-item :name="['signatories', index, 'position']"
                       :rules="!data.isCustom ? rules.position : rules.positionInput">
            <a-select v-if="!data.isCustom"
                      ref="positionField"
                      v-model:value="data.position"
                      :options="positionList"
                      placeholder="Select Personnel's Position"
                      style="width: 100%"
                      show-search
                      allow-clear @change="positionChange"/>
            <a-input v-else
                     v-model:value="data.position"
                     style="width: 100%"
                     placeholder="Personnel's Position"/>
          </a-form-item>
        </template>
      </a-form>
    </a-spin>
  </a-modal>
</template>
<script>
import { defineComponent, ref, watch } from "vue"
import { useStore } from 'vuex'
import _ from "lodash"
import * as hris from '@/services/hris'

export default defineComponent({
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
    officeList: {
      type: Array,
      default: () => { return [] },
    },
    positionList: {
      type: Array,
      default: () => { return [] },
    },
    formState: {
      type: Object,
      default: () => { return {} },
    },
  },
  emits: ['close-modal'],
  setup(props, context) {
    const store = useStore()

    // DATA
    let isVisible = ref()
    let formLoading = ref(false)
    const form = ref()

    const positionField = ref()

    const memberList = ref([])
    const labelCol = { style: { width: '150px' } }
    const wrapperCol = { span: 16 }

    const signatoryRef = ref();
    const rules = {
      officeId: {
        required: true,
        message: 'This field is required',
      },
      personnelId: {
        required: true,
        message: 'This field is required',
      },
      position: {
        required: true,
        message: 'This field is required',
      },

      officeIdInput: {
        required: true,
        message: 'This field is required',
        trigger: 'blur',
      },
      personnelIdInput: {
        required: true,
        message: 'This field is required',
        trigger: 'blur',
      },
      positionInput: {
        required: true,
        message: 'This field is required',
        trigger: 'blur',
      },
    }

    // EVENTS
    watch(() => [props.visible] , ([visible]) => {
      isVisible.value = visible
    })

    watch(() =>  props.formState, formState => {
      form.value = formState
      console.log(formState)
    }, { deep: true })

    // METHODS
    const handleCustomChange = index => {
      form.value.signatories[index].officeId = undefined
      form.value.signatories[index].personnelId = undefined
      form.value.signatories[index].position = undefined
    }

    const getPersonnelList = (officeId, index) => {
      form.value.signatories[index].personnelId = undefined
      form.value.signatories[index].position = undefined
      memberList.value = []
      if (officeId && !form.value.isCustom) {
        formLoading.value = true
        const id = officeId.value
        const getPersonnelByOffice = hris.getPersonnelByOffice
        getPersonnelByOffice(id).then(response => {
          if (response) {
            const { personnel } = response
            memberList.value = personnel
          }
          formLoading.value = false
        })
      }
    }

    const getPersonnelPosition = (value, label, extra, index) => {
      form.value.signatories[index].position = undefined
      if (typeof extra.triggerNode !== 'undefined') {
        const { position } = extra.triggerNode.dataRef
        const office = form.value.signatories[index].officeId
        const officePosition = position.filter(i => {
          return i.DepartmentID === office.value
        })[0]
        if (typeof officePosition !== 'undefined') {
          positionField.value.focus()
          form.value.signatories[index].position = officePosition.PositionName
        }
      }
    }

    const positionChange = () => {
      console.log('position change', positionField)
    }

    const onOk = () => {
      signatoryRef.value.validate().then(() => {
        console.log('values', form.value.signatories);
      })
      .catch(error => {
        console.log('error', error);
      });
    }

    const onCancel = () => {
      context.emit('close-modal')
    }

    return {
      isVisible,
      form,
      positionField,
      formLoading,
      memberList,
      labelCol,
      wrapperCol,

      signatoryRef,
      rules,

      handleCustomChange,
      getPersonnelList,
      getPersonnelPosition,
      positionChange,
      onOk,
      onCancel,
    }
  },
})
</script>
