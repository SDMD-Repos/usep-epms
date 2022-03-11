<template>
  <a-modal v-model:visible="isVisible"
           :title="modalTitle"
           :closable="false"
           :mask-closable="false"
           :ok-text="okText"
           :ok-button-props="{disabled:!allAccess && !isEdit }"
           @ok="okAction"
           @cancel="onClose">
    <a-form :label-col="labelCol"
            :wrapper-col="wrapperCol">
      <a-form-item label="Name" v-bind="validateInfos.name">
        <a-input v-model:value="form.name" :disabled="actionType === 'view'" />
      </a-form-item>
      <label>Scales</label>
      <a-input-group size="default">
        <a-row>
          <a-col :span="5" :offset="1" >
            <a-input-number v-model:value="scales.rate" placeholder="Rate" :min="1" :disabled="actionType === 'view'"/>
          </a-col>
          <a-col :span="12" :offset="1">
            <a-input v-model:value="scales.description" placeholder="Description" :disabled="actionType === 'view'"/>
          </a-col>
          <a-col :span="2" :offset="1">
            <a-button type="primary" @click="addMeasureItem" :disabled="scales.rate === null || scales.description === ''">
              <template #icon><PlusOutlined /></template>
            </a-button>
          </a-col>
        </a-row>
      </a-input-group>
      <div class="mt-3">
        <a-list item-layout="horizontal" :data-source="form.items">
          <template #renderItem="{ item, index }">
            <a-list-item>
              <template #actions v-if="actionType !== 'view'">
                <a @click="deleteItem(index)" >
                  delete
                </a>
              </template>
              {{ item.rate }} - {{ item.description }}
            </a-list-item>
          </template>
        </a-list>
      </div>
    </a-form>
  </a-modal>
</template>
<script>
import { defineComponent, reactive, ref, watch } from 'vue'
import { PlusOutlined } from '@ant-design/icons-vue'
import { message } from "ant-design-vue"

export default defineComponent({
  name: 'MeasuresFormModal',
  components: {
    PlusOutlined,
  },
  props: {
    visible: Boolean,
    actionType: {
      type: String,
      default: '',
    },
    modalTitle: {
      type: String,
      default: '',
    },
    okText: {
      type: String,
      default: '',
    },
    formState: {
      type: Object,
      default: () => {
        return {
          id: null,
          name: '',
          items: [],
          deleted: [],
        }
      },
    },
    validate: {
      type: Function,
      default: () => {},
    },
    validateInfos: {
      type: Object,
      default: () => { return {} },
    },
    isCreate: Boolean,
    isDelete: Boolean,
    isEdit: Boolean,
    allAccess: Boolean,
  },
  emits: ['close-modal', 'change-action', 'submit-form'],
  setup(props, { emit }) {
    // DATA
    let isVisible = ref()
    let form = ref()

    const labelCol = { span: 3 }
    const wrapperCol = { span: 16 }

    const scalesIntial = () => ({
      rate: null,
      description: '',
    })

    const scales = reactive(scalesIntial())

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
          if (form.value.items.length < 1) {
            message.error('Please add at least three (3) scales')
          } else {
            Object.assign(scales, scalesIntial())
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

    const addMeasureItem = () => {
      form.value.items.push({
        status: 'new',
        rate: scales.rate,
        description: scales.description,
      })
      Object.assign(scales, scalesIntial())
    }

    const deleteItem = index => {
      const item = form.value.items[index]
      form.value.items.splice(index, 1)
      if (props.actionType === 'update' && (typeof (item.status) === 'undefined' || item.status !== 'new')) {
        form.value.deleted.push(item.id)
      }
    }

    return {
      isVisible,
      form,

      labelCol,
      wrapperCol,
      scales,

      okAction,
      onClose,
      addMeasureItem,
      deleteItem,
    }
  },
})
</script>
