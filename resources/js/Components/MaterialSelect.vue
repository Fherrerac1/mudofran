<template>
  <div>
    <Multiselect :disabled="readonly || disabled" :required="isRequired" v-model="selectedOption" :options="options"
      track-by="text" label="text" :placeholder="placeholder" :class="darkMode ? 'bg-dark text-secondary' : ''"
      :searchable="true" />
    <DarkModeObserver @darkModeChange="handleDarkModeChange" />
  </div>
</template>

<script>
import DarkModeObserver from './DarkModeObserver.vue';
import Multiselect from '@vueform/multiselect';
import '@vueform/multiselect/themes/default.css';

export default {
  name: "MaterialSelect",
  components: {
    DarkModeObserver,
    Multiselect,
  },
  props: {
    variant: { type: String, default: "outline" },
    options: { type: Array, required: true },
    success: { type: Boolean, default: false },
    error: { type: Boolean, default: false },
    disabled: { type: Boolean, default: false },
    name: { type: String, default: "" },
    id: { type: String, required: true },
    modelValue: { type: [String, Number], default: "" },
    isRequired: { type: [Boolean, Number], default: false },
    readonly: { type: Boolean, default: false }, // Add this prop
    placeholder: { type: String, default: "" },
  },
  emits: ['update:modelValue'],
  data() {
    return {
      darkMode: false,
      selectedOption: this.modelValue,
    };
  },
  watch: {
    selectedOption(value) {
      this.$emit('update:modelValue', value);
    },
    modelValue(newValue) {
      this.selectedOption = newValue;
    },
  },
  methods: {
    getStatus(error, success) {
      if (success) return 'is-valid';
      if (error) return 'is-invalid';
      return '';
    },
    handleDarkModeChange(isDark) {
      this.darkMode = isDark;
    },
  },
};
</script>
