<script setup>
import { reactive } from 'vue'
import { api, useAction } from '../api'
import ErrorAlert from '../components/ErrorAlert.vue'
import FormField from '../components/FormField.vue'
import SectionCard from '../components/SectionCard.vue'
import { cop, day } from '../format'

const lendForm = reactive({ readerIdentification: '', copyCode: '' })
const lend = useAction(() => api('/loans', { method: 'POST', body: lendForm }))

const renewForm = reactive({ copyCode: '' })
const renew = useAction(() => api(`/loans/${encodeURIComponent(renewForm.copyCode)}/renewal`, { method: 'POST' }))

const returnForm = reactive({ copyCode: '' })
const giveBack = useAction(() => api('/returns', { method: 'POST', body: returnForm }))
</script>

<template>
  <div class="grid gap-6 lg:grid-cols-3">
    <SectionCard title="Prestar">
      <form class="space-y-2" @submit.prevent="lend.run()">
        <FormField label="Identificación del lector">
          <input v-model.trim="lendForm.readerIdentification" class="input w-full" required />
        </FormField>
        <FormField label="Código del ejemplar">
          <input v-model.trim="lendForm.copyCode" class="input w-full" placeholder="EJ-0002" required />
        </FormField>
        <ErrorAlert :error="lend.error" />
        <div v-if="lend.result && !lend.error" class="alert alert-success alert-soft">
          «{{ lend.result.title }}» prestado a {{ lend.result.readerName }}. Entregar el {{ day(lend.result.dueOn) }}.
        </div>
        <button class="btn btn-primary w-full" :disabled="lend.loading">Registrar préstamo</button>
      </form>
    </SectionCard>

    <SectionCard title="Renovar">
      <form class="space-y-2" @submit.prevent="renew.run()">
        <FormField label="Código del ejemplar">
          <input v-model.trim="renewForm.copyCode" class="input w-full" required />
        </FormField>
        <p class="text-sm text-base-content/60">Una sola renovación, si el préstamo no está vencido y el lector no tiene multas.</p>
        <ErrorAlert :error="renew.error" />
        <div v-if="renew.result && !renew.error" class="alert alert-success alert-soft">
          Renovado. Nueva fecha de entrega: {{ day(renew.result.dueOn) }}.
        </div>
        <button class="btn btn-primary w-full" :disabled="renew.loading">Renovar</button>
      </form>
    </SectionCard>

    <SectionCard title="Devolver">
      <form class="space-y-2" @submit.prevent="giveBack.run()">
        <FormField label="Código del ejemplar">
          <input v-model.trim="returnForm.copyCode" class="input w-full" required />
        </FormField>
        <ErrorAlert :error="giveBack.error" />
        <template v-if="giveBack.result && !giveBack.error">
          <div v-if="giveBack.result.fine" class="alert alert-warning alert-soft">
            Devolución con {{ giveBack.result.fine.daysLate }} días de retraso.
            Multa pendiente de {{ cop(giveBack.result.fine.value) }}.
          </div>
          <div v-else class="alert alert-success alert-soft">Devolución a tiempo, sin multa.</div>
        </template>
        <button class="btn btn-primary w-full" :disabled="giveBack.loading">Registrar devolución</button>
      </form>
    </SectionCard>
  </div>
</template>
