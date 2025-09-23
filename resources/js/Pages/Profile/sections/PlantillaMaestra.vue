<template>
    <!-- Botón para abrir el modal -->
    <div class="row mb-2">
        <div class="col d-flex justify-content-start">
            <material-button data-bs-toggle="modal" data-bs-target="#modificarPlantillaMaestra" class="btn btn-sm">
                Plantilla Maestra Factura
            </material-button>
        </div>
    </div>

    <BootstrapModal :-modal-id="'modificarPlantillaMaestra'" :-modal-size="'modal-lg'"
        :title="'Plantilla Maestra Facturas'">
        <!-- Tabs -->
        <div class="d-flex justify-content-between align-items-center mb-2">
            <!-- Tabs -->
            <ul class="nav nav-tabs mb-0">
                <li class="nav-item" v-for="tab in tabs" :key="tab.value">
                    <a class="nav-link" :class="{ active: currentSerie === tab.value }" href="#"
                        @click.prevent="currentSerie = tab.value">
                        {{ tab.label }}
                    </a>
                </li>
            </ul>

            <!-- Botón crear por defecto -->
            <material-button v-if="faltanPlantillasPorDefecto()" class="btn btn-success ms-3"
                @click="crearPlantillasPorDefecto">
                <i class="fas fa-plus me-2"></i>Crear Plantilla Por defecto
            </material-button>
        </div>

        <!-- Advertencia -->
        <div class="alert alert-danger mb-1 pb-2" role="alert">
            <strong>¡Atención!</strong> {{ mensajeAlerta }}
            Haz cambios solo si estás completamente seguro. Revisa el <strong>formato de vista previa</strong> antes de
            guardar.
        </div>

        <!-- Vista previa -->
        <div class="alert border mb-1 p-2 px-4" style="background-color: #f9fbfd;">
            <strong>Vista previa:</strong>
            <div class="text-primary fs-5 text-truncate">
                {{ codigoVistaPrevia }}
            </div>
        </div>

        <!-- Indicador de reordenamiento -->
        <transition name="fade">
            <div v-if="draggingIndex !== null" class="drag-info-indicator">
                <i class="fas fa-arrows-alt me-2"></i>
                Reordenando: <strong>{{ formFields[draggingIndex]?.label || 'Campo' }}</strong>
            </div>
        </transition>

        <form @submit.prevent="submit">
            <!-- Bloque reordenable -->
            <fieldset class="reorderable-fields border rounded p-2 mb-1">
                <legend class="legend-title pb-1">
                    <i class="fas fa-arrows-alt me-2"></i> Campos Reordenables
                </legend>

                <p class="text-muted small pt-3 fw-bolder" style="font-size: 0.8rem;">
                    Puedes mover las cajas para cambiar el orden en el que se generará el código.
                    Arrástralas y suéltalas según prefieras.
                </p>

                <div class="row g-3">
                    <div v-for="(field, index) in formFields" :key="field.key"
                        :class="[field.class, 'drag-item', draggingIndex === index ? 'dragging' : '']" draggable="true"
                        @dragstart="onDragStart(index)" @dragover.prevent @drop="onDrop(index)">
                        <component :is="field.component" v-model="form[field.model]" v-bind="field.props" />
                        <div class="form-text" style="font-size: 0.75rem;" v-if="field.help">{{ field.help }}</div>
                    </div>
                </div>
            </fieldset>

            <!-- Campos fijos -->
            <fieldset class="border rounded p-2" style="background-color: #f9fbfd;">
                <legend class="legend-title pb-1">
                    <i class="fas fa-lock me-2"></i> Campos Fijos
                </legend>

                <p class="text-muted small pt-2 fw-bolder" style="font-size: 0.8rem;">
                    Estos campos son fijos y siempre forman parte del código.
                </p>

                <div class="row g-3">
                    <div v-for="field in fixedFields" :key="field.key" :class="field.class">
                        <component :is="field.component" v-model="form[field.model]" v-bind="field.props" />
                        <div class="form-text" style="font-size: 0.75rem;" v-if="field.help">{{ field.help }}</div>
                    </div>
                </div>
            </fieldset>

            <!-- Botón -->
            <div class="text-end mt-3">
                <MaterialButton type="submit" :disabled="form.processing">
                    <i class="fas fa-save me-2"></i>Actualizar
                </MaterialButton>
            </div>
        </form>
    </BootstrapModal>
