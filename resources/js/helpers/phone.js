export function normalizePhone(raw) {
    let digits = raw.replace(/\D/g, '')
  
    if (digits.startsWith('380')) return '+' + digits
    if (digits.startsWith('80')) return '+3' + digits
    if (digits.startsWith('0')) return '+38' + digits
    if (digits.startsWith('8')) return '+380' + digits.slice(1)
  
    return '+380' + digits
  }
  
  export function isValidPhone(phone) {
    const normalized = normalizePhone(phone)
    return normalized.length === 13 && normalized.startsWith('+380')
  }
  