<script setup>
defineProps({ title: String })
</script>

<template>
  <section class="card bg-base-100 shadow-sm">
    <div class="card-body">
      <h2 class="card-title">{{ title }}</h2>
      <slot />
    </div>
  </section>
</template>
