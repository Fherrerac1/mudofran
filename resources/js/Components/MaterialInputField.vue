<template>
  <div class="input-group" :class="`input-group-${variant} ${getStatus(error, success)}`">
    <label :class="variant === 'static' ? '' : 'form-label'">{{ label }}</label>
    <Field :id="id" :type="type" class="form-control" :class="[getClasses(size), darkMode ? 'text-white' : 'text-black']"
      :name="name" :value="value" :placeholder="placeholder" :isRequired="isRequired" :disabled="disabled"
      @input="(e) => this.$emit('update:value', e.target.value)" />
  </div>
  <ErrorMessage :name="name" class="text-xs text-danger mt-1" as="div"></ErrorMessage>
  <DarkModeObserver @darkModeChange="handleDarkModeChange" />
</template>

<script>
import setMaterialInput from "@/assets/js/material-input.js";
import { Field, ErrorMessage } from "vee-validate";
import DarkModeObserver from "./DarkModeObserver.vue";

export default {
  name: "MaterialInputField",
  components: {
    Field,
    ErrorMessage,
    DarkModeObserver
  },
  data() {
    return {
      darkMode: false,
    }
  },
  props: {
    variant: {
      type: String,
      default: "outline",
    },
    label: {
      type: String,
      default: "",
    },
    size: {
      type: String,
      default: "default",
    },
    success: {
      type: Boolean,
      default: false,
    },
    error: {
      type: Boolean,
      default: false,
    },
    disabled: {
      type: Boolean,
      default: false,
    },
    name: {
      type: String,
      default: "",
    },
    id: {
      type: String,
      required: true,
    },
    value: {
      type: String,
      default: "",
    },
    placeholder: {
      type: String,
      default: "",
    },
    type: {
      type: String,
      default: "text",
    },
    isRequired: {
      type: Boolean,
      default: false,
    },
  },
  emits: ['update:value'],
  mounted() {
    setMaterialInput();
  },
  methods: {
    getClasses: (size) => {
      let sizeValue;

      sizeValue = size ? `form-control-${size}` : null;

      return sizeValue;
    },
    getStatus: (error, success) => {
      let isValidValue;

      if (success) {
        isValidValue = "is-valid";
      } else if (error) {
        isValidValue = "is-invalid";
      } else {
        isValidValue = null;
      }

      return isValidValue;
    },
    handleDarkModeChange(isDark) {
      this.darkMode = isDark; // Update the dark mode status when it changes
    },
  },
};
</script>