import { computed, ref} from 'vue'
import { useStore } from 'vuex'


export const usePermissionGroups = () => {

  const accessLists = computed(() => store.getters['user/access'])
  const store = useStore()
    const groupPermission = accessLists.value.filter((value)=>{
      return  ( value.permission_id == "manager" || 
                value.permission_id == "m-group" );
    });

    const gpPermission = accessLists.value.filter((value)=>{
      return  ( value.permission_id == "m-group" );
    });

    const groupCreatePermission = accessLists.value.filter((value)=>{
      return  value.permission_id == "mg-create";
    });

    const groupDeletePermission = accessLists.value.filter((value)=>{
      return value.permission_id == "mg-delete";
    });

    const groupEditPermission = accessLists.value.filter((value)=>{
        return value.permission_id == "mg-edit";
      });
  

    const notInclude = accessLists.value.filter((value)=>{
      return (value.permission_id == "m-form" || 
              value.permission_id == "mff-create" || 
              value.permission_id == "mff-delete" || 
              value.permission_id == "mff-functions" ||
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
              value.permission_id == "mf-fields"
            );
    })

    const isCreate = ref(false)
    const isDelete = ref(false)
    const allAccess = ref(false)
    const isEdit = ref(false)
    
    if(groupPermission.length > 0 || notInclude.length > 0){
      allAccess.value = true;

      if(groupPermission.length > 0 ){
        allAccess.value = true;
        isCreate.value = true;
        isDelete.value = true;
        isEdit.value = true;
        alert("zz")
      }

      if(notInclude.length > 0){
        allAccess.value = false;
        isCreate.value = false;
        isDelete.value = false;
        isEdit.value = false;
        alert("xx")
        if(gpPermission.length > 0){
          allAccess.value = true;
        }
      }
     
      alert("zzz")
    }
   
  
    if(groupCreatePermission.length > 0 ||  groupDeletePermission.length > 0 || groupEditPermission.length > 0){
        allAccess.value = false;
        alert("132")
        if(groupCreatePermission.length > 0){
            isCreate.value = true;
        }else{
            isCreate.value = false;
        }
        if(groupDeletePermission.length > 0){
            isDelete.value = true;
        }else{
            isDelete.value = false;
        }
        if(groupEditPermission.length > 0){
            isEdit.value = true;
            alert("55")
        }else{
          alert("5")
            isEdit.value = false;
        }
    }
   
  return {
    isDelete,
    isCreate,
    accessLists,
    allAccess,
    isEdit,
  }
}
