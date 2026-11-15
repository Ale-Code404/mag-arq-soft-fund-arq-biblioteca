<script setup>
import { reactive } from 'vue'
import { api, useAction } from '../api'
import ErrorAlert from '../components/ErrorAlert.vue'
import FormField from '../components/FormField.vue'
import SectionCard from '../components/SectionCard.vue'
import { GENRES } from '../format'
import { can } from '../session'

const emptyBook = () => ({ isbn: '', title: '', author: '', publisher: '', year: new Date().getFullYear(), genre: 'NARRATIVE', heightCm: 23, widthCm: 15, thicknessCm: 2 })
const book = reactive(emptyBook())
const registerBook = useAction(async () => {
  const saved = await api('/books', { method: 'POST', body: book })
  copy.isbn = saved.isbn
  Object.assign(book, emptyBook())
  return saved
})

const copy = reactive({ isbn: '', code: '', shelf: 'E-01', level: 1 })
const registerCopy = useAction(() => api(`/books/${encodeURIComponent(copy.isbn)}/copies`, {
  method: 'POST',
  body: { code: copy.code, shelf: copy.shelf, level: copy.level },
}))

const move = reactive({ code: '', shelf: 'E-01', level: 1 })
const placeCopy = useAction(() => api(`/copies/${encodeURIComponent(move.code)}/location`, {
  method: 'PUT',
  body: { shelf: move.shelf, level: move.level },
}))
</script>

<template>
  <div class="grid gap-6 lg:grid-cols-2">
    <SectionCard v-if="can('BOOK_REGISTER')" title="Registrar libro" class="lg:row-span-2">
      <form class="grid grid-cols-2 gap-x-3" @submit.prevent="registerBook.run()">
        <FormField label="ISBN" :errors="registerBook.error?.errors?.isbn">
          <input v-model.trim="book.isbn" class="input w-full" required />
        </FormField>
        <FormField label="Temática" :errors="registerBook.error?.errors?.genre">
          <select v-model="book.genre" class="select w-full">
            <option v-for="(label, value) in GENRES" :key="value" :value="value">{{ label }}</option>
          </select>
        </FormField>
        <FormField label="Título" class="col-span-2" :errors="registerBook.error?.errors?.title">
          <input v-model.trim="book.title" class="input w-full" required />
        </FormField>
        <FormField label="Autor" :errors="registerBook.error?.errors?.author">
          <input v-model.trim="book.author" class="input w-full" required />
        </FormField>
        <FormField label="Editorial" :errors="registerBook.error?.errors?.publisher">
          <input v-model.trim="book.publisher" class="input w-full" required />
        </FormField>
        <FormField label="Año" :errors="registerBook.error?.errors?.year">
          <input v-model.number="book.year" type="number" class="input w-full" required />
        </FormField>
        <FormField label="Alto (cm)" :errors="registerBook.error?.errors?.heightCm">
          <input v-model.number="book.heightCm" type="number" step="0.1" class="input w-full" required />
        </FormField>
        <FormField label="Ancho (cm)" :errors="registerBook.error?.errors?.widthCm">
          <input v-model.number="book.widthCm" type="number" step="0.1" class="input w-full" required />
        </FormField>
        <FormField label="Grosor (cm)" :errors="registerBook.error?.errors?.thicknessCm">
          <input v-model.number="book.thicknessCm" type="number" step="0.01" class="input w-full" required />
        </FormField>
        <div class="col-span-2 mt-2 space-y-2">
          <ErrorAlert :error="registerBook.error" />
          <div v-if="registerBook.result && !registerBook.error" class="alert alert-success alert-soft">
            Libro «{{ registerBook.result.title }}» registrado.
          </div>
          <button class="btn btn-primary w-full" :disabled="registerBook.loading">Registrar libro</button>
        </div>
      </form>
    </SectionCard>

    <SectionCard v-if="can('COPY_MANAGE')" title="Registrar y ubicar ejemplar">
      <form class="grid grid-cols-2 gap-x-3" @submit.prevent="registerCopy.run()">
        <FormField label="ISBN del libro"><input v-model.trim="copy.isbn" class="input w-full" required /></FormField>
        <FormField label="Código del ejemplar" :errors="registerCopy.error?.errors?.code">
          <input v-model.trim="copy.code" class="input w-full" placeholder="EJ-0021" required />
        </FormField>
        <FormField label="Estantería" :errors="registerCopy.error?.errors?.shelf">
          <input v-model.trim="copy.shelf" class="input w-full" required />
        </FormField>
        <FormField label="Nivel" :errors="registerCopy.error?.errors?.level">
          <input v-model.number="copy.level" type="number" min="1" class="input w-full" required />
        </FormField>
        <div class="col-span-2 mt-2 space-y-2">
          <ErrorAlert :error="registerCopy.error" />
          <div v-if="registerCopy.result && !registerCopy.error" class="alert alert-success alert-soft">
            Ejemplar {{ registerCopy.result.code }} ubicado en {{ registerCopy.result.shelf }}, nivel {{ registerCopy.result.level }}.
          </div>
          <button class="btn btn-primary w-full" :disabled="registerCopy.loading">Registrar ejemplar</button>
        </div>
      </form>
    </SectionCard>

    <SectionCard v-if="can('COPY_MANAGE')" title="Reubicar ejemplar">
      <form class="grid grid-cols-3 gap-x-3" @submit.prevent="placeCopy.run()">
        <FormField label="Ejemplar"><input v-model.trim="move.code" class="input w-full" required /></FormField>
        <FormField label="Estantería"><input v-model.trim="move.shelf" class="input w-full" required /></FormField>
        <FormField label="Nivel"><input v-model.number="move.level" type="number" min="1" class="input w-full" required /></FormField>
        <div class="col-span-3 mt-2 space-y-2">
          <ErrorAlert :error="placeCopy.error" />
          <div v-if="placeCopy.result && !placeCopy.error" class="alert alert-success alert-soft">
            Ejemplar {{ placeCopy.result.code }} movido a {{ placeCopy.result.shelf }}, nivel {{ placeCopy.result.level }}.
          </div>
          <button class="btn btn-primary w-full" :disabled="placeCopy.loading">Reubicar</button>
        </div>
      </form>
    </SectionCard>
  </div>
</template>