</template>

<script>
import { useForm } from '@inertiajs/vue3';
import MaterialInput from '@/Components/MaterialInput.vue';
import MaterialSelect from '@/Components/MaterialSelect.vue';
import MaterialButton from '@/Components/MaterialButton.vue';
import BootstrapModal from '@/Components/BootstrapModal.vue';
import Swal from 'sweetalert2';

export default {
    components: {
        MaterialInput,
        MaterialSelect,
        MaterialButton,
        BootstrapModal,
    },
    props: {
        plantilla: {
            type: Array,
            required: false,
        },
    },
    data() {
        return {
            draggingIndex: null,
            currentSerie: 1,
            form: useForm({
                tipo: '',
                letra: 'F',
                year: 'yy',
                numeroSerieActivo: 1,
                simbolo_1: '-',
                simbolo_2: '/',
                cantidad: 6,
                serie: 1,
            }),
            formFields: [
                {
                    key: 'letra',
                    id: 'Letra',
                    model: 'letra',
                    component: 'MaterialInput',
                    class: 'col-md-4 mb-0',
                    props: {
                        label: 'Letra',
                        type: 'text',
                        class: 'w-100 is-filled ',
                        style: 'margin-top: 0px !important;',
                        error: null,
                        input: () => {
                            // Ya no se forza longitud ni se hace slice
                            this.form.letra = this.form.letra?.trim().toUpperCase();
                        },
                    },
                    help: 'Letra o prefijo inicial del código (ej: F). Puedes dejarlo vacío si no se necesita.',
                },
                {
                    key: 'year',
                    label: 'Formato de año',
                    id: 'Year',
                    model: 'year',
                    component: 'MaterialSelect',
                    class: 'col-md-4 mb-0',
                    props: {
                        id: 'year',
                        options: [
                            { value: 'yy', text: new Date().getFullYear().toString().slice(-2) },
                            { value: 'yyyy', text: new Date().getFullYear().toString() },
                        ],
                        placeholder: 'Formato de año',
                        isRequired: true,
                        error: null,
                        class: 'w-100',
                    },
                    help: 'Elige "yy" (25) o "yyyy" (2025) para calcular el año automáticamente.',
                },
                {
                    key: 'numeroSerieActivo',
                    label: 'Número de serie',
                    model: 'numeroSerieActivo',
                    component: 'MaterialSelect',
                    class: 'col-md-4 mb-0',
                    props: {
                        id: 'numeroSerieActivo',
                        options: [
                            { value: 1, text: 'Sí' },
                            { value: 0, text: 'No' },
                        ],
                        placeholder: '¿Mostrar número de serie?',
                        error: null,
                        class: 'w-100',
                    },
                    help: 'Indica si está activo el número de serie.',
                },
                {
                    key: 'cantidad',
                    label: 'Cantidad de dígitos',
                    id: 'cantidad',
                    model: 'cantidad',
                    component: 'MaterialInput',
                    class: 'col-md-4 mb-0',
                    props: {
                        label: 'Cantidad de dígitos',
                        type: 'number',
                        class: 'w-100 is-filled',
                        style: 'margin-top: 0px !important;',
                        isRequired: true,
                        error: null,
                        input: () => {
                            this.form.cantidad = this.form.cantidad.slice(0, 2);
                        },
                    },
                    help: 'Total de dígitos para la numeración (ej. 6 → 000001).',
                },
            ],
            fixedFields: [
                {
                    key: 'simbolo_1',
                    label: 'Símbolo 1',
                    model: 'simbolo_1',
                    component: 'MaterialSelect',
                    class: 'col-md-4 mb-0',
                    props: {
                        id: 'simbolo_1',
                        options: [
                            { value: '-', text: '-' },
                            { value: '/', text: '/' },
                            { value: '_', text: '_' },
                            { value: '', text: 'Sin símbolo' },
                        ],
                        placeholder: 'Símbolo 1',
                        error: null,
                        class: 'w-100',
                        style: 'margin-top: 7px !important;',
                    },
                    help: 'Separador entre letra, año y tipo.',
                },
                {
                    key: 'simbolo_2',
                    label: 'Símbolo 2',
                    model: 'simbolo_2',
                    component: 'MaterialSelect',
                    class: 'col-md-4 mb-0',
                    id: 'simbolo_2',
                    props: {
                        id: 'simbolo_2',
                        options: [
                            { value: '-', text: '-' },
                            { value: '/', text: '/' },
                            { value: '_', text: '_' },
                            { value: '', text: 'Sin símbolo' },
                        ],
                        placeholder: 'Símbolo 2',
                        error: null,
                        class: 'w-100',
                        style: 'margin-top: 7px !important;',
                    },
                    help: 'Separador entre opcional y número.',
                },
            ],
            tabs: [
                { value: 1, label: 'Factura Normal' },
                { value: 11, label: 'Borrador' },
                { value: 2, label: 'Rectificada' },
            ],
        };
    },
    mounted() {
        this.loadPlantilla();
    },
    computed: {
        /**
         * Devuelve la plantilla maestra asociada al valor actual de currentSerie.
         * Si no se encuentra una plantilla con ese valor, devuelve un objeto vacio.
         * @return {Object}
         */
        activePlantilla() {
            return this.plantilla.find(p => p.serie === this.currentSerie) ?? {};
        },
        /**
         * Devuelve un array de opciones para el select year.
         * Los valores posibles son 'yy' y 'yyyy', y los textos son el año actual
         * con dos o cuatro dígitos respectivamente.
         * @return {Array<Object>}
         */
        yearOptions() {
            const currentYear = new Date().getFullYear();
            return [
                { value: 'yy', text: String(currentYear).slice(-2) },
                { value: 'yyyy', text: String(currentYear) },
            ];
        },
        /**
         * Devuelve el año actual en formato 'yy' o 'yyyy' dependiendo del valor actual de form.year.
         * @return {String} El año actual en formato 'yy' o 'yyyy'.
         */
        previewYear() {
            const currentYear = new Date().getFullYear();
            return this.form.year === 'yy' ? String(currentYear).slice(-2) : String(currentYear);
        },
        /**
         * Devuelve un array de opciones para los selects simbolo_1 y simbolo_2.
         * Contiene los valores '-' (guion), '/' (barra), '_' (guion bajo) y '' (sin simbolo).
         * @return {Array<Object>}
         */
        symbolOptions() {
            return [
                { value: '-', text: '-' },
                { value: '/', text: '/' },
                { value: '_', text: '_' },
                { value: '', text: 'Sin Símbolo' },
            ];
        },
        /**
         * Devuelve una cadena de texto que representa el código de vista previa según el orden dinámico definido en la plantilla maestra.
         *
         * El cálculo del código de vista previa se hace de la siguiente manera:
         * 1. Se crea un objeto valores con los valores de los campos de la plantilla maestra.
         * 2. Se itera sobre el array de campos de la plantilla maestra y para cada campo se aplica una función que devuelve el valor del campo formateado según sea necesario.
         * 3. Se devuelve la concatenación de los valores formateados.
         *
         * @return {string} El código de vista previa.
         */
        codigoVistaPrevia() {
            const valores = {
                letra: this.form.letra ?? '',
                year: this.previewYear ?? '',
                serie: this.form.serie ?? '',
                simbolo_1: this.form.simbolo_1 ?? '',
                simbolo_2: this.form.simbolo_2 ?? '',
                numero: '0'.repeat(this.form.cantidad - 1) + '1',
            };

            const activoSerie = parseInt(this.form.numeroSerieActivo) === 1;
            const partes = [];

            let primerCampoYaPuesto = false;
            let ultimaClave = null;
            let ultimaClaveSinValor = false;

            const campos = {
                /**
                 * Función que devuelve el valor del campo 'letra' formateado según sea necesario.
                 * Si el campo no tiene valor, se devuelve una cadena vacía y se marca como clave sin valor.
                 * Si el campo ya ha sido incluido en el cálculo del código de vista previa, se devuelve el valor
                 * del campo letra precedido por el símbolo 1 si la clave anterior no tiene valor, o sin símbolo
                 * si la clave anterior tiene valor.
                 * Si el campo no ha sido incluido en el cálculo del código de vista previa, se devuelve el valor
                 * del campo letra sin modificación.
                 *
                 */
                letra: () => {
                    if (!valores.letra) {
                        ultimaClaveSinValor = true;
                        return '';
                    }

                    if (primerCampoYaPuesto) {
                        const simbolo = (ultimaClave === 'letra' && ultimaClaveSinValor) ? '' : valores.simbolo_1;
                        return simbolo + valores.letra;
                    }

                    return valores.letra;
                },
                /**
                 * Función que devuelve el valor del campo 'year' formateado según sea necesario.
                 * Si el campo no tiene valor, se devuelve una cadena vacía.
                 * Si el campo ya ha sido incluido en el cálculo del código de vista previa, se devuelve el valor
                 * del campo year precedido por el símbolo 1 si la clave anterior no tiene valor, o sin símbolo
                 * si la clave anterior tiene valor.
                 * Si el campo no ha sido incluido en el cálculo del código de vista previa, se devuelve el valor
                 * del campo year sin modificación.
                 *
                 */
                year: () => {
                    if (!valores.year) return '';

                    if (primerCampoYaPuesto) {
                        const simbolo = (ultimaClave === 'letra' && ultimaClaveSinValor) ? '' : valores.simbolo_1;
                        return simbolo + valores.year;
                    }

                    return valores.year;
                },
                /**
                 * Función que devuelve el valor del campo 'numeroSerieActivo' formateado según sea necesario.
                 * Si el campo 'numeroSerieActivo' no está activo, se devuelve una cadena vacía.
                 * Si el campo ya ha sido incluido en el cálculo del código de vista previa, se devuelve el valor
                 * del campo 'numeroSerieActivo' precedido por el símbolo 1 si la clave anterior no tiene valor, o
                 * sin símbolo si la clave anterior tiene valor.
                 * Si el campo no ha sido incluido en el cálculo del código de vista previa, se devuelve el valor
                 * del campo 'numeroSerieActivo' sin modificación.
                 *
                 */
                numeroSerieActivo: (valor) => {
                    if (!activoSerie) return '';

                    if (primerCampoYaPuesto) {
                        const simbolo = (ultimaClave === 'letra' && ultimaClaveSinValor) ? '' : valores.simbolo_1;
                        return simbolo + valores.serie;
                    }

                    return valores.serie;
                },
                /**
                 * Función que devuelve el valor del campo 'cantidad' formateado según sea necesario.
                 * Si el campo 'cantidad' no ha sido incluido en el cálculo del código de vista previa, se devuelve
                 * el valor del campo 'cantidad' sin modificación. Si el campo ya ha sido incluido en el cálculo del
                 * código de vista previa, se devuelve el valor del campo 'cantidad' precedido por el símbolo 2 si la
                 * clave anterior no tiene valor, o sin símbolo si la clave anterior tiene valor.
                 *
                 */
                cantidad: (valor) => {
                    return primerCampoYaPuesto
                        ? valores.simbolo_2 + valores.numero
                        : valores.numero;
                },
            };

            for (const field of this.formFields) {
                const key = field.model;
                const valor = campos[key]();

                if (valor) {
                    partes.push(valor);
                    primerCampoYaPuesto = true;
                    ultimaClave = key;
                    ultimaClaveSinValor = false;
                }
            }

            return partes.join('');
        },
        /**
         * Devuelve un mensaje de advertencia correspondiente a la serie de factura actual.
         *
         * El mensaje alerta sobre las posibles consecuencias de modificar la plantilla maestra
         * en la numeración de las facturas asociadas a la serie actual.
         *
         * @return {String} Mensaje de advertencia según la serie actual:
         * - Serie 1: Afecta facturas normales.
         * - Serie 11: Afecta facturas borrador.
         * - Serie 2: Afecta facturas rectificadas.
         * - Cualquier otra serie: Afecta la numeración de facturas en general.
         */
        mensajeAlerta() {
            switch (this.currentSerie) {
                case 1:
                    return 'Modificar esta plantilla puede afectar la numeración de facturas normales.';
                case 7:
                    return 'Modificar esta plantilla puede afectar la numeración de facturas borradores.';
                case 2:
                    return 'Modificar esta plantilla puede afectar la numeración de facturas rectificadas.';
                default:
                    return 'Modificar esta plantilla puede afectar la numeración de facturas.';
            }
        },
    },
    methods: {
        /**
         * Carga la plantilla maestra activa en el formulario.
         *
         * Obtiene la plantilla maestra correspondiente a la `currentSerie` y actualiza los campos del formulario
         * con los valores de la plantilla activa. Si no se encuentra una plantilla activa, la función no realiza cambios.
         * Además, ajusta el orden de los campos del formulario según el orden definido en la plantilla.
         */
        loadPlantilla() {
            const p = this.activePlantilla;
            if (!p || !p.serie) return;

            this.form.tipo = p.tipo;
            this.form.letra = p.letra;
            this.form.year = p.year;
            this.form.numeroSerieActivo = p.numeroSerieActivo ?? true;
            this.form.simbolo_1 = p.simbolo_1;
            this.form.simbolo_2 = p.simbolo_2;
            this.form.cantidad = p.cantidad;
            this.form.serie = p.serie;

            this.form.errors = {};

            if (p.orden && Array.isArray(p.orden)) {
                const modelToField = Object.fromEntries(this.formFields.map(f => [f.model, f]));
                this.formFields = p.orden
                    .map(model => modelToField[model])
                    .filter(Boolean);
            }
        },
        /**
         * Envía el formulario con los cambios de la plantilla maestra al servidor.
         *
         * Parchea la ruta `perfil.update_plantilla` con los datos del formulario y actualiza la plantilla
         * maestra correspondiente a la serie actual. Si la actualización es exitosa, muestra un mensaje
         * de éxito con la respuesta del servidor y recarga la página. De lo contrario, muestra un mensaje
         * de error con los detalles del error (si los hay) o un mensaje genérico de error inesperado.
         */
        async submit() {
            try {
                this.form.serie = this.currentSerie;
                this.form.orden = this.formFields.map(f => f.model);

                const response = await axios.patch(route('perfil.update_plantilla'), this.form);

                if (response.data.success) {
                    Swal.fire('¡Éxito!', response.data.message, 'success').then(() => location.reload());
                }
                else {
                    Swal.fire('Error', response.data.message || 'Error inesperado', 'error');
                }
            }
            catch (error) {
                const msg = error.response?.data?.message ||
                    Object.values(error.response?.data?.errors || {}).flat().join('\n') ||
                    'Error inesperado al actualizar la plantilla.';
                Swal.fire('Error', msg, 'error');
            }
        },
        /**
         * Crea las plantillas maestras por defecto para las series de facturas
         * que no tienen una plantilla maestra asociada.
         * Si el servidor devuelve un status 200, muestra un mensaje de éxito y recarga la página.
         * Si el servidor devuelve un status diferente a 200, muestra un mensaje de error con el mensaje
         * de error proporcionado por el servidor, o un mensaje de error genérico si no se proporciona.
         * @return {Promise<void>}
         */
        async crearPlantillasPorDefecto() {
            try {
                const response = await axios.post(route('plantillas_maestras.store_default'));

                if (response.data.success) {
                    Swal.fire('¡Éxito!', response.data.message, 'success').then(() => location.reload());
                }
                else {
                    Swal.fire('Error', response.data.message || 'No se pudo crear.', 'error');
                }
            }
            catch (error) {
                const msg = error.response?.data?.message ||
                    Object.values(error.response?.data?.errors || {}).flat().join('\n') ||
                    'Error inesperado al crear las plantillas.';
                Swal.fire('Error', msg, 'error');
            }
        },
        /**
         * Determina si faltan plantillas por defecto (series 1, 2, 5, 7, 11).
         * Si ya existen todas, no muestra el botón.
         * @return {Boolean}
         */
        faltanPlantillasPorDefecto() {
            const seriesRequeridas = [1, 2, 5, 7, 11];
            const existentes = this.plantilla
                ?.filter(p => p.tipo === 'Factura' && seriesRequeridas.includes(p.serie))
                .map(p => p.serie);

            return [...new Set(seriesRequeridas)].some(serie => !existentes.includes(serie));
        },
        /**
         * Comprueba si un campo es fijo, es decir, no puede ser reordenado.
         * @param {String} key El nombre del campo a comprobar.
         * @return {Boolean} true si el campo es fijo, false en caso contrario.
         */
        isFixed(key) {
            return ['simbolo_1', 'simbolo_2'].includes(key);
        },
        /**
         * Asigna el índice del campo arrastrado al valor draggingIndex para que pueda ser usado en el drop.
         * @param {Number} index El índice del campo que se está arrastrando.
         */
        onDragStart(index) {
            this.draggingIndex = index;
        },
        /**
         * Actualiza el orden de los campos en el formulario según el índice sobre el que se ha soltado el campo.
         * @param {Number} index El índice en el que se ha soltado el campo arrastrado.
         */
        onDrop(index) {
            const draggedField = this.formFields[this.draggingIndex];
            this.formFields.splice(this.draggingIndex, 1);
            this.formFields.splice(index, 0, draggedField);
            this.draggingIndex = null;
        },
    },
    watch: {
        /**
         * Llama a loadPlantilla cuando cambia la serie.
         * @param {Number} newVal Valor de currentSerie después de cambiar.
         */
        currentSerie(newVal) {
            this.loadPlantilla();
            this.form.serie = newVal;
        }
    },
};
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease, transform 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
    transform: translateY(-10px);
}

