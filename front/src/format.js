export const GENRES = { CHILDREN: 'Infantil', NARRATIVE: 'Narrativa', ESSAY: 'Ensayo', POETRY: 'Poesía' }

export const ROLES = { Administrator: 'Administrador', Librarian: 'Bibliotecario', Director: 'Director' }

const money = new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP', maximumFractionDigits: 0 })
const decimal = new Intl.NumberFormat('es-CO', { minimumFractionDigits: 1, maximumFractionDigits: 1 })
const integer = new Intl.NumberFormat('es-CO')

export const cop = (value) => money.format(value)
export const cm = (value) => `${decimal.format(value)} cm`
export const count = (value) => integer.format(value)

/** API dates come as YYYY-MM-DD; they are shown without time-zone shifts. */
export function day(value) {
  if (!value) return '—'
  const [y, m, d] = value.split('-')
  return `${d}/${m}/${y}`
}

export function percent(part, total) {
  return total > 0 ? Math.min(100, Math.round((part / total) * 100)) : 0
}
