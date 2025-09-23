<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    href: {
        type: String,
        required: true,
    },
});
</script>

<template>
  <a
      :href="href"
      class="d-block w-100 px-3 py-2 text-start text-sm text-dark bg-light rounded transition-all hover:bg-secondary focus:bg-secondary"
  >
      <slot />
  </a>
</template>

