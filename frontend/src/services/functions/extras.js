export const useExtras = () => {
  const findInNested = (arrayData, value) => {
    let result
    if(typeof arrayData !== 'undefined') {
      arrayData.some(o => result = o.key === value && o || findInNested(o.children, value));
    }
    return result || undefined;
  }

  const getAllAlphabet = () => {
    const alpha = Array.from(Array(26)).map((e, i) => i + 65)
    return alpha.map((x) => ({ value: String.fromCharCode(x), label: String.fromCharCode(x) }))
  }

  const reduceKeys = (item, fields) => {
    return Object.keys(item).reduce(function(obj, k) {
      if (fields.includes(k)) {
        obj[k] = item[k];
      }
      return obj;
    }, {})
  }

  return { findInNested, getAllAlphabet, reduceKeys }
}
