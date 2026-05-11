import { cpf, cnpj } from '@dmalbuquerque/cpf-cnpj-validator'

export function useMasks() {
  /**
   * Masks CPF (000.000.000-00)
   */
  const maskCPF = (val) => {
    if (!val) return ''
    let v = val.replace(/\D/g, '')
    if (v.length > 11) v = v.substring(0, 11)
    if (v.length <= 3) return v
    if (v.length <= 6) return `${v.substring(0, 3)}.${v.substring(3)}`
    if (v.length <= 9) return `${v.substring(0, 3)}.${v.substring(3, 6)}.${v.substring(6)}`
    return `${v.substring(0, 3)}.${v.substring(3, 6)}.${v.substring(6, 9)}-${v.substring(9)}`
  }

  /**
   * Masks CNPJ (00.000.000/0000-00)
   */
  const maskCNPJ = (val) => {
    if (!val) return ''
    let v = val.replace(/\D/g, '')
    if (v.length > 14) v = v.substring(0, 14)
    if (v.length <= 2) return v
    if (v.length <= 5) return `${v.substring(0, 2)}.${v.substring(2)}`
    if (v.length <= 8) return `${v.substring(0, 2)}.${v.substring(2, 5)}.${v.substring(5)}`
    if (v.length <= 12) return `${v.substring(0, 2)}.${v.substring(2, 5)}.${v.substring(5, 8)}/${v.substring(8)}`
    return `${v.substring(0, 2)}.${v.substring(2, 5)}.${v.substring(5, 8)}/${v.substring(8, 12)}-${v.substring(12)}`
  }

  /**
   * Masks Phone ((00) 00000-0000)
   */
  const maskPhone = (val) => {
    if (!val) return ''
    let v = val.replace(/\D/g, '')
    if (v.length > 11) v = v.substring(0, 11)
    if (v.length <= 2) return v
    if (v.length <= 6) return `(${v.substring(0, 2)}) ${v.substring(2)}`
    if (v.length <= 10) return `(${v.substring(0, 2)}) ${v.substring(2, 6)}-${v.substring(6)}`
    return `(${v.substring(0, 2)}) ${v.substring(2, 7)}-${v.substring(7)}`
  }

  /**
   * Validates CPF using @dmalbuquerque/cpf-cnpj-validator
   */
  const validateCPF = (val) => {
    if (!val) return false
    return cpf.isValid(val)
  }

  /**
   * Validates CNPJ using @dmalbuquerque/cpf-cnpj-validator
   */
  const validateCNPJ = (val) => {
    if (!val) return false
    return cnpj.isValid(val)
  }

  return {
    maskCPF,
    maskCNPJ,
    maskPhone,
    validateCPF,
    validateCNPJ
  }
}
