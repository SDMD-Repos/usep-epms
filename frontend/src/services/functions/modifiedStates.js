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

  const measuresList  = computed(() => {
    const list = store.state.formManager.measures
    let finalList = []

    list.map(i => {
      let items = []

      if(i.is_custom === 1) {
        finalList.push({
          value: i.key,
          label: i.name,
          displayAsItems: i.display_as_items,
          isCustom: i.is_custom,
          items: i.custom_items,
        })
      } else {
        i.categories.forEach(e => {
          finalList.push({
            value: e.id + "-" + i.id,
            label: i.name + (e.numbering ? e.numbering.toLowerCase() : ''),
            displayAsItems: i.display_as_items,
            isCustom: i.is_custom,
            measureId: i.id,
            categoryId: e.id,
            items: e.items,
          })
        })
      }
    })

    return finalList
  })

  return { functionsWithProgram, measuresList }
}
