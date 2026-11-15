<script setup>
import { onMounted, reactive } from 'vue'
import { api, useAction } from '../api'
import ErrorAlert from '../components/ErrorAlert.vue'
import FormField from '../components/FormField.vue'
import SectionCard from '../components/SectionCard.vue'
import { cm, count, GENRES, percent } from '../format'

const query = (scope, value) => api(`/capacity?scope=${scope}${value ? `&value=${encodeURIComponent(value)}` : ''}`)

const overview = useAction(async () => ({
  total: await query('total'),
  genres: await Promise.all(Object.keys(GENRES).map((g) => query('genre', g))),
}))

const shelfCode = reactive({ value: '' })
const shelf = useAction(() => query('shelf', shelfCode.value.trim()))

onMounted(() => overview.run())
</script>

<template>
  <div class="space-y-6">
    <ErrorAlert :error="overview.error" />
    <div v-if="overview.loading && !overview.result" class="loading loading-dots loading-lg"></div>

    <template v-if="overview.result">
      <div class="stats stats-vertical w-full bg-base-100 shadow-sm lg:stats-horizontal">
        <div class="stat">
          <div class="stat-title">Capacidad instalada</div>
          <div class="stat-value text-2xl">{{ cm(overview.result.total.installedCm) }}</div>
          <div class="stat-desc">Suma del largo de todos los niveles</div>
        </div>
        <div class="stat">
          <div class="stat-title">Usada</div>
          <div class="stat-value text-2xl">{{ cm(overview.result.total.usedCm) }}</div>
          <div class="stat-desc">
            <progress class="progress progress-primary w-32"
              :value="percent(overview.result.total.usedCm, overview.result.total.installedCm)" max="100"></progress>
            {{ percent(overview.result.total.usedCm, overview.result.total.installedCm) }} %
          </div>
        </div>
        <div class="stat">
          <div class="stat-title">Disponible</div>
          <div class="stat-value text-2xl text-success">{{ cm(overview.result.total.availableCm) }}</div>
          <div class="stat-desc">Caben aprox. {{ count(overview.result.total.availableBooks) }} libros más</div>
        </div>
        <div class="stat">
          <div class="stat-title">Capacidad teórica</div>
          <div class="stat-value text-2xl">{{ count(overview.result.total.theoreticalBooks) }}</div>
          <div class="stat-desc">libros con grosor promedio de {{ cm(overview.result.total.averageThicknessCm) }}</div>
        </div>
      </div>

      <SectionCard title="Por temática">
        <div class="overflow-x-auto">
          <table class="table">
            <thead>
              <tr><th>Temática</th><th>Grosor promedio</th><th>Capacidad teórica</th><th>Libros que aún caben</th></tr>
            </thead>
            <tbody>
              <tr v-for="g in overview.result.genres" :key="g.value">
                <td>{{ GENRES[g.value] }}</td>
                <td>
                  {{ cm(g.averageThicknessCm) }}
                  <span v-if="g.usesDefaultThickness" class="badge badge-ghost badge-sm" title="No hay ejemplares de esta temática">por defecto</span>
                </td>
                <td>{{ count(g.theoreticalBooks) }}</td>
                <td>{{ count(g.availableBooks) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </SectionCard>
    </template>

    <SectionCard title="Por estantería">
      <form class="flex flex-wrap items-end gap-2" @submit.prevent="shelf.run()">
        <FormField label="Código de la estantería">
          <input v-model="shelfCode.value" class="input" placeholder="E-01" required />
        </FormField>
        <button class="btn btn-primary" :disabled="shelf.loading">Consultar</button>
      </form>
      <ErrorAlert :error="shelf.error" />
      <div v-if="shelf.result" class="grid gap-2 sm:grid-cols-3">
        <div>Instalada: <b>{{ cm(shelf.result.installedCm) }}</b></div>
        <div>Usada: <b>{{ cm(shelf.result.usedCm) }}</b></div>
        <div>Disponible: <b>{{ cm(shelf.result.availableCm) }}</b></div>
        <div>Grosor promedio: <b>{{ cm(shelf.result.averageThicknessCm) }}</b></div>
        <div>Capacidad teórica: <b>{{ count(shelf.result.theoreticalBooks) }}</b> libros</div>
        <div>Aún caben: <b>{{ count(shelf.result.availableBooks) }}</b> libros</div>
      </div>
    </SectionCard>
  </div>
</template>
