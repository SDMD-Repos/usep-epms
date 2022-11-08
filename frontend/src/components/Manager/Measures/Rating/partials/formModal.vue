<template>
  <a-modal v-model:visible="isVisible" :title="modalTitle" :ok-text="okText"
           :closable="false" :mask-closable="false"
           :ok-button-props="{ disabled: !isEdit && !isCreate }"
           width="35%"
           @ok="okAction" @cancel="onClose">
    <a-form :label-col="labelCol"
            :wrapper-col="wrapperCol">
      <a-form-item label="Numerical Rating" v-bind="validateInfos.numericalRating">
        <span v-if="actionType === 'view'"><b>{{ form.numericalRating }}</b></span>
        <a-input-number v-else v-model:value="form.numericalRating" :min="0" :max="10" />
      </a-form-item>

      <a-form-item>
        <template #label><span class="required-indicator">Average Point Score</span></template>
        <div class="row" v-if="actionType !== 'view'">
          <div class="col-sm-6 col-md-3">
            <a-form-item v-bind="validateInfos['averagePointScore.from']">
              <a-input-number v-model:value="form.averagePointScore.from" :min="0" :max="10" :step="0.01" string-mode />
            </a-form-item>
          </div>
          -
          <div class="col-sm-6 col-md-3">
            <a-form-item v-bind="validateInfos['averagePointScore.to']">
              <a-input-number v-model:value="form.averagePointScore.to" :min="0" :max="10" :step="0.01" string-mode />
            </a-form-item>
          </div>
        </div>
        <span v-else>
          <b>{{ form.averagePointScore.from }} - {{ form.averagePointScore.to }}</b>
        </span>
      </a-form-item>

      <a-form-item label="Adjectival Rating" v-bind="validateInfos.adjectivalRating">
        <span v-if="actionType === 'view'"><b>{{ form.adjectivalRating }}</b></span>
        <a-input v-else v-model:value="form.adjectivalRating" :disabled="actionType === 'view'" />
      </a-form-item>

      <a-form-item label="Description" v-bind="validateInfos.description">
        <span v-if="actionType === 'view'">
          <p style="font-weight: bold; padding-top: 5px;">{{ form.description }}</p>
        </span>
        <a-textarea v-else v-model:value="form.description" :disabled="actionType === 'view'" :rows="4" />
      </a-form-item>
    </a-form>
  </a-modal>
</template>

<script>
import { defineComponent, reactive, ref, watch, inject } from 'vue'
import { PlusOutlined } from '@ant-design/icons-vue'

export default defineComponent({
  name: 'RatingFormModal',
  components: {
    /*PlusOutlined,*/
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
          displayAsItems: false,
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

    const _message = inject('a-message')

    // DATA
    let isVisible = ref()
    let form = ref()

    const labelCol = { span: 6 }
    const wrapperCol = { span: 16 }

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
          emit('submit-form')
        })
        .catch(err => {
          console.log('error', err);
        });
    }

    const onClose = () => {
      emit('close-modal')
    }

    return {
      isVisible,
      form,

      labelCol,
      wrapperCol,

      okAction,
      onClose,
    }
  },
})
</script>
