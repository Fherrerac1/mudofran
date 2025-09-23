<template>
    <AuthenticatedLayout :user="user" :title="'Duplicar Presupuesto'">
        <div class="container-fluid overflow-hidden">
            <div class="row" id="nav">
                <a class="d-flex text-decoration-none fs-5" href="javascript:history.go(-1);" role="button">
                    <i class="material-icons pe-2 pt-1">
                        <span class="material-symbols-outlined">arrow_back</span>
                    </i>Volver
                </a>
            </div>

            <div class="rounded p-0 mx-0 mb-5 shadow" style="position: relative">
                <form @submit.prevent="submitForm" class="">
                    <div v-if="errors.length" class="alert alert-danger">
                        <ul>
                            <li v-for="(error, index) in errors" :key="index">{{ error }}</li>
                        </ul>
                    </div>

                    <div class="row">
                        <div class="row col-lg-6">
                            <h2>Datos del Cliente</h2>

                            <!-- Cliente Select -->
                            <div class="form-group col-lg-6" v-if="tipo_contacto === 'cliente'">
                                <label for="clientes" class="fw-bold">Seleccionar Cliente:</label>
                                <MaterialSelect name="cliente_id" id="clientes" v-model="form.cliente_id"
                                    placeholder="Selecciona un Cliente"
                                    :options="clientes.map(cliente => ({ value: cliente.id, text: `${cliente.nombre} ${cliente.apellido_1 ?? ''}` }))" />
                            </div>

                            <div class="form-group col-lg-6" v-if="tipo_contacto === 'contacto'">
                                <label for="contactos" class="fw-bold">Seleccionar Contacto:</label>
                                <MaterialSelect name="contacto_id" id="contactos" v-model="form.contacto_id"
                                    placeholder="Selecciona un contacto"
                                    :options="contactos.map(contacto => ({ value: contacto.id, text: `${contacto.nombre} ${contacto.apellido_1 ?? ''}` }))" />
                            </div>

                            <div class="row col-lg-6">
                                <label class="form-label">Tipo de contacto</label>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" id="tipoCliente" value="cliente"
                                        v-model="tipo_contacto">
                                    <label class="form-check-label" for="tipoCliente">Cliente</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" id="tipoContacto" value="contacto"
                                        v-model="tipo_contacto">
                                    <label class="form-check-label" for="tipoContacto">Contacto</label>
                                </div>
                            </div>

                            <DatosCliente :form="form" :clientes="clientes" />

                            <!-- Método de Pago Select -->
                            <div class="col-6 form-group">
                                <label class="pe-2" for="metodo_pago">Método de pago:</label>
                                <MaterialSelect id="metodo" v-model="form.metodo_pago" :options="[
                                    { value: '0', text: 'Transferencia Bancaria' },
                                    { value: '1', text: 'Giro Bancario' },
                                    { value: '2', text: 'Efectivo' },
                                    { value: '3', text: 'Confirming' }
                                ]" />
                            </div>

                            <!-- Número de Cuenta Select -->
                            <div v-if="showNumCuenta" class="col-6 form-group num_cuenta_field">
                                <label class="pe-2" for="num_cuenta">Nº Cuenta:</label>
                                <MaterialSelect name="num_cuenta" id="num_cuenta" v-model="form.num_cuenta"
                                    :options="metodos.map(metodo => ({ value: metodo.id, text: metodo.concepto }))"
                                    :is-required="showNumCuenta" />
                            </div>

                        </div>

                        <div class="row col-lg-6">
                            <h2>Datos del Presupuesto</h2>
                            <!-- Invoice Number -->
                            <div class="col-12 form-group">
                                <MaterialInput type="text" name="numPresupuesto" id="numPresupuesto"
                                    label="Nº Presupuesto" v-model="form.numPresupuesto" :is-required="true"
                                    :readonly="true" />
                            </div>

                            <!-- Issue Date -->
                            <div class="col-lg-6 form-group">
                                <MaterialInput type="date" name="fechaInicio" id="fechaInicio"
                                    label="Fecha de expedición" v-model="form.fechaInicio" :is-required="true"
                                    variant="static" />
                            </div>

                            <!-- Expiry Date -->
                            <div class="col-lg-6 form-group">
                                <MaterialInput type="date" name="fechaFin" id="fechaFin" label="Fecha de vencimiento"
                                    v-model="form.fechaFin" variant="static" />
                            </div>

                            <!-- IVA Select -->
                            <div class="col-6 form-group">
                                <label for="iva" class="fw-bold">IVA:</label>
                                <MaterialSelect name="iva" id="iva" v-model="form.iva" :is-required="true" :options="[
                                    { value: '21', text: '21%' },
                                    { value: '15', text: '15%' },
                                    { value: '10', text: '10%' },
                                    { value: '4', text: '4%' },
                                    { value: '0', text: '0%' }
                                ]" />
                            </div>

                            <!-- IRPF Select -->
                            <div class="col-6 form-group">
                                <label for="irpf" class="fw-bold">IRPF:</label>
                                <MaterialSelect name="irpf" id="irpf" v-model="form.irpf" :is-required="true" :options="[
                                    { value: '0', text: '0%' },
                                    { value: '15', text: '15%' }
                                ]" />
                            </div>

                        </div>
                    </div>

                    <hr class="my-4" />

                    <div class="row">
                        <h3>Contenido</h3>
                        <generarInputs :form="form" @update-content="updateContent"></generarInputs>
                    </div>

                    <hr class="my-4" />

                    <div class="row">
                        <h2>Datos Adicionales</h2>
                        <div>
                            <div class="form-group">
                                <label for="observaciones" class="fw-bold">Observaciones</label>
                                <MaterialTextarea name="observaciones" id="observaciones" v-model="form.observaciones"
                                    :max-length="400" />
                            </div>
                            <div class="form-group">
                                <label for="condiciones" class="fw-bold">Pliegos y condiciones</label>
                                <MaterialTextarea name="condiciones" id="condiciones" v-model="form.condiciones"
                                    :max-length="400" />
                            </div>
                        </div>
                    </div>

                    <hr>
                    <!-- Section: Document Upload -->
                    <div class="mb-4">
                        <h4>Documentos Adjuntos</h4>
                        <div class="form-group">
                            <MultiFileInputs v-model:model-value="form.fotos" id="documents" ref="documentInput"
                                multiple label="Adjunte cualquier documento relevante para su inscripción." />
                        </div>
                    </div>

                    <!-- Presupuesto Anexo -->
                    <div class="form-group col-lg-12">
                        <label for="anexo" class="fw-bold">Seleccionar Presupuesto:(Anexo)</label>
                        <MaterialSelect placeholder="Selecciona Presupuesto:(Anexo)" name="presupuesto_anexo" id="anexo"
                            v-model="form.presupuesto_anexo"
                            :options="presupuestos.map(presupuesto => ({ value: presupuesto.id, text: presupuesto.numPresupuesto }))" />
                    </div>

                    <MaterialButton type="submit" id="submitButton" class="mt-3" :is-disabled="form.processing">
                        <span v-if="form.processing">
                            <i class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></i>
                        </span>
                        Guardar
                    </MaterialButton>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script>
import { useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Swal from 'sweetalert2';
import generarInputs from '../Compras/generarInputs.vue';
import MaterialSelect from '@/Components/MaterialSelect.vue';
import MaterialInput from '@/Components/MaterialInput.vue';
import MaterialTextarea from '@/Components/MaterialTextarea.vue';
import MaterialButton from '@/Components/MaterialButton.vue';
import MultiFileInputs from '@/Components/MultiFileInputs.vue';
import DatosCliente from './DatosCliente.vue';

export default {
    components: {
        AuthenticatedLayout,
        MaterialSelect,
        generarInputs,
        MaterialTextarea,
        MaterialButton,
        MaterialInput,
        MultiFileInputs,
        DatosCliente
    },
    props: {
        user: {
            type: Object,
            required: true,
        },
        clientes: {
            type: Array,
            required: true,
        },
        contactos: {
            type: Array,
            required: true,
        },
        numPresupuesto: {
            type: String,
            required: true,
        },
        metodos: {
            type: Array,
            required: true,
        },
        presupuestos: {
            type: Array,
            required: false,
        },
        presupuesto: {
            type: Object,
            required: false,
        },
    },
    data() {
        return {
            tipo_contacto: this.presupuesto.cliente_id ? 'cliente' : 'contacto',
            form: useForm(this.presupuesto),
            errors: [],
            showNumCuenta: false,
        };
    },
    watch: {
        computedFechaFin(newFechaFin) {
            this.form.fechaFin = newFechaFin;
        },
        'form.metodo_pago': 'toggleNumCuentaVisibility',
        'form.fechaInicio': 'toggleNumCuentaVisibility',
        'form.tiempo': 'toggleNumCuentaVisibility',
    },
    methods: {
        async generatePresupuestoNumber() {
            try {
                const response = await axios.get(`/generate/presupuesto_number`);
                this.form.numPresupuesto = response.data;
                // Handle the response as needed
            } catch (error) {
                console.error('Error generating presupesto number:', error);
            }
        },
        toggleNumCuentaVisibility() {
            this.showNumCuenta = this.form.metodo_pago === '0'; // Show field only if Transferencia Bancaria
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

            this.form.post(route('presupuestos.store'), {
                onSuccess: () => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Éxito',
                        text: '¡El formulario se envió correctamente!',
                        confirmButtonText: 'OK',
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
                            confirmButtonText: 'OK',
                        });
                    }
                },
            });
        },
        updateContent(contentType, content) {
            if (contentType === 'servicios') {
                this.form.servicios = content;
            } else if (contentType === 'productos') {
                this.form.productos = content;
            }
        },
    },
    mounted() {
        this.toggleNumCuentaVisibility();
        this.form.numPresupuesto = this.numPresupuesto;
    },
};
</script>
