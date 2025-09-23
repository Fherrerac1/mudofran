<template>
  <div class="form-check">
    <input :id="id" class="form-check-input" type="radio" :name="name" :checked="modelValue === value"
      @change="updateValue" />
    <label class="custom-control-label" :for="id">
      <slot />
    </label>
  </div>
</template>

<script>
export default {
  name: "MaterialRadio",
  props: {
    name: {
      type: String,
      required: true,
    },
    id: {
      type: String,
      required: true,
    },
    value: {
      type: [String, Number, Boolean], // The value of the radio option
      required: true,
    },
    modelValue: {
      type: [String, Number, Boolean], // The current selected value
      default: null,
    },
  },
  methods: {
    updateValue() {
      // Emit the selected value to the parent
      this.$emit("update:modelValue", this.value);
    },
  },
};
</script>
