import { computed, ref} from 'vue'
import { useStore } from 'vuex'


export const usePermission = (permission)  => {
    const { listCreate, listDelete , listEdit, listAapcr } = permission
       
    const accessLists = computed(() => store.getters['user/access'])
    const store = useStore()

    const createPermission = accessLists.value.filter((value)=>{
            return listCreate  ? listCreate.includes(value.permission_id) : 0
            });
    const deletePermission = accessLists.value.filter((value)=>{
            return listDelete  ? listDelete.includes(value.permission_id) : 0
            });
    const editPermission = accessLists.value.filter((value)=>{
            return listEdit ?  listEdit.includes(value.permission_id) : 0
            });
    
    const aapcrPermission = accessLists.value.filter((value)=>{
      return listAapcr ?  listAapcr.includes(value.permission_id) : 0
      });


    const isCreate = ref(false)
    const isDelete = ref(false)
    const isEdit = ref(false)
    const aapcrFormPermission = ref(false)

    if(createPermission.length > 0){
        isCreate.value = true;
    } 

    if(deletePermission.length > 0){
        isDelete.value = true;
    }

    if(editPermission.length > 0){
        isEdit.value = true;
    }

    if(aapcrPermission.length > 0){
      aapcrFormPermission.value = true;
  }

  return {
    isCreate,
    isDelete,
    isEdit,
    aapcrFormPermission,
  }
}

