<template>
  <div class="input-group mt-3" :class="`input-group-${variant} ${getStatus(error, success)}`" ref="inputGroup">
    <label :for="id" class="fw-bold text-muted" :class="[
      variant === 'static' ? '' : 'form-label',
      darkMode ? 'text-white' : 'text-dark',
      labelClass,
      { 'has-star': shouldShowAsterisk }
    ]">
      {{ label }}
    </label>

    <input :id="id" :type="type" class="form-control px-1" :class="darkMode ? 'text-white' : 'text-black'" :name="name"
      :required="isRequired" :aria-required="isRequired" :disabled="disabled" :readonly="readonly" :value="modelValue"
      @input="onInput" @focus="onFocus" @blur="onBlur" :pattern="pattern || undefined" v-bind="$attrs" />
  </div>
</template>

<script>
export default {
  name: "MaterialInput",
  inheritAttrs: false,
  props: {
    // 🔹 Ahora por defecto es "static"
    variant: { type: String, default: "static" },
    label: { type: String, default: "" },
    size: { type: String, default: "default" },
    success: { type: Boolean, default: false },
    labelClass: { type: String, default: "" },
    error: { type: Boolean, default: false },
    disabled: { type: Boolean, default: false },
    name: { type: String, default: "" },
    id: { type: String, required: true },
    modelValue: { type: [String, Number], default: "" },
    type: { type: String, default: "text" },
    isRequired: { type: Boolean, default: false },
    color: { type: String, default: "dark" },
    readonly: { type: Boolean, default: false },
    pattern: { type: String, default: "" },

    // Tri-estado:
    asterisk: { type: [Boolean, String, Number, null], default: null },
  },
  emits: ["update:modelValue", "input", "blur", "focus"],
  data() {
    return { darkMode: false };
  },
  computed: {
    shouldShowAsterisk() {
      if (this.asterisk === null || typeof this.asterisk === "undefined") {
        return !!this.isRequired;
      }
      const v =
        typeof this.asterisk === "string"
          ? this.asterisk.trim().toLowerCase()
          : this.asterisk;
      return !["false", "0", "no", "off"].includes(String(v));
    },
  },
  methods: {
    onInput(event) {
      const val = event.target.value;
      this.$emit("update:modelValue", val);
      this.$emit("input", val);
    },
    getStatus(error, success) {
      if (success) return "is-valid";
      if (error) return "is-invalid";
      return "";
    },
    onFocus() {
      this.$refs.inputGroup?.classList.add("is-focused");
      this.$emit("focus");
    },
    onBlur() {
      const el = this.$refs.inputGroup;
      if (el) {
        if (this.modelValue) el.classList.add("is-filled");
        else el.classList.remove("is-filled");
        el.classList.remove("is-focused");
      }
      this.$emit("blur");
    },
  },
  mounted() {
    if (this.modelValue) {
      this.$refs.inputGroup?.classList.add("is-filled");
    }
  },
};
</script>

<style scoped>
/* Elimina fondo autofill WebKit */
input:-webkit-autofill,
input:-webkit-autofill:hover,
input:-webkit-autofill:focus,
input:-webkit-autofill:active {
  -webkit-box-shadow: 0 0 0px 1000px white inset !important;
  -webkit-text-fill-color: #000 !important;
  transition: background-color 5000s ease-in-out 0s;
}

/* Subrayado Material */
.input-group.input-group-dynamic .form-control,
.input-group.input-group-dynamic .form-control:focus,
.input-group.input-group-static .form-control,
.input-group.input-group-static .form-control:focus {
  background-image: linear-gradient(0deg,
      var(--unique-color) 2px,
      rgba(156, 39, 176, 0) 0),
    linear-gradient(0deg, #d2d2d2 1px, hsla(0, 0%, 82%, 0) 0);
}

/* Asterisco pegado al label */
.has-star::after {
  content: "*";
  color: #dc3545;
  font-weight: 700;
  margin-left: 0;
}
</style>
