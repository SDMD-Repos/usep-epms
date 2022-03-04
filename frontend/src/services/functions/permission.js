import { computed } from 'vue'
import { useStore } from 'vuex'


export const usePermission = () => {

const accessLists = computed(() => store.getters['user/access'])
const formPermission = accessLists.value.filter((value)=>{
  return value.permission_id == "m-form";
});


return {
  accessLists,
  formPermission,
}
}
