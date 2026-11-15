<script setup>
import { reactive } from 'vue'
import { api, useAction } from '../api'
import ErrorAlert from '../components/ErrorAlert.vue'
import FormField from '../components/FormField.vue'
import SectionCard from '../components/SectionCard.vue'
import { cop, day } from '../format'
import { can } from '../session'

const readerForm = reactive({ identification: '', name: '', address: '' })
const register = useAction(async () => {
  const reader = await api('/readers', { method: 'POST', body: { ...readerForm, address: readerForm.address || null } })
  Object.assign(readerForm, { identification: '', name: '', address: '' })
  return reader
})

const search = reactive({ identification: '' })
const history = useAction(() => api(`/readers/${encodeURIComponent(search.identification)}/history`))

const pay = useAction(async (fineId) => {
  await api(`/fines/${fineId}/payment`, { method: 'POST' })
  await history.run()
})

function loanStatus(loan) {
  if (!loan.active) return { label: `Devuelto ${day(loan.returnedOn)}`, css: 'badge-ghost' }
  return loan.overdue ? { label: `Vencido (${loan.daysLate} días)`, css: 'badge-error' } : { label: 'Activo', css: 'badge-info' }
}
</script>

<template>
  <div class="grid gap-6 lg:grid-cols-3">
    <SectionCard v-if="can('LOAN_VIEW')" title="Historial del lector" class="lg:col-span-2">
      <form class="flex flex-wrap items-end gap-2" @submit.prevent="history.run()">
        <FormField label="Identificación">
          <input v-model.trim="search.identification" class="input" placeholder="1030405060" required />
        </FormField>
        <button class="btn btn-primary" :disabled="history.loading">Consultar</button>
      </form>
      <ErrorAlert :error="history.error" />
      <ErrorAlert :error="pay.error" />

      <template v-if="history.result && !history.error">
        <div class="flex flex-wrap items-center gap-3">
          <span class="text-lg font-semibold">{{ history.result.reader.name }}</span>
          <span class="text-base-content/60">{{ history.result.reader.address }}</span>
          <span v-if="history.result.totalPendingFines" class="badge badge-warning">
            Multas pendientes: {{ cop(history.result.totalPendingFines) }}
          </span>
          <span v-else class="badge badge-success badge-soft">Sin multas pendientes</span>
        </div>
        <p v-if="!history.result.loans.length" class="text-base-content/60">No tiene préstamos.</p>
        <div v-else class="overflow-x-auto">
          <table class="table table-sm">
            <thead>
              <tr><th>Ejemplar</th><th>Libro</th><th>Prestado</th><th>Entrega</th><th>Estado</th><th>Multa</th></tr>
            </thead>
            <tbody>
              <tr v-for="loan in history.result.loans" :key="loan.id">
                <td>{{ loan.copyCode }}</td>
                <td>{{ loan.title }}</td>
                <td>{{ day(loan.loanedOn) }}</td>
                <td>{{ day(loan.dueOn) }}<span v-if="loan.renewals" class="text-xs"> (renovado)</span></td>
                <td><span class="badge badge-sm" :class="loanStatus(loan).css">{{ loanStatus(loan).label }}</span></td>
                <td>
                  <template v-if="loan.fine">
                    {{ cop(loan.fine.value) }}
                    <span v-if="loan.fine.status === 'PAID'" class="badge badge-success badge-sm">Pagada {{ day(loan.fine.paidOn) }}</span>
                    <button v-else-if="can('FINE_REGISTER_PAYMENT')" class="btn btn-warning btn-xs"
                      :disabled="pay.loading" @click="pay.run(loan.fine.id)">Registrar pago</button>
                    <span v-else class="badge badge-warning badge-sm">Pendiente</span>
                  </template>
                  <span v-else>—</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </template>
    </SectionCard>

    <SectionCard v-if="can('READER_REGISTER')" title="Registrar lector">
      <form class="space-y-2" @submit.prevent="register.run()">
        <FormField label="Identificación" :errors="register.error?.errors?.identification">
          <input v-model.trim="readerForm.identification" class="input w-full" required />
        </FormField>
        <FormField label="Nombre" :errors="register.error?.errors?.name">
          <input v-model.trim="readerForm.name" class="input w-full" required />
        </FormField>
        <FormField label="Dirección (opcional)" :errors="register.error?.errors?.address">
          <input v-model.trim="readerForm.address" class="input w-full" />
        </FormField>
        <ErrorAlert :error="register.error" />
        <div v-if="register.result && !register.error" class="alert alert-success alert-soft">
          Lector {{ register.result.name }} registrado.
        </div>
        <button class="btn btn-primary w-full" :disabled="register.loading">Registrar</button>
      </form>
    </SectionCard>
  </div>
</template>
