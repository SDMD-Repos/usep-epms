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
    moduleArray.forEach(function (value){
      var module = accessRights.value.find((permission) => permission.permission_id === value)
      var cPermission = fetchAllChildrenPermission(module.id)
      if (hasUserAccess(module.id) && !hasChildrenPermission(cPermission)) hasAccess.value = true
      else {
        var parent = getParentPermission(module.id)
        do {
          var pPermission = fetchAllChildrenPermission(parent)
          if (hasUserAccess(parent) && !hasChildrenPermission(pPermission)) hasAccess.value = true
        }while (parent = getParentPermission(parent))
      }
    })
    hasAccess.value = false
  }

  const fetchAllChildrenPermission = (id) => {
    let permissions = []
    getChildrenPermission(id).forEach(function (value, key){
      permissions = permissions.concat(fetchAllChildrenPermission(value))
    })
    return permissions
  }

  const getChildrenPermission = (id) => {
    let permissions = []
    accessRights.value.forEach(function (value, key){
      if (value.parent_id === id) permissions.concat(value.id)
    })
    return permissions
  }

  const hasUserAccess = (id) => {
    if (accessRights.value && accessRights.value.length)
      accessRights.value.forEach(function (value, key){
        if (id === value.access_right_id) return true
      })
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
    accessRights.value.forEach(function (value){
      if (value.id === id) return value.parent_id
    })
    return false
  }

  return {
    hasAccess,
  }
}
