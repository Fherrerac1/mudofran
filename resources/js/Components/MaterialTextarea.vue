<template>
  <div class="input-group" :class="`input-group-${variant} ${getStatus(error, success)}`">
    <textarea :id="id" class="form-control" :class="DarkMode ? 'text-white' : ''" :rows="rows" :name="name"
      :value="modelValue" :placeholder="placeholder" :required="isRequired" :disabled="disabled" :maxlength="maxLength"
      @input="onInput"></textarea>
  </div>
  <DarkModeObserver @darkModeChange="handleDarkModeChange" />

</template>


<script>
import setMaterialInput from "@/assets/js/material-input.js";
import DarkModeObserver from "./DarkModeObserver.vue";

export default {
  name: "MaterialTextarea",
  components: {
    DarkModeObserver
  },
  props: {
    variant: {
      type: String,
      default: "outline",
    },
    id: {
      type: String,
      required: true,
    },
    name: {
      type: String,
      default: "",
    },
    modelValue: {
      type: String,
      default: "",
    },
    placeholder: {
      type: String,
      default: "Your text here...",
    },
    isRequired: Boolean,
    disabled: {
      type: Boolean,
      default: false,
    },
    rows: {
      type: Number,
      default: 5,
    },
    success: {
      type: Boolean,
      default: false,
    },
    error: {
      type: Boolean,
      default: false,
    },
    maxLength: {
      type: Number,
      default: 400,
    },
  },
  data() {
    return {
      DarkMode: false
    }
  },

  emits: ['update:modelValue'],
  mounted() {
    setMaterialInput();
  },
  methods: {
    getStatus(error, success) {
      if (success) {
        return "is-valid";
      } else if (error) {
        return "is-invalid";
      }
      return null;
    },
    onInput(event) {
      this.$emit('update:modelValue', event.target.value); // Emit value on input
    },
    handleDarkModeChange(isDark) {
      this.DarkMode = isDark; // Update the dark mode status when it changes
    }
  },
};
</script>
