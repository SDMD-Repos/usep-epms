export const filters = {
  numbersWithCommasDecimal(data) {
    let number = data || ''

    if (number !== '') {
      number = number.toFixed(2).replace(/./g, function(c, i, a) {
        return i > 0 && c !== '.' && (a.length - i) % 3 === 0 ? ',' + c : c
      })
    }

    return number
  },
  numbersWithCommas(data) {
    let number = data || ''

    if (number !== '') {
      number = number.toString().replace(/./g, function(c, i, a) {
        return i > 0 && c !== '.' && (a.length - i) % 3 === 0 ? ',' + c : c
      })
    }

    return number
  },
}
