<template>
    <AuthenticatedLayout :user="user" :title="'Facturas Recurrentes'">
        <div class="container-fluid overflow-hidden">
            <div class="row" id="nav">
                <a class="d-flex text-decoration-none fs-5" href="javascript:history.go(-1);" role="button">
                    <i class="material-icons pe-2 pt-1">
                        <span class="material-symbols-outlined">arrow_back</span>
                    </i>
                    Volver
                </a>
            </div>

            <main class="mt-0 main-content">
                <section>
                    <div class="container mb-4 p-0">
                        <div class="row justify-content-center">
                            <div class="col mx-auto">
                                <div class="card mt-3">
                                    <div class="card-header p-0 position-relative mx-3">
                                        <div class="style_color forms shadow-warning border-radius-lg py-3 text-center">
                                            <h3 class="font-weight-bolder text-white">Activar Recurrencia para Factura
                                                {{ facturas_recurrente.factura?.numFactura }}</h3>
                                        </div>
                                    </div>
                                    <div class="py-4 card-body">
                                        <form @submit.prevent="submitForm">

                                            <div class="row g-3">
                                                <div class="form-group">
                                                    <label class="form-label" for="frecuencia">Frecuencia de
                                                        Recurrencia</label>
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
                                                    <label class="form-label" for="fechaInicio">Fecha de Inicio</label>
                                                    <input type="date" v-model="form.fechaInicio" id="fechaInicio"
                                                        class="form-control border px-3" required readonly />
                                                </div>

                                                <!-- Próxima Fecha -->
                                                <div class="form-group">
                                                    <label class="form-label" for="proxima_fecha">Próxima Fecha</label>
                                                    <input type="text" v-model="form.proxima_fecha" id="proxima_fecha"
                                                        class="form-control border px-3" disabled />
                                                </div>

                                                <div class="form-group">
                                                    <label class="form-label" for="fechaFin">Fecha de Fin
                                                        (opcional)</label>
                                                    <input type="date" v-model="form.fechaFin" id="fechaFin"
                                                        class="form-control border px-3" />
                                                </div>
                                            </div>

                                            <div class="mt-3">
                                                <MaterialButton type="submit" id="submitButton" class="btn btn-primary"
                                                    :is-disabled="form.processing">
                                                    <span v-if="form.processing">
                                                        <i class="spinner-border spinner-border-sm" role="status"
                                                            aria-hidden="true"></i>
                                                    </span>
                                                    Guardar Recurrencia
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

    </AuthenticatedLayout>
</template>

<script>
import { useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Swal from 'sweetalert2';
import MaterialSelect from '@/Components/MaterialSelect.vue';
import MaterialButton from '@/Components/MaterialButton.vue';

export default {
    components: {
        AuthenticatedLayout,
        MaterialSelect,
        MaterialButton
    },
    props: {
        facturas_recurrente: {
            type: Object,
            required: true,
        },
        user: {
            type: Object,
            required: true,
        },
    },
    data() {
        return {
            form: useForm(this.facturas_recurrente),
            successMessage: '',
            errorMessage: '',
        };
    },
    methods: {
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
};
</script>

<style scoped>
/* Add your custom styles here */
</style>
