<template>
    <BootstrapModal -modal-id="fraccion_modal" -modal-size="modal-lg">
        <form @submit.prevent="submitForm" class="">

            <p class="alert alert-primary">
                Se ha pagado <strong>{{ totalFacturas }}</strong> €
                ({{ calculatePercentage(totalFacturas, presupuesto.total) }}%)
                Quedan <strong>{{ remainingAmount }}</strong> €
            </p>

            <div class="modal-body">
                <div class="row clase">
                    <div class="col-auto me-auto">
                        {{ presupuesto.cliente_nombre }}<br />
                        {{ presupuesto.numPresupuesto }}
                    </div>
                    <div class="col-auto">
                        Total {{ formattedTotal }}€
                    </div>
                </div>

                <div class="row g-3">
                    <!-- Invoice Number -->
                    <div class="col-12 form-group">
                        <MaterialInput variant="static" type="text" name="numFactura" id="numFactura" label="Nº Factura"
                            v-model="form.numFactura" :is-required="true" :readonly="true" />
                    </div>

                    <div class="col-6">
                        <MaterialInput variant="static" type="date" label="Fecha Inicio" name="fechaInicio"
                            id="fechaInicio" v-model="form.fechaInicio" :is-required="true" />
                    </div>

                    <div class="col-6">
                        <MaterialInput variant="static" type="date" label="Vencimiento" name="fechaFin" id="fechaFin"
                            v-model="form.fechaFin" :readonly="true" />
                    </div>

                    <!-- Time Select -->
                    <div class="col-lg-6 form-group">
                        <label for="tiempo" class="fw-bold">Vencimiento:</label>
                        <MaterialSelect :is-required="true" v-model="form.tiempo" id="tiempo" :options="[
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

                    <div class="col-12">
                        <MaterialInput variant="static" type="text" label="Descripción" name="descripcion"
                            id="descripcion" v-model="form.concepto" placeholder="Descripción" :is-required="true" />
                    </div>
                    <div style="margin-bottom: -30px;">Seleccione el % o el importe a fraccionar:</div>

                    <div class="col">
                        <MaterialInput variant="static" type="number" step="any" label="Porcentaje [%]"
                            name="porcentaje" id="porcentaje" v-model="form.porcentaje" @input="updateTotalFraccionado"
                            :placeholder="`Max ${remainingPercentage.toFixed(2)}%`" :is-required="true" :min="0"
                            :max="100" />
                    </div>

                    <div class="col">
                        <MaterialInput variant="static" type="number" step="any" label="Total" name="total"
                            id="totalFraccionado" v-model="form.total" @input="updatePorcentaje"
                            :placeholder="`Max ${remainingAmount.toFixed(2)} €`" :min="0"
                            :max="remainingAmount.toFixed(2)" />
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
    </BootstrapModal>
</template>

<script>
import BootstrapModal from '@/Components/BootstrapModal.vue';
import MaterialButton from '@/Components/MaterialButton.vue';
import MaterialInput from '@/Components/MaterialInput.vue';
import MaterialSelect from '@/Components/MaterialSelect.vue';
import { useForm } from '@inertiajs/vue3';
import Swal from 'sweetalert2';

export default {
    components: {
        BootstrapModal,
        MaterialInput,
        MaterialButton,
        MaterialSelect
    },
    props: {
        presupuesto: {
            type: Object,
            required: true
        },
    },
    data() {
        return {
            form: useForm({
                cliente_id: this.presupuesto.cliente_id,
                presupuesto_id: this.presupuesto.id,
                iva: this.presupuesto.iva,
                irpf: this.presupuesto.irpf,
                metodo_pago: this.presupuesto.metodo_pago,
                num_cuenta: this.presupuesto.num_cuenta,
                serie: 1,
                numFactura: '',
                fechaInicio: '',
                fechaFin: '',
                tiempo: '1',
                total_sin_iva: this.presupuesto.total_sin_iva,
                total_iva: this.presupuesto.total_iva,
                total_irpf: this.presupuesto.total_irpf,
                total: '0.00',
                observaciones: '',
                condiciones: '',
                concepto: '',
                porcentaje: '0.00',
                servicios: this.presupuesto.servicios,
                productos: this.presupuesto.productos
            }),
            metodosPago: [
                "Transferencia Bancaria",
                "Giro Bancario",
                "Efectivo",
                "Confirming"
            ],
            vencimientoOptions: [1, 30, 60, 90, 120, 150, 180, 210]
        };
    },
    mounted() {
        this.generateFacturaNumber(this.form.serie);
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
        remainingAmount() {
            return this.presupuesto.total - this.totalFacturas;
        },
        remainingPercentage() {
            return this.presupuesto.total
                ? 100 - (this.totalFacturas / this.presupuesto.total) * 100
                : 0;
        },
        formattedTotal() {
            return this.presupuesto.total.toLocaleString("es-ES", {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        },
        totalFacturas() {
            return this.presupuesto.facturas.reduce((sum, factura) => sum + factura.total, 0);
        }
    },
    watch: {
        computedFechaFin(newFechaFin) {
            this.form.fechaFin = newFechaFin;
        },
    },
    methods: {
        async generateFacturaNumber(serie) {
            try {
                const response = await axios.get(`/generate/${serie}/facturas_number`);
                this.form.numFactura = response.data;
                // Handle the response as needed
            } catch (error) {
                console.error('Error generating factura number:', error);
            }
        },
        calculatePercentage(part, total) {
            return total ? ((part / total) * 100).toFixed(2) : 0;
        },
        updateTotalFraccionado() {
            if (this.form.porcentaje) {
                const percentage = parseFloat(this.form.porcentaje) / 100;
                const total = this.presupuesto.total * percentage;
                this.form.total = total.toFixed(2);
                this.form.total_sin_iva = (total / (1 + this.presupuesto.iva / 100)).toFixed(2);
                this.form.total_iva = (total - this.form.total_sin_iva).toFixed(2);
                this.form.total_irpf = (this.form.total_sin_iva * (this.presupuesto.irpf / 100)).toFixed(2);
            }
        },
        updatePorcentaje() {
            if (this.form.total) {
                const total = parseFloat(this.form.total);
                this.form.porcentaje = ((total / this.presupuesto.total) * 100).toFixed(2);
                this.form.total_sin_iva = (total / (1 + this.presupuesto.iva / 100)).toFixed(2);
                this.form.total_iva = (total - this.form.total_sin_iva).toFixed(2);
                this.form.total_irpf = (this.form.total_sin_iva * (this.presupuesto.irpf / 100)).toFixed(2);
            }
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
            // Post form data to the backend
            this.form.post(route('facturas.store'), {
                onSuccess: () => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Éxito',
                        text: '¡El formulario se envió con éxito!',
                        confirmButtonText: 'Aceptar',
                    }).then(() => {
                        // Optional: Reload the page or reset the form
                        this.$emit('close-modal'); // Close modal if using parent modal control
                    });
                },
                onError: (error) => {
                    // Assign errors to display them in the form if needed
                    this.errors = error;

                    // Display detailed error messages if available
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
                onFinish: () => {
                    // Stop loading animation after completion
                    location.reload();
                }
            });
        }
    }

};
</script>
