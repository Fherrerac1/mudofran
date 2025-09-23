<template>
    <div class="input-group mt-3" :class="`input-group-${variant} ${getStatus(error, success)}`" ref="inputGroup">
        <label :class="[variant === 'static' ? '' : 'form-label', darkMode ? 'text-white' : 'text-dark']">
            {{ label }}
        </label>
        <input ref="inputElement" :id="id" :type="type" class="form-control px-1"
            :class="darkMode ? 'text-white' : 'text-black'" :name="name" :required="isRequired" :disabled="disabled"
            :readonly="readonly" v-model="localModelValue" @focus="setFocus" @blur="removeFocus"
            :pattern="pattern || undefined" :step="step" />
    </div>
</template>

<script>
import DarkModeObserver from '@/Components/DarkModeObserver.vue';

export default {
    name: "productosCompleter",
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
        step: { type: String, default: undefined },
        producto: { type: Object, default: () => ({}) },
    },
    data() {
        return {
            darkMode: false,
            localValue: this.modelValue, // Initialize localValue from modelValue
        };
    },
    computed: {
        localModelValue: {
            get() {
                return this.localValue;
            },
            set(value) {
                this.localValue = value; // Update the localValue
                this.$emit('update:modelValue', value); // Emit the updated value
            },
        },
    },
    methods: {
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
                if (this.localModelValue) {
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
                        url: "/productos/search",
                        dataType: "json",
                        data: {
                            q: request.term,
                        },
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
                    $.ajax({
                        url: `/productos/${ui.item.value}/data`,
                        dataType: "json",
                        success: (data) => {
                            this.producto.nombre = data.nombre;
                            this.producto.precio = data.precio;
                            this.producto.descripcion = data.observaciones;
                            this.localModelValue = data.nombre; // Update localModelValue
                        },
                    });
                },
            });
        },
    },
    watch: {
        modelValue(newVal) {
            this.localValue = newVal; // Sync local value when parent updates
        },
    },
    mounted() {
        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = 'https://code.jquery.com/ui/1.14.1/themes/base/jquery-ui.css';
        document.head.appendChild(link);

        if (this.modelValue) {
            this.$refs.inputGroup.classList.add('is-filled');
        }
        this.initializeAutocomplete();
    },
    beforeDestroy() {
        if (this.$refs.inputElement) {
            $(this.$refs.inputElement).autocomplete("destroy");
        }
    },
};
</script>
