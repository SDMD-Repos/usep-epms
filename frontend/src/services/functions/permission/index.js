import { computed, ref } from 'vue'
import { useStore } from 'vuex'

export const usePermission = permission => {
  const { listCreate, listDelete, listEdit, listAapcr, listOpcrvp, listOpcr } = permission

  const accessLists = computed(() => store.getters['user/access'])
  const store = useStore()

  const createPermission = accessLists.value.filter(value => {
    return listCreate ? listCreate.includes(value.permission_id) : 0
  })

  const deletePermission = accessLists.value.filter(value => {
    return listDelete ? listDelete.includes(value.permission_id) : 0
  })

  const editPermission = accessLists.value.filter(value => {
    return listEdit ? listEdit.includes(value.permission_id) : 0
  })

  const aapcrPermission = accessLists.value.filter(value => {
    return listAapcr ? listAapcr.includes(value.permission_id) : 0
  })

  const opcrvpPermission = accessLists.value.filter(value => {
    return listOpcrvp ? listOpcrvp.includes(value.permission_id) : 0
  })

  const opcrPermission = accessLists.value.filter(value => {
    return listOpcr ? listOpcr.includes(value.permission_id) : 0
  })

  const isCreate = ref(false)
  const isDelete = ref(false)
  const isEdit = ref(false)
  const aapcrFormPermission = ref(false)
  const opcrvpFormPermission = ref(false)
  const opcrFormPermission = ref(false)
  const aapcrTab = ref('')
  const opcrvpTab = ref('')
  const currentForms = ref([])

  if (createPermission.length > 0) {
    isCreate.value = true
  }

  if (deletePermission.length > 0) {
    isDelete.value = true
  }

  if (editPermission.length > 0) {
    isEdit.value = true
  }

  if (aapcrPermission.length > 0) {
    currentForms.value.push('aapcr')
    aapcrFormPermission.value = true
  }

  if (opcrvpPermission.length > 0) {
    currentForms.value.push('vpopcr')
    opcrvpFormPermission.value = true
  }

  if (opcrPermission.length > 0) {
    opcrFormPermission.value = true
  }

  const allForms = currentForms.value
  return {
    isCreate,
    isDelete,
    isEdit,
    aapcrFormPermission,
    opcrvpFormPermission,
    opcrFormPermission,
    aapcrTab,
    opcrvpTab,
    allForms,
  }
}
