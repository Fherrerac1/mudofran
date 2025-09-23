<template>
  <div class="input-group mt-3" :class="`input-group-${variant} ${getStatus(error, success)}`" ref="inputGroup">
    <label :class="[variant === 'static' ? '' : 'form-label', darkMode ? 'text-white' : 'text-dark']">
      {{ label }}
    </label>
    <input ref="inputElement" :id="id" :type="type" class="form-control px-1"
      :class="darkMode ? 'text-white' : 'text-black'" :name="name" :required="isRequired" :disabled="disabled"
      :readonly="readonly" :value="modelValue" @input="onInput" @focus="setFocus" @blur="removeFocus"
      :pattern="pattern || undefined" />
    <DarkModeObserver @darkModeChange="handleDarkModeChange" />
  </div>
</template>

<script>
import DarkModeObserver from '@/Components/DarkModeObserver.vue';

export default {
  name: "ClientesCompleter",
  components: {
    DarkModeObserver,
  },
  props: {
    variant: { type: String, default: "outline" },
    label: { type: String, default: "" },
    size: { type: String, default: "default" },
    success: { type: Boolean, default: false },
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
    from: { type: Object, default: () => ({}) }, // passed as prop
  },
  emits: ['update:modelValue', 'update-from'], // Include custom event
  data() {
    return {
      darkMode: false,
    };
  },
  methods: {
    onInput(event) {
      this.$emit('update:modelValue', event.target.value);
    },
    getStatus(error, success) {
      if (success) return 'is-valid';
      if (error) return 'is-invalid';
      return '';
    },
    setFocus() {
      const inputGroup = this.$refs.inputGroup;
      if (inputGroup) {
        inputGroup.classList.add('is-focused');
      }
    },
    removeFocus() {
      const inputGroup = this.$refs.inputGroup;
      if (inputGroup) {
        if (this.modelValue) {
          inputGroup.classList.add('is-filled');
        } else {
          inputGroup.classList.remove('is-filled');
        }
        inputGroup.classList.remove('is-focused');
      }
    },
    handleDarkModeChange(isDark) {
      this.darkMode = isDark;
    },
    initializeAutocomplete() {
      $(this.$refs.inputElement).autocomplete({
        source: (request, response) => {
          $.ajax({
            url: "/clientes/search",
            dataType: "json",
            data: { q: request.term },
            success: (data) => {
              response(
                data.map(item => ({
                  label: item.nombre,
                  value: item.id,
                }))
              );
            },
          });
        },
        minLength: 1,
        select: (event, ui) => {
          this.$emit('update:modelValue', ui.item.label); // Update modelValue when an item is selected
          $.ajax({
            url: `/clientes/${ui.item.value}/data`,
            dataType: "json",
            success: (data) => {
              // Emit the updated `from` object to the parent
              this.$emit('update-from', {
                cliente_nombre: data.nombre,
                dni_cliente: data.dni,
                pais_cliente: data.pais,
                email_cliente: data.email,
                phone_cliente: data.telefono,
                cp_cliente: data.cp,
                direccion_cliente: data.direccion,
              });
            },
          });
        },
      });
    },
  },
  watch: {
    modelValue(newVal) {
      if (this.$refs.inputElement) {
        this.$refs.inputElement.value = newVal;
      }
    },
  },
  mounted() {
    if (this.modelValue) {
      this.$refs.inputGroup.classList.add('is-filled');
    }
    this.initializeAutocomplete(); // Initialize autocomplete on mount
  },
};
</script>
