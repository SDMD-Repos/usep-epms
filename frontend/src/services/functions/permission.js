import {computed, onMounted, ref, watch} from 'vue'
import { useStore } from 'vuex'

export const usePermission = (moduleArray) => {
  const store = useStore()
  const accessRights = computed(() => store.getters['system/permission'].accessRights)
  const userAccess = computed(() => store.getters['system/permission'].userAccess)
  const hasAccess = ref(false)

  watch(accessRights, accessRights => {
    checkPermission()
  })

  const checkPermission = () => {
    for (let i = 0; i < moduleArray.length; i++){
      var module = accessRights.value.find((permission) => permission.permission_id === moduleArray[i])
      var cPermission = fetchAllChildrenPermission(module.id)

      if (hasUserAccess(module.id) && !hasChildrenPermission(cPermission)){
        hasAccess.value = true
        return
      }
      else {
        var parent = getParentPermission(module.id)

        do {
          var pPermission = fetchAllChildrenPermission(parent)
          if (hasUserAccess(parent) && !hasChildrenPermission(pPermission)){
            hasAccess.value = true
            return
          }
        }while (parent = getParentPermission(parent))
      }
    }
  }

  const fetchAllChildrenPermission = (id) => {
    let permissions = []
    let allChildrenPermission = []
    let childPermission = getChildrenPermission(id)

    getChildrenPermission(id).forEach(function (value, key){
      allChildrenPermission = fetchAllChildrenPermission(value)
      if (allChildrenPermission.length)
        permissions.push(allChildrenPermission)
    })
    if (childPermission.length)
      permissions.concat(childPermission)
    return permissions
  }

  const getChildrenPermission = (id) => {
    let permissions = []
    accessRights.value.forEach(function (value, key){
      if (value.parent_id === id) {
        permissions.push(value.id)
      }
    })
    return permissions
  }

  const hasUserAccess = (id) => {

    if (userAccess.value && accessRights.value.length)
      for (let i = 0; i < userAccess.value.length; i++){
        if (id === userAccess.value[i].access_right_id) return true
      }
    return false
  }

  const hasChildrenPermission = (permissions) => {
    if (userAccess.value && userAccess.value.length){
      userAccess.value.forEach(function (value){
        if (permissions && permissions.length){
          permissions.forEach(function (permissionValue){
            if (value.access_right_id === permissionValue) return true
          })
        }
      })
    }
    return false
  }

  const getParentPermission = (id) => {
    if (accessRights.value && accessRights.value.length){
      for (let i = 0; i < accessRights.value.length; i++) {
        if (accessRights.value[i].id === id) return accessRights.value[i].parent_id
      }
    }
    return false
  }

  return {
    hasAccess,
  }
}
