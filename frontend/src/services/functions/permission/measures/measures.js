import { computed, ref} from 'vue'
import { useStore } from 'vuex'


export const usePermissionMeasures = () => {

  const accessLists = computed(() => store.getters['user/access'])
  const store = useStore()
    const measuresPermission = accessLists.value.filter((value)=>{
      return  ( value.permission_id == "manager" || 
                value.permission_id == "m-measures" );
    });

    const mPermission = accessLists.value.filter((value)=>{
      return  ( value.permission_id == "m-measures" );
    });

    const measuresCreatePermission = accessLists.value.filter((value)=>{
      return  value.permission_id == "mm-create";
    });

    const measuresDeletePermission = accessLists.value.filter((value)=>{
      return value.permission_id == "mm-delete";
    });

    const measuresEditPermission = accessLists.value.filter((value)=>{
        return value.permission_id == "mm-edit";
      });
  

    const notInclude = accessLists.value.filter((value)=>{
      return (value.permission_id == "m-form" || 
              value.permission_id == "mff-create" || 
              value.permission_id == "mff-delete" || 
              value.permission_id == "mff-functions" ||
              value.permission_id == "m-group" || 
              value.permission_id == "mg-create" || 
              value.permission_id == "mg-delete" ||
              value.permission_id == "mg-edit" ||
            //   value.permission_id == "m-signatories" ||
            //   value.permission_id == "ms-aapcr" ||
            //   value.permission_id == "ms-opcrvp" ||
            //   value.permission_id == "ms-opcr" ||
            //   value.permission_id == "ms-cpcr" ||
            //   value.permission_id == "ms-ipcr" ||
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
    
    if(measuresPermission.length > 0 || notInclude.length > 0){
      allAccess.value = true;

      if(measuresPermission.length > 0 ){
        allAccess.value = true;
        isCreate.value = true;
        isDelete.value = true;
        isEdit.value = true;
      
      }

      if(notInclude.length > 0){
        allAccess.value = false;
        isCreate.value = false;
        isDelete.value = false;
        isEdit.value = false;
      
        if(mPermission.length > 0){
          allAccess.value = true;
        }
      }
    
    }
   
  
    if(measuresCreatePermission.length > 0 ||  measuresDeletePermission.length > 0 || measuresEditPermission.length > 0){
        allAccess.value = false;
        
        if(measuresCreatePermission.length > 0){
            isCreate.value = true;
        }else{
            isCreate.value = false;
        }
        if(measuresDeletePermission.length > 0){
            isDelete.value = true;
        }else{
            isDelete.value = false;
        }
        if(measuresEditPermission.length > 0){
            isEdit.value = true;
        }else{
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
