import { reactive } from 'vue'

const STORAGE_KEY = 'biblioteca-uan.session'

function readSaved() {
  try {
    return JSON.parse(localStorage.getItem(STORAGE_KEY)) ?? {}
  } catch {
    return {}
  }
}

const saved = readSaved()

export const session = reactive({ token: saved.token ?? null, user: saved.user ?? null })

export function saveSession(token, user) {
  Object.assign(session, { token, user })
  try {
    localStorage.setItem(STORAGE_KEY, JSON.stringify({ token, user }))
  } catch {
    // Without storage the session lasts until the page is reloaded
  }
}

export function clearSession() {
  Object.assign(session, { token: null, user: null })
  try {
    localStorage.removeItem(STORAGE_KEY)
  } catch {
    // Nothing to clean up
  }
}

/** True when the user has at least one of the given permissions. */
export function can(...permissions) {
  return permissions.some((p) => session.user?.permissions?.includes(p))
}
