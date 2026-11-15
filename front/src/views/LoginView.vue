<script setup>
import { reactive } from 'vue'
import { useRouter } from 'vue-router'
import { api, useAction } from '../api'
import ErrorAlert from '../components/ErrorAlert.vue'
import FormField from '../components/FormField.vue'
import { allowedSections } from '../router'
import { saveSession } from '../session'

const router = useRouter()
const form = reactive({ login: '', password: '' })

const login = useAction(async () => {
  const { token, user } = await api('/auth/login', { method: 'POST', body: form })
  saveSession(token, user)
  router.push(allowedSections()[0]?.path ?? '/')
})
</script>

<template>
  <div class="flex min-h-screen items-center justify-center bg-base-200 p-4">
    <form class="card w-full max-w-sm bg-base-100 shadow" @submit.prevent="login.run()">
      <div class="card-body">
        <h1 class="card-title text-2xl">📚 Biblioteca UAN</h1>
        <p class="text-base-content/70">Sistema de capacidad y préstamos</p>
        <FormField label="Usuario" :errors="login.error?.errors?.login">
          <input v-model.trim="form.login" class="input w-full" autocomplete="username" required />
        </FormField>
        <FormField label="Contraseña" :errors="login.error?.errors?.password">
          <input v-model="form.password" type="password" class="input w-full" autocomplete="current-password" required />
        </FormField>
        <ErrorAlert :error="login.error" />
        <button class="btn btn-primary mt-2" :disabled="login.loading">
          <span v-if="login.loading" class="loading loading-spinner loading-sm"></span>
          Ingresar
        </button>
      </div>
    </form>
  </div>
</template>
