import { computed, ref} from 'vue'
import { useStore } from 'vuex'


export const usePermissionAccessRights = () => {

  const accessLists = computed(() => store.getters['user/access'])
  const store = useStore()
  const accessHeadPermission = accessLists.value.filter((value)=>{
      return  ( value.permission_id == "adminPermission" || 
                value.permission_id == "ap-form" );
    });

  const arPermission = accessLists.value.filter((value)=>{
    return value.permission_id == "ap-form";
    });

  const assignHeadPermission = accessLists.value.filter((value)=>{
    return  value.permission_id == "apf-aapcr";
  });
  
  const notInclude = accessLists.value.filter((value)=>{
    return (value.permission_id == "m-form" || 
            value.permission_id == "ap-manager" || 
            value.permission_id == "apf-opcrvr" || 
            value.permission_id == "apf-opcr" || 
            value.permission_id == "apf-cpcr"
            );
    })

  const isCreate = ref(false)
  const allAccess = ref(false)

  if(accessHeadPermission.length > 0 || arPermission.length > 0){
    allAccess.value = true;
    isCreate.value = true;
  
    if((accessHeadPermission.length > 0 && arPermission.length > 0) && notInclude.length > 0){
      allAccess.value = false;
      isCreate.value = false;
    }
    if(accessHeadPermission.length > 0 && notInclude.length > 0){
      allAccess.value = false;
      isCreate.value = false;
    }
    if(arPermission.length > 0 ){
      allAccess.value = true;
      isCreate.value = true;
    }
  }

  if(assignHeadPermission.length > 0){
    isCreate.value = true;
    allAccess.value = true; 
  }

  return {
    isCreate,
    allAccess,
  }
}