.drag-info-indicator {
    position: absolute;
    top: 10px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 1060;
    background-color: #f5f8fc;
    color: #002c54;
    border: 1px solid #dbe5f1;
    border-radius: 1.5rem;
    padding: 0.5rem 1.25rem;
    font-size: 0.95rem;
    font-weight: 500;
    box-shadow: 0 4px 12px rgba(0, 44, 84, 0.1);
    display: flex;
    align-items: center;
    transition: all 0.3s ease-in-out;
}

.drag-item {
    transition: all 0.2s ease-in-out;
    border: 1px dashed transparent;
    padding: 8px;

    border-radius: 8px;
    position: relative;
}

.drag-item:hover {
    border-color: #b5b5b5;
    background-color: #f1f1f1;
}

.drag-item.dragging {
    opacity: 0.6;
    background-color: #e9ecef;
    border-color: #6454ff;
    box-shadow: 0 0 10px rgba(100, 84, 255, 0.2);
}

.reorderable-fields {
    border-color: #cfdbe6 !important;
    background-color: #f9fbfd;
    position: relative;
}

.legend-title {
    font-size: 0.95rem;
    font-weight: 500;
    color: #4a5568;
    background-color: #f9fbfd;
    padding: 0 0.75rem;
    margin-bottom: 0;
    border-bottom: 1px solid #dee2e6;
    display: inline-flex;
    align-items: center;
    border-radius: 0.25rem;
}
</style>
