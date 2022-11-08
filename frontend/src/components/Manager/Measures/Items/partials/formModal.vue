<template>
  <a-modal v-model:visible="isVisible" :title="modalTitle"
           :closable="false" :mask-closable="false"
           :ok-text="okText" :ok-button-props="{disabled: !isEdit && !isCreate}"
           width="800px" centered
           @ok="okAction" @cancel="onClose">

    <a-form ref="measuresFormRef" :model="form" layout="vertical">
      <a-form-item label="Name" name="name" :rules="rules.name">
        <a-input v-if="actionType !== 'view'" v-model:value="form.name" />
        <p class="readonly-fields" v-else><b>{{ form.name }}</b></p>
      </a-form-item>

      <a-form-item label="Display as items?" name="displayAsItems">
        <a-switch v-model:checked="form.displayAsItems" :disabled="actionType === 'view'" />
      </a-form-item>

      <a-form-item label="Is Custom?" name="isCustom">
        <a-switch v-model:checked="form.isCustom" :disabled="actionType === 'view'" @change="handleCustomChange"/>
      </a-form-item>

      <div v-if="!form.isCustom">
        <a-form-item label="Description" name="description"
                     :rules="[{ required: !form.isCustom, message: 'This field is required', trigger: 'blur' }]">
        <span v-if="actionType === 'view'">
          <p class="readonly-fields paragraph">{{ form.description }}</p>
        </span>
          <a-textarea v-else v-model:value="form.description" :disabled="actionType === 'view'" :rows="1" />
        </a-form-item>

        <a-form-item label="Variable Equivalent" name="variableEquivalent"
                     :rules="[{ required: !form.isCustom, message: 'This field is required', trigger: 'blur' }]">
          <a-input v-if="actionType !== 'view'" v-model:value="form.variableEquivalent" :style="{ 'width': '200px' }" />
          <p class="readonly-fields" v-else><b>{{ form.variableEquivalent }}</b></p>
        </a-form-item>

        <a-form-item label="Elements" name="elements"
                     :rules="[{ required: !form.isCustom, message: 'This field is required', trigger: 'blur' }]">
        <span v-if="actionType === 'view'">
          <p class="readonly-fields paragraph">{{ form.elements }}</p>
        </span>
          <a-textarea v-else v-model:value="form.elements" :disabled="actionType === 'view'" :rows="2" />
        </a-form-item>

        <a-form-item>
          <template #label><span class="required-indicator">Categories</span></template>
          <div v-if="actionType !== 'view'">
            <a-button type="primary" size="small" @click="addCategory" :style="{'width': '75px'}">New</a-button>
          </div>

          <a-list v-if="form.categories.length > 0" item-layout="horizontal" :data-source="form.categories">
            <template #renderItem="{ item, index }">
              <a-list-item>
                <template #actions v-if="actionType !== 'view'">
                  <a v-if="item.edited" @click="handleCategoryActions('save', index)" ><save-outlined class="action-icons"/></a>
                  <a v-else @click="handleCategoryActions('edit', index)" ><edit-outlined class="action-icons" /></a>

                  <a @click="handleCategoryActions('delete', index)" ><delete-outlined class="action-icons" /></a>
                </template>
                <span v-if="!item.edited" class="pl-3">
                  {{ item.numbering ? item.numbering + ". " + item.name : item.name }}
                </span>
                <div v-else>
                  <a-input-group compact>
                    <a-select v-model:value="item.numbering" :options="alphabet" placeholder="Select" />
                    <a-input id="category-input-name" v-model:value="item.name" placeholder="Category Name"/>
                  </a-input-group>
                </div>
              </a-list-item>
            </template>
            <template #header></template>
          </a-list>
        </a-form-item>
      </div>

      <a-form-item>
        <template #label><span class="required-indicator">Items</span></template>
        <div class="container">
          <div class="row" v-if="!form.isCustom">
            <a-select v-model:value="itemData.category" placeholder="Select Category" style="width: 350px" @change="handleCategoryChange">
              <a-select-option v-for="c in form.categories.filter(i => !i.edited)" :key="c.id" :value="c.id">
                {{ c.numbering ? c.numbering + ". " + c.name : c.name }}
              </a-select-option>
            </a-select>
          </div>

          <div class="row pt-4" v-if="(itemData.category || form.isCustom) && actionType !== 'view'">
            <a-input-group compact>
              <a-select v-model:value="itemData.rating" :options="ratingList" placeholder="Select Rating"
                        label-in-value :field-names="{ label: 'numerical_rating', value: 'id' }" style="width: 20%" />
              <a-textarea v-model:value="itemData.description" placeholder="Description"
                          :auto-size="{ minRows: 1, maxRows: 5 }" style="width: 400px"/>
              <a-button type="primary" :disabled="!itemData.rating || !itemData.description" @click="handleItemActions('save')">
                {{ !itemData.editMode ? 'Add' : 'Update' }}
              </a-button>
            </a-input-group>
          </div>
        </div>

        <a-divider />

        <a-table :pagination="false" :columns="categoryColumns" :data-source="categoryItems" size="small">
          <template #bodyCell="{ column, record, index }">
            <template v-if="column.key === 'rating'">
              <span class="with-new-line">{{ record.rating.label || record.rating.numerical_rating }}</span>
            </template>

            <template v-if="column.key === 'description'">
              <span class="with-new-line">{{ record.description }}</span>
            </template>

            <template v-if="column.key === 'operation' && !record.edited && actionType !== 'view'">
              <a @click="handleItemActions('edit', record.id)" ><edit-outlined class="action-icons" /></a>
              <a-divider type="vertical"/>
              <a @click="handleItemActions('delete', index)" ><delete-outlined class="action-icons" /></a>
            </template>
          </template>
        </a-table>
      </a-form-item>
    </a-form>
  </a-modal>
