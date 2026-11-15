<script setup>
import { computed, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { api } from './api'
import { ROLES } from './format'
import { allowedSections } from './router'
import { clearSession, session } from './session'

const route = useRoute()
const router = useRouter()
const menu = computed(() => (session.user ? allowedSections() : []))

// When the API rejects the token the session is cleared; go back to login
watch(() => session.token, (token) => {
  if (!token) router.push('/login')
})

async function logout() {
  await api('/auth/logout', { method: 'POST' }).catch(() => {})
  clearSession()
}
</script>

<template>
  <RouterView v-if="route.meta.public" />

  <div v-else class="drawer lg:drawer-open">
    <input id="menu" type="checkbox" class="drawer-toggle" />
    <div class="drawer-content flex min-h-screen flex-col bg-base-200">
      <nav class="navbar bg-base-100 shadow-sm">
        <label for="menu" class="btn btn-ghost btn-square lg:hidden" aria-label="Abrir menú">☰</label>
        <span class="flex-1 px-2 font-semibold">{{ menu.find((s) => s.path === route.path)?.label }}</span>
        <div class="flex items-center gap-2">
          <div class="hidden text-right text-sm sm:block">
            <div class="font-medium">{{ session.user?.name }}</div>
            <div class="text-base-content/60">{{ session.user?.roles.map((r) => ROLES[r] ?? r).join(', ') }}</div>
          </div>
          <button class="btn btn-ghost btn-sm" @click="logout">Salir</button>
        </div>
      </nav>
      <main class="mx-auto w-full max-w-6xl flex-1 p-4 lg:p-6">
        <RouterView />
      </main>
    </div>
    <div class="drawer-side">
      <label for="menu" class="drawer-overlay" aria-label="Cerrar menú"></label>
      <aside class="min-h-full w-64 bg-base-100">
        <div class="p-4 text-lg font-bold">📚 Biblioteca UAN</div>
        <ul class="menu w-full">
          <li v-for="section in menu" :key="section.path">
            <RouterLink :to="section.path" active-class="menu-active">{{ section.label }}</RouterLink>
          </li>
        </ul>
      </aside>
    </div>
  </div>
</template>
