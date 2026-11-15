<script setup>
import { onMounted, reactive, ref } from 'vue'
import { api, useAction } from '../api'
import ErrorAlert from '../components/ErrorAlert.vue'
import FormField from '../components/FormField.vue'
import SectionCard from '../components/SectionCard.vue'
import { ROLES } from '../format'
import { session } from '../session'

const users = useAction(() => api('/users'))

// Every change goes through the same action so its error shows above the table
const change = useAction(async (request) => {
  await request()
  await users.run()
})
const toggleActive = (user) => change.run(() => api(`/users/${user.id}`, { method: 'PUT', body: { active: !user.active } }))
const toggleRole = (user, role) => {
  const roles = user.roles.includes(role) ? user.roles.filter((r) => r !== role) : [...user.roles, role]
  return change.run(() => api(`/users/${user.id}/roles`, { method: 'PUT', body: { roles } }))
}

const emptyUser = () => ({ login: '', password: '', identification: '', name: '', position: '', roles: ['Librarian'] })
const form = reactive(emptyUser())
const create = useAction(async () => {
  const user = await api('/users', { method: 'POST', body: form })
  Object.assign(form, emptyUser())
  await users.run()
  return user
})

const passwordFor = ref(null)
const newPassword = ref('')
const changePassword = () => change.run(async () => {
  await api(`/users/${passwordFor.value.id}`, { method: 'PUT', body: { password: newPassword.value } })
  passwordFor.value = null
  newPassword.value = ''
})

onMounted(() => users.run())
</script>

<template>
  <div class="grid gap-6 lg:grid-cols-3">
    <SectionCard title="Usuarios" class="lg:col-span-2">
      <ErrorAlert :error="users.error" />
      <ErrorAlert :error="change.error" />
      <div class="overflow-x-auto">
        <table class="table table-sm">
          <thead>
            <tr><th>Usuario</th><th>Roles</th><th>Estado</th><th></th></tr>
          </thead>
          <tbody>
            <tr v-for="user in users.result ?? []" :key="user.id">
              <td>
                <div class="font-medium">{{ user.name }}</div>
                <div class="text-xs text-base-content/60">{{ user.login }} · {{ user.position }}</div>
              </td>
              <td>
                <div class="flex flex-wrap gap-1">
                  <button v-for="(label, role) in ROLES" :key="role" class="badge badge-sm cursor-pointer"
                    :class="user.roles.includes(role) ? 'badge-primary' : 'badge-ghost'"
                    :disabled="change.loading" @click="toggleRole(user, role)">{{ label }}</button>
                </div>
              </td>
              <td>
                <input type="checkbox" class="toggle toggle-success toggle-sm" :checked="user.active"
                  :disabled="change.loading || user.id === session.user?.id" @change="toggleActive(user)" />
              </td>
              <td><button class="btn btn-ghost btn-xs" @click="passwordFor = user">Contraseña</button></td>
            </tr>
          </tbody>
        </table>
      </div>
      <form v-if="passwordFor" class="flex flex-wrap items-end gap-2" @submit.prevent="changePassword">
        <FormField :label="`Nueva contraseña para ${passwordFor.login}`">
          <input v-model="newPassword" type="password" minlength="8" class="input" required />
        </FormField>
        <button class="btn btn-primary">Guardar</button>
        <button type="button" class="btn btn-ghost" @click="passwordFor = null">Cancelar</button>
      </form>
    </SectionCard>

    <SectionCard title="Nuevo usuario">
      <form class="space-y-1" @submit.prevent="create.run()">
        <FormField label="Nombre" :errors="create.error?.errors?.name"><input v-model.trim="form.name" class="input w-full" required /></FormField>
        <FormField label="Identificación" :errors="create.error?.errors?.identification"><input v-model.trim="form.identification" class="input w-full" required /></FormField>
        <FormField label="Cargo" :errors="create.error?.errors?.position"><input v-model.trim="form.position" class="input w-full" required /></FormField>
        <FormField label="Usuario" :errors="create.error?.errors?.login"><input v-model.trim="form.login" class="input w-full" required /></FormField>
        <FormField label="Contraseña" :errors="create.error?.errors?.password">
          <input v-model="form.password" type="password" minlength="8" class="input w-full" required />
        </FormField>
        <FormField label="Roles" :errors="create.error?.errors?.roles">
          <label v-for="(label, role) in ROLES" :key="role" class="label">
            <input v-model="form.roles" type="checkbox" :value="role" class="checkbox checkbox-sm" /> {{ label }}
          </label>
        </FormField>
        <ErrorAlert :error="create.error" />
        <div v-if="create.result && !create.error" class="alert alert-success alert-soft">Usuario {{ create.result.login }} creado.</div>
        <button class="btn btn-primary w-full" :disabled="create.loading">Crear usuario</button>
      </form>
    </SectionCard>
  </div>
</template>