</template>
<script>
import { defineComponent, reactive, ref, watch, inject, createVNode, onMounted, computed } from 'vue'
import { useStore } from "vuex"
import { SaveOutlined, EditOutlined, DeleteOutlined, ExclamationCircleOutlined } from '@ant-design/icons-vue'
import { Modal } from "ant-design-vue";
import { useExtras } from '@/services/functions/extras'

export default defineComponent({
  name: 'ItemsFormModal',
  components: { SaveOutlined, EditOutlined, DeleteOutlined },
  props: {
    visible: Boolean,
    actionType: { type: String, default: '' },
    modalTitle: { type: String, default: '' },
    okText: { type: String, default: '' },
    isCreate: Boolean,
    isDelete: Boolean,
    isEdit: Boolean,
    allAccess: Boolean,
    formState: {
      type: Object,
      default: () => {
        return {
          id: null,
          name: '',
          displayAsItems: false,
          isCustom: false,
          description: '',
          variableEquivalent: '',
          elements: '',
          categories: [],
          customItems: [],
          deleted: [],
        }
      },
    },
  },
  emits: ['close-modal', 'change-action', 'submit-form'],
  setup(props, { emit }) {

    const _message = inject('a-message')

    const store = useStore()

    // DATA
    const measuresFormRef = ref()
    const isVisible = ref()
    const isCategoryOpen = ref(false)
    const form = ref()
    const submitError = ref('')

    const labelCol = { span: 5 }
    const wrapperCol = { span: 16 }

    const categoryCount = ref(0)

    const categoryInitial = () => ({ id: null, numbering: null, name: '', items: [], edited: false })

    const categoryData = reactive(categoryInitial())

    const itemInitial = () => ({
      id: null, category: null, rating: null, description: '', editMode: false,
    })

    const itemData = reactive(itemInitial())

    const storage = reactive({ item: {} })

    const categoryColumns = [
      { title: 'Rating', dataIndex: 'rating', key: 'rating', width: 20 },
      { title: 'Description', dataIndex: 'description', key: 'description', width: 190 },
      { title: 'Actions', dataIndex: 'operation', key: 'operation', width: 40 },
    ]

    const { getAllAlphabet, reduceKeys } = useExtras()

    const alphabet = ref(getAllAlphabet())

    const rules = {
      name: [
        { required: true, message: 'This field is required', trigger: 'blur' },
        { whitespace: true, message: 'Please input a valid Group name', trigger: 'change' },
        { min: 3, message: 'Length should be at least 3 characters', trigger: 'change' },
      ],
    }

    // COMPUTED
    const ratingList = computed(() => store.getters['formManager/manager'].measureRatings)

    const categoryItems = computed(() => {
      if(form.value.isCustom) { return form.value.customItems }
      else {
        if(itemData.category !== "") {
          const category = form.value.categories.filter(e => e.id === itemData.category)

          if(category.length > 0  && typeof category[0].items !== 'undefined') {
            return category[0].items
          }else { return [] }
        }else { return [] }
      }
    })

    // EVENTS
    watch(() => [props.visible, props.formState] , ([visible, formState]) => {
      isVisible.value = visible
      form.value = formState
    })

    // METHODS
    const addCategory = () => {
      const newId = 'new_' + categoryCount.value++
      categoryData.id = newId
      form.value.categories.push({
        id: newId,
        status: 'new',
        edited: true,
        numbering: null,
        name: '',
      })
    }

    const handleCategoryActions = (action, index) => {
      const item = form.value.categories[index]

      switch (action) {
        case 'save':
          Object.assign(categoryData, reduceKeys(item, ["id", "numbering", "name", "edited", 'status', 'items']))

          const { id, numbering, name } = categoryData

          if (name === ''){
            _message.error('Please input a valid Category Name')
          }else {
            const duplicateCategory = form.value.categories.filter((e, i) => {
              return (e.numbering === numbering || e.name === name) && e.id !== id
            })

            if(duplicateCategory.length > 0) {
              _message.error('Unable to add item. Duplicate entry. Numbering and Category Name must be unique')
            }else {
              categoryData.edited = false
              Object.assign(item, categoryData)
              resetCategoryData()

              _message.success('Category was saved successfully')
            }
          }
          break
        case 'edit':
          item.edited = true

          if(typeof item.status === 'undefined' || item.status === '') { delete categoryData.status }

          break
        case 'delete':
          if (props.actionType === 'update' && (typeof (item.status) === 'undefined' || item.status !== 'new')) {
            form.value.deleted.categories.push(item.id)
          }

          form.value.categories.splice(index, 1)

          resetItemData()
          break
        case 'cancel':
          if(typeof item.status !== 'undefined' && item.status === 'new') {
            form.value.categories.splice(index, 1)
          }

          break
      }

    }

    const handleItemActions = (mode, index=null) => {
      switch (mode) {
        case 'save':
          const { id, rating, description, editMode, category } = itemData
          let allowAdd = true, hasDuplicate = null, itemList = []

          if(!form.value.isCustom) {
            const category = form.value.categories.filter(i => i.id === itemData.category)[0]

            if(typeof category.items === 'undefined') {
              category.items = []
            }

            itemList = category.items
          }
          else { itemList = form.value.customItems }

          if(itemList.length > 0) {
            hasDuplicate = itemList.filter(e => {
              let itemRating = typeof e.rating === 'object' ? e.rating.value || e.rating.id : e.rating
              return (itemRating === rating.value || e.description === description) && e.id !== id
            })

            if(hasDuplicate.length > 0) { allowAdd = false }
          }

          if(allowAdd === true) {
            if (editMode === false) {
              _message.success('Item added successfully')

              itemList.push({
                id: 'new_' + categoryCount.value++,
                status: 'new',
                edited: false,
                rating: rating,
                description: description,
              })
            } else {
              _message.success('Item updated successfully')

              const item = itemList.filter(e => e.id === id)[0]
              item.rating = rating
              item.description = description
              item.edited = false
            }

            resetItemExceptCategory()
          } else {
            _message.error('Unable to add item. Rating and Description must be unique.')
          }

          break
        case 'edit':
          let itemCategory = []

          if(!form.value.isCustom) {
            itemCategory = form.value.categories.filter(e => e.id === itemData.category)[0].items
          }else { itemCategory = form.value.customItems }

          const item = itemCategory.filter(e => e.id === index)[0]
          let itemRating = typeof item.rating === 'object' ? item.rating : { label: item.numerical_rating, value: item.rating }

          item.edited = true

          itemData.id = item.id
          itemData.rating = itemRating
          itemData.description = item.description
          itemData.editMode = true

          storage.item = {...itemData}
          break
        case 'delete':
          let findCategory = []

          if(!form.value.isCustom) {
            findCategory = form.value.categories.filter(e => e.id === itemData.category)[0].items
          }else { findCategory = form.value.customItems }

          if (props.actionType === 'update' && (typeof (findCategory[index].status) === 'undefined' || findCategory[index].status !== 'new')) {
            form.value.deleted.items.push(findCategory[index].id)
          }
          findCategory.splice(index, 1)

          _message.success('Item deleted successfully')

          break
      }
    }

    const handleCustomChange = async () => {
      form.value.description = ''
      form.value.variableEquivalent = ''
      form.value.elements = ''
      form.value.categories = []
      form.value.customItems = []

      if(props.actionType === 'update') {
        form.value.deleted.categories = []
        form.value.deleted.items = []
      }

      resetItemData()
      resetCategoryData()
    }

    const handleCategoryChange = (value, option) => {
      if(props.actionType !== 'view') {
        if(itemData.editMode === true) {
          const { category, id } = storage.item
          const data = form.value.categories.filter(e => e.id === category)
          console.log(data[0])
          const dataItems = data[0].items.filter(i => i.id === id)
          console.log(dataItems)
          dataItems[0].edited = false
        }
        resetItemExceptCategory()
        storage.item = {...itemData}
      }
    }

    const resetCategoryData = () => { Object.assign(categoryData, categoryInitial()) }

    const resetItemData = () => { Object.assign(itemData, itemInitial()) }

    const resetItemExceptCategory = () => {
      itemData.id = null
      itemData.rating = null
      itemData.description = ''
      itemData.editMode = false
    }

    const okAction = () => {
      if (props.actionType === 'view') {
        emit('change-action', 'update')
      } else {
        validateFields()
      }
    }

    const validateFields = () => {
      measuresFormRef.value.validate()
        .then( async () => {
          let hasEmpty = false

          submitError.value = ''

          if(!form.value.isCustom) {
            const savedCategories = form.value.categories.filter(e => !e.edited)

            if(savedCategories < 1) {
              _message.error("There were no Categories added to the list")
              hasEmpty = true
            }else {
              for await (const item of savedCategories) {
                if(typeof item.items === 'undefined' || item.items.length < 1) {
                  _message.error("There were no Items added to category (" + (item.numbering + ". " + item.name) + ")")
                  hasEmpty = true
                  break
                }
              }
            }
          }else {
            if(form.value.customItems.length < 1) {
              _message.error('Please add at least three (3) items')
              hasEmpty = true
            }
          }

          if(hasEmpty === false) {
            Modal.confirm({
              title: () => 'Are you sure you want to save this?',
              icon: () => createVNode(ExclamationCircleOutlined),
              content: () => '',
              okText: 'Yes',
              cancelText: 'No',
              onOk: async () => {
                await emit('submit-form', { ...form.value })
                await onClose()
              },
              onCancel() {},
            });


          }
        })
        .catch(err => {
          console.log('error', err);
        });
    }

    const resetModalData = async () => {
      submitError.value = ''
      categoryCount.value = 0
      storage.item = {}

      await resetItemData()
      await resetCategoryData()
    }

    const onClose = async () => {
      await resetModalData()
      await emit('close-modal')
    }

    return {
      measuresFormRef, isVisible, isCategoryOpen, form, submitError,

      labelCol, wrapperCol, itemData, categoryColumns, alphabet, categoryData, rules,

      ratingList, categoryItems, storage,

      okAction, onClose,
      addCategory, handleCategoryActions, handleCustomChange, handleCategoryChange, handleItemActions,
    }
  },
})
</script>
<style scoped>
#category-box {
  padding-left: 150px;
  padding-right: 50px;
}

#category-input-name {
  width: 400px;
}

.action-icons {
  font-size: 16px;
}

.with-new-line { white-space: pre-wrap; }

.readonly-fields {
  margin: 0 0 1.5em;
  padding-left: 10px;
  background-color: #f3f3f3;
}

.readonly-fields.paragraph {
  font-weight: bold;
  padding-top: 5px;
}
</style>
