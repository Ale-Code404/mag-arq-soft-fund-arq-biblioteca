<script setup>
import { onMounted, reactive } from 'vue'
import { api, useAction } from '../api'
import ErrorAlert from '../components/ErrorAlert.vue'
import FormField from '../components/FormField.vue'
import SectionCard from '../components/SectionCard.vue'

const fields = [
  { key: 'loanDays', label: 'Días de préstamo', step: 1 },
  { key: 'renewalDays', label: 'Días de renovación', step: 1 },
  { key: 'maxRenewals', label: 'Renovaciones permitidas', step: 1 },
  { key: 'dailyFineValue', label: 'Multa por día de retraso (COP)', step: 1 },
  { key: 'clearanceCm', label: 'Holgura por ejemplar (cm)', step: 0.1 },
  { key: 'defaultThicknessCm', label: 'Grosor por defecto (cm)', step: 0.1 },
]

const form = reactive({})
const load = useAction(async () => Object.assign(form, await api('/settings')))
const save = useAction(async () => Object.assign(form, await api('/settings', { method: 'PUT', body: form })))

onMounted(() => load.run())
</script>

<template>
  <SectionCard title="Parámetros de la biblioteca" class="max-w-xl">
    <ErrorAlert :error="load.error" />
    <form v-if="load.result" class="grid gap-x-4 sm:grid-cols-2" @submit.prevent="save.run()">
      <FormField v-for="f in fields" :key="f.key" :label="f.label" :errors="save.error?.errors?.[f.key]">
        <input v-model.number="form[f.key]" type="number" :step="f.step" min="0" class="input w-full" required />
      </FormField>
      <div class="mt-2 space-y-2 sm:col-span-2">
        <p class="text-sm text-base-content/60">Los cambios aplican a los préstamos nuevos y recalculan la capacidad.</p>
        <ErrorAlert :error="save.error" />
        <div v-if="save.result && !save.error" class="alert alert-success alert-soft">Configuración guardada.</div>
        <button class="btn btn-primary w-full" :disabled="save.loading">Guardar</button>
      </div>
    </form>
  </SectionCard>
</template>
