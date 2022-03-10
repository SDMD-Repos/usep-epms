import { computed, ref } from 'vue'
import { useStore } from 'vuex'


export const usePermissionProgram = () => {

  const accessLists = computed(() => store.getters['user/access'])
  const store = useStore()
    const programPermission = accessLists.value.filter((value)=>{
      return  (value.permission_id == "manager" || 
              value.permission_id == "m-form" || 
              value.permission_id == "mf-programs");
    });

    const programCreatePermission = accessLists.value.filter((value)=>{
      return  value.permission_id == "mfp-create";
    });

    const programDeletePermission = accessLists.value.filter((value)=>{
      return value.permission_id == "mfp-delete";
    });

    const notInclude = accessLists.value.filter((value)=>{
      return (value.permission_id == "m-group" || 
              value.permission_id == "mg-create" || 
              value.permission_id == "mg-delete" || 
              value.permission_id == "mg-edit" || 
              value.permission_id == "m-measures" || 
              value.permission_id == "mm-create" || 
              value.permission_id == "mm-delete" ||
              value.permission_id == "mm-edit" ||
              // value.permission_id == "m-signatories" ||
              // value.permission_id == "ms-aapcr" ||
              // value.permission_id == "ms-opcrvp" ||
              // value.permission_id == "ms-opcr" ||
              // value.permission_id == "ms-cpcr" ||
              // value.permission_id == "ms-ipcr" ||
              value.permission_id == "mf-functions" ||
              value.permission_id == "mff-create" ||
              value.permission_id == "mff-delete" ||
              value.permission_id == "mf-subcat" ||
              value.permission_id == "mfs-create" ||
              value.permission_id == "mfs-delete" ||
              value.permission_id == "mf-fields" ||
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

    console.log(notInclude)

    const progPermission = accessLists.value.filter((value)=>{
      return  value.permission_id == "m-form" || 
              value.permission_id == "mf-programs";
    });
    
    const isCreate = ref(true)
    const isDelete = ref(true)
    const allAccess = ref(true)
    
    if(programPermission.length > 0 || notInclude.length > 0){
      allAccess.value = true
      if(notInclude.length > 0 && progPermission.length > 0 ){
        allAccess.value = true;
        isCreate.value = false;
        isDelete.value = false;
       
      }

      if(notInclude.length > 0 && programPermission.length > 0 ){
        allAccess.value = false;
        isCreate.value = false;
        isDelete.value = false;  
        if(progPermission.length > 0){
          allAccess.value = true; 
        }
      }
    }
   
    if(programDeletePermission.length > 0){
      isCreate.value = false;
      isDelete.value = true;
      allAccess.value = false; 
     
    }

    if(programCreatePermission.length > 0){
      isCreate.value = true;
      isDelete.value = false;
      allAccess.value = false; 
    }

    if(programCreatePermission.length > 0 && programDeletePermission.length > 0){
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
