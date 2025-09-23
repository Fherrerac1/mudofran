<template>
    <BootstrapModal -modal-id="facturas-recurrentes-update-modal" class="factura-recurrente-modal"
        -modal-size="modal-md" title="Factura Recurrente">

        <div class="container-fluid overflow-hidden">
            <main class="mt-0 main-content">
                <section>
                    <div class="container mb-4 p-0">
                        <div class="row justify-content-center">
                            <div class="col mx-auto">
                                <div class="card mt-3">
                                    <div class="card-header p-0 position-relative mx-3">
                                        <div class="style_color forms border-radius-lg p-3">
                                            <h5 class="font-weight-bolder text-white">Factura Recurrente
                                                <small class="text-light">
                                                    {{ facturas_recurrente.factura?.numFactura || '' }}
                                                </small>
                                            </h5>
                                            <p class="text-white-50 small mb-0">Configura la recurrencia automática
                                                de esta factura</p>
                                        </div>
                                    </div>
                                    <div class="py-4 card-body">
                                        <form @submit.prevent="submitForm">

                                            <div class="row g-3">
                                                <div class="form-group">
                                                    <label class="form-label fw-bold" for="frecuencia">Frecuencia del
                                                        cobro *</label>
                                                    <MaterialSelect id="frecuencia" v-model="form.frecuencia" :options="[
                                                        { value: 'diaria', text: 'Diaria' },
                                                        { value: 'semanal', text: 'Semanal' },
                                                        { value: 'mensual', text: 'Mensual' },
                                                        { value: 'trimestral', text: 'Trimestral' },
                                                        { value: 'semestral', text: 'Semestral' },
                                                        { value: 'anual', text: 'Anual' }
                                                    ]" />
                                                </div>

                                                <div class="form-group">
                                                    <label class="form-label fw-bold" for="fechaInicio">Fecha de
                                                        activación</label>
                                                    <input type="date" v-model="form.fechaInicio" id="fechaInicio"
                                                        class="form-control border px-3" required />
                                                </div>

                                                <!-- Próxima Fecha -->
                                                <div class="form-group">
                                                    <label class="form-label fw-bold" for="proxima_fecha">Fecha del
                                                        primer cargo</label>
                                                    <input type="text" :value="proximaFechaFormatted" id="proxima_fecha"
                                                        class="form-control border px-3" disabled />
                                                </div>

                                                <div class="form-group">
                                                    <label class="form-label fw-bold" for="fechaFin">Fecha de
                                                        finalización de la recurrencia
                                                        (opcional)</label>
                                                    <input type="date" v-model="form.fechaFin" id="fechaFin"
                                                        class="form-control border px-3" />
                                                </div>
                                            </div>

                                            <div class="mt-3">
                                                <MaterialButton type="submit" id="submitButton" class="btn w-100"
                                                    :is-disabled="form.processing">
                                                    <span v-if="form.processing">
                                                        <i class="spinner-border spinner-border-sm" role="status"
                                                            aria-hidden="true"></i>
                                                    </span>
                                                    Actualizar Recurrencia
                                                </MaterialButton>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </main>
        </div>
    </BootstrapModal>
</template>

<script>
import { useForm } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import MaterialSelect from '@/Components/MaterialSelect.vue';
import MaterialButton from '@/Components/MaterialButton.vue';

import BootstrapModal from '@/Components/BootstrapModal.vue';

export default {
    components: {
        MaterialSelect,
        MaterialButton,
        BootstrapModal
    },
    props: {
        facturas_recurrente: {
            type: [Object, null],
            required: true,
            default: () => ({}),
        },
        user: {
            type: Object,
            required: true,
        },
    },
    data() {
        return {
            form: useForm({
                frecuencia: '',
                fechaInicio: '',
                fechaFin: '',
                factura_id: this.facturas_recurrente.factura_id,
                proxima_fecha: '',
                ...this.facturas_recurrente, // si ya viene data
            }),
        };
    },

    methods: {
        updateProximaFecha() {
            if (!this.form.fechaInicio) return;

            const startDate = new Date(this.form.fechaInicio);
            let nextDate;

            switch (this.form.frecuencia) {
                case 'diaria':
                    nextDate = new Date(startDate.setDate(startDate.getDate() + 1));
                    break;
                case 'semanal':
                    nextDate = new Date(startDate.setDate(startDate.getDate() + 7));
                    break;
                case 'mensual':
                    nextDate = new Date(startDate.setMonth(startDate.getMonth() + 1));
                    break;
                case 'trimestral':
                    nextDate = new Date(startDate.setMonth(startDate.getMonth() + 3));
                    break;
                case 'semestral':
                    nextDate = new Date(startDate.setMonth(startDate.getMonth() + 6));
                    break;
                case 'anual':
                    nextDate = new Date(startDate.setFullYear(startDate.getFullYear() + 1));
                    break;
                default:
                    nextDate = startDate;
            }

            // Update proxima_fecha in the form  - FIX 
            this.form.proxima_fecha = nextDate.toISOString().split('T')[0]; // YYYY-MM-DD

        },
        submitForm() {

            Swal.fire({
                title: 'Cargando...',
                text: 'Estamos procesando tu solicitud, por favor espera.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            this.form.post(route('recurrentes.update', this.facturas_recurrente.id), {
                onSuccess: () => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Éxito',
                        text: '¡El formulario se envió con éxito!',
                        confirmButtonText: 'Aceptar',
                    });
                },
                onError: (error) => {
                    this.errors = error;

                    if (error) {
                        const errorMessages = Object.values(error).flat().join('<br>');
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            html: errorMessages,
                            confirmButtonText: 'Aceptar',
                        });
                    }
                },
            });
        },
    },
    computed: {
        proximaFechaFormatted() {
            if (!this.form.proxima_fecha) return '';
            const date = new Date(this.form.proxima_fecha);
            const day = String(date.getDate()).padStart(2, '0');
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const year = date.getFullYear();
            return `${day}/${month}/${year}`;
        }
    },
    watch: {
        facturas_recurrente: {
            handler(nuevaFactura) {
                if (nuevaFactura && Object.keys(nuevaFactura).length > 0) {
                    this.form.frecuencia = nuevaFactura.frecuencia || '';
                    this.form.fechaInicio = nuevaFactura.fechaInicio || '';
                    this.form.fechaFin = nuevaFactura.fechaFin || '';
                    this.form.proxima_fecha = nuevaFactura.proxima_fecha || '';
                    this.form.factura_id = nuevaFactura.factura_id || '';
                }
            },
            immediate: true
        },
        // Watch for changes in fechaInicio or frecuencia and update proxima_fecha
        'form.fechaInicio'(newVal) {
            this.updateProximaFecha();
        },
        'form.frecuencia'(newVal) {
            this.updateProximaFecha();
        }
    }

};
</script>

<style scoped>
/* Add your custom styles here */
</style>
