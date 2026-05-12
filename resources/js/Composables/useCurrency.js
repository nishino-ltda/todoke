export function useCurrency(locale = 'pt-BR') {
  function formatCurrency(value) {
    return new Intl.NumberFormat(locale, {
      style: 'currency',
      currency: 'BRL',
    }).format(Number(value || 0))
  }

  return { formatCurrency }
}
