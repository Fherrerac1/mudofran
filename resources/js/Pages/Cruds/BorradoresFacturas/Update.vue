<template>
    <AuthenticatedLayout :user="user" :title="'Editar Factura'">
        <div class="container-fluid overflow-hidden">
            <div class="row" id="nav">
                <a class="d-flex text-decoration-none fs-5" href="javascript:history.go(-1);" role="button">
                    <i class="material-icons pe-2 pt-1">
                        <span class="material-symbols-outlined">arrow_back</span>
                    </i>Volver
                </a>
            </div>

            <div class="rounded p-0 mx-0 mb-5 shadow" style="position: relative">
                <form @submit.prevent="submitForm">
                    <div v-if="errors.length" class="alert alert-danger">
                        <ul>
                            <li v-for="(error, index) in errors" :key="index">{{ error }}</li>
                        </ul>
                    </div>

                    <div class="row">
                        <div class="row col-lg-6">
                            <h2>Datos del Cliente</h2>

                            <!-- Cliente Select -->
                            <div class="form-group col-lg-6">
                                <label for="clientes" class="fw-bold">Seleccionar Cliente:</label>
                                <MaterialSelect name="cliente_id" id="clientes" v-model="form.cliente_id"
                                    placeholder="Selecciona un Cliente"
                                    :options="clientes.map(cliente => ({ value: cliente.id, text: `${cliente.nombre} ${cliente.apellido_1 ?? ''}` }))" />
                            </div>

                            <DatosCliente :form="form" :clientes="clientes" />

                            <!-- Presupuesto Select -->
                            <div class="form-group col-lg-6">
                                <div class="form-group col-12">
                                    <label for="presupuestos" class="fw-bold">Seleccionar Presupuesto:</label>
                                    <MaterialSelect name="presupuesto_id" placeholder="Sin presupuesto"
                                        id="presupuestos" v-model="form.presupuesto_id"
                                        :options="presupuestos.map(presupuesto => ({ value: presupuesto.id, text: presupuesto.numPresupuesto }))" />
                                </div>
                                <span v-if="factura.presupuesto_id">porcentage : {{ factura.porcentaje }}%</span>
                            </div>

                            <!-- Método de Pago Select -->
                            <div class="col-lg-6 form-group">
                                <label class="pe-2" for="metodo_pago">Método de pago:</label>
                                <MaterialSelect id="metodo" v-model="form.metodo_pago" :options="[
                                    { value: '0', text: 'Transferencia Bancaria' },
                                    { value: '1', text: 'Giro Bancario' },
                                    { value: '2', text: 'Efectivo' },
                                    { value: '3', text: 'Confirming' }
                                ]" />
                            </div>

                            <!-- Número de Cuenta Select -->
                            <div v-if="showNumCuenta" class="col-lg-6 form-group num_cuenta_field">
                                <label class="pe-2" for="num_cuenta">Nº Cuenta:</label>
                                <MaterialSelect name="num_cuenta" id="num_cuenta" v-model="form.num_cuenta"
                                    :options="metodos.map(metodo => ({ value: metodo.id, text: metodo.concepto }))"
                                    :is-required="showNumCuenta" />
                            </div>

                        </div>

                        <div class="row col-lg-6">
                            <h2>Datos del Borrador</h2>
                            <!-- Invoice Number -->
                            <div class="col-12 form-group">
                                <MaterialInput type="text" name="numFactura" id="numFactura" label="Nº Factura"
                                    v-model="form.numFactura" :is-required="true" :readonly="true" />
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
                                    v-model="form.fechaFin" :readonly="true" variant="static" />
                            </div>

                            <!-- Time Select -->
                            <div class="col-lg-6 form-group">
                                <label for="tiempo" class="fw-bold">Vencimiento:</label>
                                <MaterialSelect v-model="form.tiempo" id="tiempo" :options="[
                                    { value: '1', text: '1 Día' },
                                    { value: '30', text: '30 Días' },
                                    { value: '60', text: '60 Días' },
                                    { value: '90', text: '90 Días' },
                                    { value: '120', text: '120 Días' },
                                    { value: '150', text: '150 Días' },
                                    { value: '180', text: '180 Días' },
                                    { value: '210', text: '210 Días' }
                                ]" />
                            </div>

                            <!-- IVA Select -->
                            <div class="col-lg-6 form-group">
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
                            <div class="col-lg-6 form-group">
                                <label for="irpf" class="fw-bold">IRPF:</label>
                                <MaterialSelect name="irpf" id="irpf" v-model="form.irpf" :is-required="true" :options="[
                                    { value: '0', text: '0%' },
                                    { value: '15', text: '15%' }
                                ]" />
                            </div>

                            <!-- retention Select -->
                            <div class="col-lg-6 form-group">
                                <label for="retention" class="fw-bold">Retencion:</label>
                                <MaterialSelect name="retention" id="retention" v-model="form.retencion"
                                    :is-required="true" :options="[
                                        { value: '0', text: '0%' },
                                        { value: '5', text: '5%' }
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
                            <div class="form-group">
                                <label for="concepto" class="fw-bold">Concepto</label>
                                <MaterialTextarea name="concepto" id="concepto" v-model="form.concepto"
                                    :max-length="300" />
                            </div>
                        </div>
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
import 'jquery-ui/ui/widgets/autocomplete';
import 'jquery-ui/themes/base/all.css'; // You can replace `all.css` with a specific theme
import ClientesCompleter from '../Presupuestos/ClientesCompleter.vue';
import MultiFileInputs from '@/Components/MultiFileInputs.vue';
import DatosCliente from '../Presupuestos/DatosCliente.vue';

