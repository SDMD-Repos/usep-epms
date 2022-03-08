import { computed } from 'vue'
import { useStore } from 'vuex'


export const usePermission = () => {

  const accessLists = computed(() => store.getters['user/access'])
  const store = useStore()
    const formPermission = accessLists.value.filter((value)=>{
      return  (value.permission_id == "manager" || 
              value.permission_id == "m-form" || 
              value.permission_id == "mf-functions");
    });

    const formCreatePermission = accessLists.value.filter((value)=>{
      return  value.permission_id == "mff-create";
    });

    const formDeletePermission = accessLists.value.filter((value)=>{
      return value.permission_id == "mff-delete";
    });

    const notInclude = accessLists.value.filter((value)=>{
      return  (value.permission_id == "m-group" || 
              value.permission_id == "mm-create" || 
              value.permission_id == "mm-delete");
    })

    const funcPermission = accessLists.value.filter((value)=>{
      return  value.permission_id == "m-form" || 
              value.permission_id == "mf-functions";
    });
    

    let isCreate = true;
    let isDelete = true;
    let allAccess = true;
    let returns = "";

    if (formPermission.length > 0 || notInclude.length > 0){
    allAccess = true;
    
    if (notInclude.length > 0 && funcPermission.length > 0 ){
      allAccess = true;
      isCreate = false;
      isDelete = false;
      
    }

    if(notInclude.length > 0 && formPermission.length > 0)
    allAccess = true;
    isCreate = false;
    isDelete = false;
    }
   
    if (formDeletePermission.length > 0 ) {
      isCreate = false;
      isDelete = true;
      allAccess = false;
    }
    if (formCreatePermission.length > 0) {
      isCreate = true;
      isDelete = false;
      allAccess = false;
    }

    if (formCreatePermission.length > 0 && formDeletePermission.length > 0){
      isCreate = true;
      isDelete = true;
    }

  return {
    isDelete,
    isCreate,
    accessLists,
    allAccess,
    returns,
  }
}
