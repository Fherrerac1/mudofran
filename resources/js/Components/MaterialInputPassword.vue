<template>
    <div class="position-relative">
        <input
            :id="id"
            :type="visible ? 'text' : 'password'"
            class="form-control border custom-password-input px-3 py-2"
            :class="[darkMode ? 'text-white bg-dark' : 'text-black bg-white']"
            :name="name"
            :required="isRequired"
            :disabled="disabled"
            :readonly="readonly"
            :value="modelValue"
            @input="onInput"
            @focus="setFocus"
            @blur="removeFocus"
            :pattern="pattern || undefined"
            :step="step"
        />

        <button
            type="button"
            :id="`${id}-toggle`"
            class="btn-eye-toggle"
            :class="{ 'has-error': error }"
            aria-label="Mostrar u ocultar contraseña"
            @click="toggleVisibility"
        >
            <i :class="['eye-icon', visible ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye']" />
        </button>

        <DarkModeObserver @darkModeChange="handleDarkModeChange" />
    </div>
</template>

<script>
import DarkModeObserver from './DarkModeObserver.vue';

export default {
    name: "MaterialInputPassword",
    components: {
        DarkModeObserver,
    },
    props: {
        id: { type: String, required: true },
        name: { type: String, default: "password" },
        label: { type: String, default: "" },
        modelValue: { type: [String, Number], default: "" },
        isRequired: { type: Boolean, default: false },
        disabled: { type: Boolean, default: false },
        readonly: { type: Boolean, default: false },
        pattern: { type: String, default: "" },
        step: { type: String, default: undefined },
        error: { type: Boolean, default: false },
        success: { type: Boolean, default: false },
    },
    emits: ['update:modelValue'],
    data() {
        return {
            visible: false,
            darkMode: false,
        };
    },
    methods: {
        toggleVisibility() {
            this.visible = !this.visible;
        },
        onInput(event) {
            this.$emit('update:modelValue', event.target.value);
        },
        getStatus(error, success) {
            if (success) return 'is-valid';
            if (error) return 'is-invalid';
            return '';
        },
        setFocus() {
            const inputGroup = this.$el;
            if (inputGroup) inputGroup.classList.add('is-focused');
        },
        removeFocus() {
            const inputGroup = this.$el;
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
    },
    mounted() {
        if (this.modelValue) {
            this.$el.classList.add('is-filled');
        }
    },
};
</script>

<style scoped>
.custom-password-input {
    padding-right: 2.5rem !important;
}

.custom-password-input.is-invalid {
    padding-right: 6.5rem !important;
}

/* Botón del ojito (ajustado) */
.btn-eye-toggle {
    position: absolute;
    right: 0.5rem;
    top: 50%;
    transform: translateY(-50%);
    width: 2rem;
    height: 2rem;
    padding: 0;
    border: none;
    background: transparent !important;
    box-shadow: none !important;
    outline: none !important;
    cursor: pointer;
}

/* Ícono del ojito */
.eye-icon {
    font-size: 1rem;
    color: #6c757d;
}
</style>
