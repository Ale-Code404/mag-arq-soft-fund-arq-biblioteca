<script setup>
defineProps({ label: String, errors: Array })
</script>

<template>
  <fieldset class="fieldset">
    <legend class="fieldset-legend">{{ label }}</legend>
    <slot />
    <p v-for="message in errors ?? []" :key="message" class="label text-error">{{ message }}</p>
  </fieldset>
</template>
