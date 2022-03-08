import { computed, ref} from 'vue'
import { useStore } from 'vuex'


export const usePermissionForm = () => {

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
      return (value.permission_id == "m-group" || 
              value.permission_id == "mg-create" || 
              value.permission_id == "mg-delete" || 
              value.permission_id == "m-measures" || 
              value.permission_id == "mm-create" || 
              value.permission_id == "mm-delete" ||
              value.permission_id == "m-signatories" ||
              value.permission_id == "ms-aapcr" ||
              value.permission_id == "ms-opcrvp" ||
              value.permission_id == "ms-opcr" ||
              value.permission_id == "ms-cpcr" ||
              value.permission_id == "ms-ipcr" ||
              value.permission_id == "mf-programs" ||
              value.permission_id == "mfp-create" ||
              value.permission_id == "mfp-delete" ||
              value.permission_id == "mf-subcat" ||
              value.permission_id == "mfs-create" ||
              value.permission_id == "mfs-delete"
            );
    })

    const funcPermission = accessLists.value.filter((value)=>{
      return  value.permission_id == "m-form" || 
              value.permission_id == "mf-functions";
    });
    
    const isCreate = ref(true)
    const isDelete = ref(true)
    const allAccess = ref(true)
    
    if(formPermission.length > 0 || notInclude.length > 0){
      allAccess.value = true
      if(notInclude.length > 0 && funcPermission.length > 0 ){
        allAccess.value = true;
        isCreate.value = false;
        isDelete.value = false;
       
      }

      if(notInclude.length > 0 && formPermission.length > 0 ){
        allAccess.value = false;
        isCreate.value = false;
        isDelete.value = false;  
        if(funcPermission.length > 0){
          allAccess.value = true; 
        }
      }
    }
   
    if(formDeletePermission.length > 0){
      isCreate.value = false;
      isDelete.value = true;
      allAccess.value = false; 
     
    }

    if(formCreatePermission.length > 0){
      isCreate.value = true;
      isDelete.value = false;
      allAccess.value = false; 
    }

    if(formCreatePermission.length > 0 && formDeletePermission.length > 0){
      isCreate.value = true;
      isDelete.value = true; 
    
    }

    // alert(isCreate)
    // alert(allAccess)
  return {
    isDelete,
    isCreate,
    accessLists,
    allAccess,
   
  }
}
