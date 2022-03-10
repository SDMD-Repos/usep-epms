import { computed, ref} from 'vue'
import { useStore } from 'vuex'


export const usePermissionFields = () => {

  const accessLists = computed(() => store.getters['user/access'])
  const store = useStore()
    const formPermission = accessLists.value.filter((value)=>{
      return  (value.permission_id == "manager" || 
              value.permission_id == "m-form" || 
              value.permission_id == "mf-fields");
    });


    const notInclude = accessLists.value.filter((value)=>{
      return (value.permission_id == "m-group" || 
              value.permission_id == "mg-create" || 
              value.permission_id == "mg-edit" || 
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
              value.permission_id == "mfs-delete" ||
              value.permission_id == "mfs-functions" ||
              value.permission_id == "ms-aapcr" ||
              value.permission_id == "msa-create" ||
              value.permission_id == "msa-delete" ||
              value.permission_id == "ms-opcrvp" ||
              value.permission_id == "msovp-create" ||
              value.permission_id == "msovp-delete" ||
              value.permission_id == "ms-opcr" ||
              value.permission_id == "mso-create" ||
              value.permission_id == "mso-create" ||
              value.permission_id == "ms-cpcr" ||
              value.permission_id == "msc-create" ||
              value.permission_id == "msc-delete" ||
              value.permission_id == "ms-ipcr" ||
              value.permission_id == "msi-create" ||
              value.permission_id == "msi-delete" 
            );
    })

    const funcPermission = accessLists.value.filter((value)=>{
      return  value.permission_id == "m-form" || 
              value.permission_id == "mf-fields";
    });
    
    const isCreate = ref(true)
    const allAccess = ref(true)
    
    if(formPermission.length > 0 || notInclude.length > 0){
      allAccess.value = true
      if(notInclude.length > 0 && funcPermission.length > 0 ){
        allAccess.value = true;
        isCreate.value = false;
      }

      if(notInclude.length > 0 && formPermission.length > 0 ){
        allAccess.value = false;
        isCreate.value = false;
        if(funcPermission.length > 0){
          allAccess.value = true; 
        }
      }
    }

  return {
    isCreate,
    accessLists,
    allAccess,
   
  }
}
