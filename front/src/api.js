import { reactive } from 'vue'
import { clearSession, session } from './session'

/** Backend API base URL, from VITE_API_URL in .env */
const BASE_URL = (import.meta.env.VITE_API_URL ?? 'http://localhost:8080/api').replace(/\/$/, '')

/** Error from the API: message in Spanish, business rule code and per-field errors. */
export class ApiError extends Error {
  constructor(status, body) {
    super(body?.message ?? 'No fue posible comunicarse con el servidor.')
    this.status = status
    this.rule = body?.rule ?? null
    this.errors = body?.errors ?? {}
  }
}

export async function api(path, { method = 'GET', body } = {}) {
  const headers = { Accept: 'application/json' }
  if (body !== undefined) headers['Content-Type'] = 'application/json'
  if (session.token) headers.Authorization = `Bearer ${session.token}`

  let response
  try {
    response = await fetch(`${BASE_URL}${path}`, { method, headers, body: body === undefined ? undefined : JSON.stringify(body) })
  } catch {
    throw new ApiError(0, null)
  }
  if (response.status === 204) return null

  const data = await response.json().catch(() => null)
  if (!response.ok) {
    // An expired or revoked token ends the session; the router sends the user to login
    if (response.status === 401 && session.token) clearSession()
    throw new ApiError(response.status, data)
  }
  return data
}

/** Wraps an async call with loading, error and result state for a form or button. */
export function useAction(fn) {
  const state = reactive({ loading: false, error: null, result: null, run })

  async function run(...args) {
    state.loading = true
    state.error = null
    try {
      state.result = await fn(...args)
      return state.result
    } catch (e) {
      state.error = e
      return undefined
    } finally {
      state.loading = false
    }
  }

  return state
}
