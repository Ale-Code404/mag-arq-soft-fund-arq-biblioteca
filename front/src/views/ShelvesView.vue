<script setup>
import { onMounted, reactive } from 'vue'
import { api, useAction } from '../api'
import ErrorAlert from '../components/ErrorAlert.vue'
import FormField from '../components/FormField.vue'
import SectionCard from '../components/SectionCard.vue'
import { cm, percent } from '../format'

const shelves = useAction(() => api('/shelves'))

const newLevel = (number) => ({ number, lengthCm: 90, heightCm: 32 })
const form = reactive({ code: '', levels: [newLevel(1)] })

const create = useAction(async () => {
  const shelf = await api('/shelves', { method: 'POST', body: form })
  Object.assign(form, { code: '', levels: [newLevel(1)] })
  await shelves.run()
  return shelf
})

function addLevel() {
  form.levels.push(newLevel(Math.max(0, ...form.levels.map((l) => l.number)) + 1))
}

onMounted(() => shelves.run())
</script>

<template>
  <div class="grid gap-6 lg:grid-cols-3">
    <div class="space-y-4 lg:col-span-2">
      <ErrorAlert :error="shelves.error" />
      <p v-if="shelves.result?.length === 0" class="text-base-content/60">Aún no hay estanterías registradas.</p>
      <SectionCard v-for="shelf in shelves.result ?? []" :key="shelf.code" :title="`Estantería ${shelf.code}`">
        <p class="text-sm text-base-content/70">
          {{ cm(shelf.usedCm) }} usados de {{ cm(shelf.installedCm) }} · {{ cm(shelf.availableCm) }} disponibles
        </p>
        <div class="overflow-x-auto">
          <table class="table table-sm">
            <thead>
              <tr><th>Nivel</th><th>Largo × alto</th><th>Ejemplares</th><th class="w-1/3">Ocupación</th></tr>
            </thead>
            <tbody>
              <tr v-for="level in shelf.levels" :key="level.number">
                <td>{{ level.number }}</td>
                <td>{{ cm(level.lengthCm) }} × {{ cm(level.heightCm) }}</td>
                <td>{{ level.copies }}</td>
                <td>
                  <progress class="progress progress-primary" :value="percent(level.usedCm, level.lengthCm)" max="100"></progress>
                  <span class="text-xs">{{ cm(level.availableCm) }} libres</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </SectionCard>
    </div>

    <SectionCard title="Nueva estantería">
      <form class="space-y-2" @submit.prevent="create.run()">
        <FormField label="Código" :errors="create.error?.errors?.code">
          <input v-model.trim="form.code" class="input w-full" placeholder="E-03" required />
        </FormField>
        <div v-for="(level, i) in form.levels" :key="i" class="flex items-end gap-2">
          <FormField :label="`Nivel ${level.number}: largo`" :errors="create.error?.errors?.[`levels.${i}.lengthCm`]">
            <input v-model.number="level.lengthCm" type="number" step="0.1" min="0.1" class="input" required />
          </FormField>
          <FormField label="alto (cm)" :errors="create.error?.errors?.[`levels.${i}.heightCm`]">
            <input v-model.number="level.heightCm" type="number" step="0.1" min="0.1" class="input" required />
          </FormField>
          <button type="button" class="btn btn-ghost btn-square mb-1" :disabled="form.levels.length === 1"
            aria-label="Quitar nivel" @click="form.levels.splice(i, 1)">✕</button>
        </div>
        <button type="button" class="btn btn-ghost btn-sm" @click="addLevel">+ Agregar nivel</button>
        <ErrorAlert :error="create.error" />
        <div v-if="create.result && !create.error" class="alert alert-success alert-soft">Estantería {{ create.result.code }} registrada.</div>
        <button class="btn btn-primary w-full" :disabled="create.loading">Registrar</button>
      </form>
    </SectionCard>
  </div>
</template>
