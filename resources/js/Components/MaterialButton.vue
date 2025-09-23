<template>
  <button
    :type="type"
    class="btn mb-0"
    :class="[
      'btn-base',
      getClasses(variant, color, size, fullWidth),
      { 'selected-button': active },
    ]"
  >
    <slot />
  </button>
</template>

<script>
export default {
  name: "MaterialButton",
  props: {
    color: { type: String, default: "success" },
    size: { type: String, default: "md" },
    variant: { type: String, default: "fill" },
    fullWidth: { type: Boolean, default: false },
    active: { type: Boolean, default: false },
    type: { type: String, default: "button" },
  },
  methods: {
    getClasses(variant, color, size, fullWidth) {
      let colorValue, sizeValue, fullWidthValue;

      colorValue = `unique_bg`;
      sizeValue = size ? `btn-${size}` : null;
      fullWidthValue = fullWidth ? `w-100` : null;

      return `${colorValue} ${sizeValue} ${fullWidthValue}`;
    },
  },
};
</script>
