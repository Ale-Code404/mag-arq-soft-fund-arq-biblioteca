<script setup>
defineProps({ error: Object })
</script>

<template>
  <div v-if="error" role="alert" class="alert alert-error alert-soft">
    <span>
      <span v-if="error.rule" class="badge badge-error badge-sm mr-2">{{ error.rule }}</span>
      {{ error.message }}
    </span>
  </div>
</template>
