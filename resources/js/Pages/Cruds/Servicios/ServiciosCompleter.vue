<template>
    <div class="input-group mt-3" :class="`input-group-${variant} ${getStatus(error, success)}`" ref="inputServicio">
        <label :class="[variant === 'static' ? '' : 'form-label', darkMode ? 'text-white' : 'text-dark']">
            {{ label }}
        </label>
        <input ref="inputElement" :id="id" :type="type" class="form-control px-1"
            :class="darkMode ? 'text-white' : 'text-black'" :name="name" :required="isRequired" :disabled="disabled"
            :readonly="readonly" :value="servicio.contenido" @input="onInput" @focus="setFocus" @blur="removeFocus"
            :pattern="pattern || undefined" :step="step" />
    </div>
</template>

<script>
import DarkModeObserver from '@/Components/DarkModeObserver.vue';

export default {
    name: "ServiciosCompleter",
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
        servicio: { type: Object, default: () => ({}) }  // Accept the 'servicio' object as a prop
    },
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
            const inputServicio = this.$refs.inputServicio;
            if (inputServicio) {
                inputServicio.classList.add('is-focused');
            }
        },
        removeFocus() {
            const inputServicio = this.$refs.inputServicio;
            if (inputServicio) {
                if (this.modelValue) {
                    inputServicio.classList.add('is-filled');
                } else {
                    inputServicio.classList.remove('is-filled');
                }
                inputServicio.classList.remove('is-focused');
            }
        },
        handleDarkModeChange(isDark) {
            this.darkMode = isDark; // Update the dark mode status when it changes
        },
        initializeAutocomplete() {
            $(this.$refs.inputElement).autocomplete({
                source: (request, response) => {
                    $.ajax({
                        url: "/servicios/search",
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
                    $.ajax({
                        url: `/servicios/${ui.item.value}/data`,
                        dataType: "json",
                        success: (data) => {
                            // Emit the updated `from` object to the parent
                            this.$emit('update:modelValue', data.nombre);
                            this.servicio.contenido = data.nombre;
                            this.servicio.descripcion = data.observaciones;
                            this.servicio.id = data.id;
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
        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = 'https://code.jquery.com/ui/1.14.1/themes/base/jquery-ui.css';
        document.head.appendChild(link);

        if (this.modelValue) {
            this.$refs.inputServicio.classList.add('is-filled');
        }
        this.initializeAutocomplete(); // Initialize autocomplete on mount
    },
    beforeDestroy() {
        if (this.$refs.inputElement) {
            $(this.$refs.inputElement).autocomplete("destroy"); // Destroy autocomplete when component is destroyed
        }
    },
};
</script>
