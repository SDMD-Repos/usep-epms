import { computed } from "vue";
import { useStore } from 'vuex'

export const useModifiedStates = () => {
  const store = useStore()

  const functionsWithProgram = computed( () => {
    const functions = store.getters['formManager/manager'].functions

    return functions.map(function(functionValue){
      const programs = store.getters['formManager/manager'].programs

      return {
        'children' : programs.filter(programValue => programValue.category_id === functionValue.id).map(function(mapValue){
          let mappedKey = functionValue.id + "-" + mapValue.id
          /*if(typeof mapValue.form_id !== 'undefined') {
            mappedKey = mappedKey + "-" + mapValue.form_id
          }*/
          mapValue.key = mappedKey
          mapValue.title = mapValue.name
          mapValue.value = mappedKey
          return mapValue
        }),
        value: functionValue.id,
        key: functionValue.id.toString(),
        title: functionValue.name,
        name: functionValue.name,
        selectable: false,
      }
    })
  })

  return { functionsWithProgram }
}
