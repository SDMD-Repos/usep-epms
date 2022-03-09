import { computed, ref} from 'vue'
import { useStore } from 'vuex'


export const usePermissionCategory = () => {

  const accessLists = computed(() => store.getters['user/access'])
  const store = useStore()
    const categoryPermission = accessLists.value.filter((value)=>{
      return  (value.permission_id == "manager" || 
              value.permission_id == "m-form" || 
              value.permission_id == "mf-subcat");
    });

    const categoryCreatePermission = accessLists.value.filter((value)=>{
      return  value.permission_id == "mfs-create";
    });

    const categoryDeletePermission = accessLists.value.filter((value)=>{
      return value.permission_id == "mfs-delete";
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
              value.permission_id == "mf-functions" ||
              value.permission_id == "mff-create" ||
              value.permission_id == "mff-delete" ||
              value.permission_id == "mf-fields"

            );
    })

    const catPermission = accessLists.value.filter((value)=>{
      return  value.permission_id == "m-form" || 
              value.permission_id == "mf-subcat";
    });
    
    const isCreate = ref(true)
    const isDelete = ref(true)
    const allAccess = ref(true)
    
    if(categoryPermission.length > 0 || notInclude.length > 0){
      allAccess.value = true
      if(notInclude.length > 0 && catPermission.length > 0 ){
        allAccess.value = true;
        isCreate.value = false;
        isDelete.value = false;
      }

      if(notInclude.length > 0 && categoryPermission.length > 0 ){
        allAccess.value = false;
        isCreate.value = false;
        isDelete.value = false;  
        if(catPermission.length > 0){
          allAccess.value = true; 
        }
      }
    }
   
    if(categoryDeletePermission.length > 0){
      isCreate.value = false;
      isDelete.value = true;
      allAccess.value = false; 
    }

    if(categoryCreatePermission.length > 0){
      isCreate.value = true;
      isDelete.value = false;
      allAccess.value = false; 
    }

    if(categoryCreatePermission.length > 0 && categoryDeletePermission.length > 0){
      isCreate.value = true;
      isDelete.value = true; 
    
    }

  return {
    isDelete,
    isCreate,
    accessLists,
    allAccess,
   
  }
}
