import { computed, ref } from 'vue'
import { useStore } from 'vuex'

export const usePermission = permission => {
  const store = useStore()

  const { 
    listCreate, listDelete, listEdit, listAapcr, listOpcrvp, listOpcr, listCpcr, listIpcr, AccessRightsManager, request, adminPermission, adminRequests,
   } = permission
  const accessLists = computed(() => store.getters['user/access'])
  const formAccess = computed(() => store.getters['user/user'].formAccess)
  
  // console.log(accessLists.value);
  // console.log(formAccess);
  const admin = accessLists.value.filter(value => {
    return adminPermission ? adminPermission.includes(value.permission_id) : 0
  });

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

  const cpcrPermission = accessLists.value.filter(value => {
    return listCpcr ? listCpcr.includes(value.permission_id) : 0
  })

  const ipcrPermission = accessLists.value.filter(value => {
    return listIpcr ? listIpcr.includes(value.permission_id) : 0
  })

  const ManagerPermission = accessLists.value.filter(value => {
    return AccessRightsManager ? AccessRightsManager.includes(value.permission_id) : 0
  })

  const requestModulePermission = accessLists.value.filter(value => {
    return request ? request.includes(value.permission_id) : 0
  })

  const isCreate = ref(false)
  const isDelete = ref(false)
  const isEdit = ref(false)
  const aapcrFormPermission = ref(false)
  const opcrvpFormPermission = ref(false)
  const opcrFormPermission = ref(false)
  const cpcrFormPermission = ref(false)
  const ipcrFormPermission = ref(false)
  const ARManagerPermission = ref(false)
  const requestPermission = ref(false)
  const adminPermissionRef = ref(false)

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
    currentForms.value.push('opcr')
    opcrFormPermission.value = true
  }

  if (cpcrPermission.length > 0) {
    currentForms.value.push('cpcr')
    cpcrFormPermission.value = true
  }

  if (ipcrPermission.length > 0) {
    currentForms.value.push('ipcr')
    ipcrFormPermission.value = true
  }

  if (ManagerPermission.length > 0) {
    ARManagerPermission.value = true
  }

  if (requestModulePermission.length > 0) {
    requestPermission.value = true
  }

  if(admin.length > 0) {
    adminPermissionRef.value = true
  }

  const allForms = currentForms.value

  return {
    isCreate,
    isDelete,
    isEdit,
    aapcrFormPermission,
    opcrvpFormPermission,
    opcrFormPermission,
    cpcrFormPermission,
    ipcrFormPermission,
    aapcrTab,
    opcrvpTab,
    allForms,
    ARManagerPermission,
    requestPermission,
    formAccess,
    adminPermissionRef,
  }
}
