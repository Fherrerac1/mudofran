<template>
  <div class="form-check form-switch d-flex">
    <input :id="id" class="form-check-input" :class="$attrs.class" type="checkbox" :name="name"
      :checked="modelValue === 1" @change="$emit('update:modelValue', $event.target.checked ? 1 : 0)" />
    <label class="form-check-label ms-3" :class="labelClass" :for="id">
      <slot />
    </label>
  </div>
</template>

<script>
export default {
  name: "MaterialSwitch",
  props: {
    name: {
      type: String,
      required: true,
    },
    id: {
      type: String,
      required: true,
    },
    modelValue: {
      type: [Number, String], // Allow both number and string types
      default: 0, // Default to 0 (unchecked)
    },
    labelClass: {
      type: String,
      default: "",
    },
  },
};
</script>
