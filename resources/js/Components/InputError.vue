<script setup>
defineProps({
    message: {
        type: String,
    },
});
</script>

<template>
  <div v-if="message" class="alert alert-danger" role="alert">
      {{ message }}
  </div>
</template>