export default {
    components: {
        AuthenticatedLayout,
        MaterialSelect,
        generarInputs,
        MaterialTextarea,
        MaterialButton,
        MaterialInput,
        ClientesCompleter,
        MultiFileInputs,
        DatosCliente
    },
    props: {
        user: {
            type: Object,
            required: true,
        },
        factura: {
            type: Object,
            required: true,
        },
        clientes: {
            type: Array,
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
    },
    data() {
        return {
            form: useForm(this.factura),
            errors: [],
            showNumCuenta: false,
        };
    },
    computed: {
        computedFechaFin() {
            if (this.form.fechaInicio && this.form.tiempo) {
                const fechaInicioDate = new Date(this.form.fechaInicio);
                fechaInicioDate.setDate(fechaInicioDate.getDate() + parseInt(this.form.tiempo));
                return fechaInicioDate.toISOString().split('T')[0];
            }
            return '';
        },
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
            this.form.post(route('facturas.update', this.factura.id), {
                onSuccess: () => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: 'The form was submitted successfully!',
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
        searchClientes(query) {
            return this.clientes
                .filter((cliente) => cliente.nombre.toLowerCase().includes(query.toLowerCase()))
                .map((cliente) => cliente.nombre); // Only return the 'nombre' property
        },
        updateFrom(updatedData) {
            this.form.cliente_nombre = updatedData.cliente_nombre || this.form.cliente_nombre;
            this.form.dni_cliente = updatedData.dni_cliente || this.form.dni_cliente;
            this.form.pais_cliente = updatedData.pais_cliente || this.form.pais_cliente;
            this.form.email_cliente = updatedData.email_cliente || this.form.email_cliente;
            this.form.phone_cliente = updatedData.phone_cliente || this.form.phone_cliente;
            this.form.cp_cliente = updatedData.cp_cliente || this.form.cp_cliente;
            this.form.direccion_cliente = updatedData.direccion_cliente || this.form.direccion_cliente;
        },
        handleSerieChange(newValue) {
            this.form.serie = newValue;

            // Example: Trigger a backend API call based on the selected serie
            this.generateFacturaNumber(this.form.serie);
        },
        async generateFacturaNumber(serie) {
            try {
                const response = await axios.get(`/generate/${serie}/facturas_number`);
                this.form.numFactura = response.data;
                // Handle the response as needed
            } catch (error) {
                console.error('Error generating factura number:', error);
            }
        },
    },
    mounted() {
        this.toggleNumCuentaVisibility();
    },
};
</script>
