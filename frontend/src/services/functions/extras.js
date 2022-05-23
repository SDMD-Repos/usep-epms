export const useExtras = () => {
  const findInNested = (arrayData, value) => {
    let result
    if(typeof arrayData !== 'undefined') {
      arrayData.some(o => result = o.key === value && o || findInNested(o.children, value));
    }
    return result || undefined;
  }

  return { findInNested }
}
