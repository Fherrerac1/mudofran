<template>
    <div class="mb-3">
      <label :for="id" class="form-label">{{ label }}</label>
      <input
        :id="id"
        :type="type"
        :value="modelValue"
        @input="$emit('update:modelValue', $event.target.value)"
        @focus="handleFocus(true)"
        @blur="handleFocus(false)"
        :class="['form-control', { 'is-focus': hasFocus, 'is-invalid': error }]"
        :placeholder="placeholder"
        :disabled="disabled"
      />
      <div v-if="error" class="invalid-feedback">
        {{ error }}
      </div>
    </div>
  </template>
  
  <script setup>
  import { ref } from 'vue';
  
  const props = defineProps({
    id: String,
    modelValue: String,
    type: {
      type: String,
      default: 'text',
    },
    label: String,
    placeholder: String,
    error: String,
    disabled: Boolean,
  });
  
  const hasFocus = ref(false);
  
  function handleFocus(focused) {
    hasFocus.value = focused;
  }
  </script>
  
  <style scoped>
  .is-focus {
    border-color: #66afe9; /* Bootstrap's focus color */
    box-shadow: 0 0 0 0.2rem rgba(38, 143, 255, 0.25); /* Bootstrap's focus shadow */
  }
  </style>
  