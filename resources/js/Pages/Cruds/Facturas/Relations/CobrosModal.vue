<template>
    <!-- Cobros Modal -->
    <BootstrapModal :-modal-id="'cobros_modal'" -modal-size="modal-xl"
        :title="selectedFactura ? 'Cobros ' + selectedFactura.numFactura : 'Cobros'">
        <div v-if="selectedFactura && (selectedFactura.estado === 0 || selectedFactura.estado === 5)">
            <MaterialButton href="#" data-bs-toggle="modal" :data-bs-target="'#add_cobro_modal'"
                class="d-flex align-items-center ms-auto">
                <i class="material-icons me-2">add</i> Agregar
            </MaterialButton>
        </div>

        <MaterialTable v-if="selectedFactura" title="Cobros" :key="selectedFactura.id">
            <thead>
                <tr>
                    <th class="text-uppercase text-xxs font-weight-bolder opacity-7">Fecha</th>
                    <th class="text-uppercase text-xxs font-weight-bolder opacity-7">Nº Factura</th>
                    <th class="text-uppercase text-xxs font-weight-bolder opacity-7">Observaciones</th>
                    <th class="text-uppercase text-xxs font-weight-bolder opacity-7">Total</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="cobro in selectedFactura.cobros" :key="cobro.id">
                    <td class="text-xs font-weight-bold">{{ formatDate(cobro.fecha_cobro) }}</td>
                    <td class="text-xs font-weight-bold">{{ selectedFactura?.numFactura }}</td>
                    <td class="text-xs font-weight-bold">{{ cobro.observaciones }}</td>
                    <td class="text-xs font-weight-bold">{{ formatCurrency(cobro.total) }}</td>
                </tr>
            </tbody>
            <tfoot v-if="selectedFactura">
                <tr>
                    <td colspan="3" class="text-right font-weight-bold">Total Cobrado:</td>
                    <td class="text-xs font-weight-bold">{{ formatCurrency(totalCobrado) }}</td>
                </tr>
                <tr>
                    <td colspan="3" class="text-right font-weight-bold">Total Pendiente:</td>
                    <td class="text-xs font-weight-bold">{{ formatCurrency(totalPendiente) }}</td>
                </tr>
            </tfoot>
        </MaterialTable>
    </BootstrapModal>

    <!-- Add Cobro Modal -->
    <BootstrapModal v-if="selectedFactura" :-modal-id="'add_cobro_modal'" -modal-size="modal-lg" title="Añadir Cobro">
        <form @submit.prevent="submit" v-if="selectedFactura">
            <p>Cobros Pendientes ({{ formatCurrency(totalPendiente) }})</p>

            <div class="form-group">
                <label for="fecha_cobro">Fecha</label>
                <input type="date" id="fecha_cobro" v-model="form.fecha_cobro" class="form-control border px-3"
                    required />
            </div>

            <div class="form-group">
                <label for="observaciones">Observaciones</label>
                <input type="text" id="observaciones" v-model="form.observaciones" class="form-control border px-3"
                    required />
            </div>

            <div class="form-group">
                <label for="total">Total</label>
                <input type="number" id="total" v-model.number="form.total" class="form-control border px-3" step="any"
                    :max="totalPendiente" :placeholder="'max ' + totalPendiente" required />
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
import MaterialTable from '@/Components/MaterialTable.vue';
import { useForm } from '@inertiajs/vue3';
import Swal from 'sweetalert2';

export default {
    components: {
        BootstrapModal,
        MaterialTable,
        MaterialButton
    },
    props: {
        factura: {
            type: Object,
            required: false,
            default: null
        }
    },
    data() {
        return {
            selectedFactura: null,
            form: useForm({
                fecha_cobro: '',
                factura_id: null,
                observaciones: '',
                total: ''
            })
        }
    },
    watch: {
        factura: {
            handler(newFactura) {
                this.selectedFactura = newFactura ? { ...newFactura } : null
                if (this.selectedFactura) this.initForm(this.selectedFactura)
            },
            immediate: true
        }
    },
    computed: {
        totalCobrado() {
            return Number(this.selectedFactura?.cobros?.reduce((sum, c) => sum + c.total, 0).toFixed(2)) || 0;
        },
        totalPendiente() {
            if (!this.selectedFactura) return 0;
            return Number((this.selectedFactura.total - this.totalCobrado).toFixed(2));
        }
    },
    methods: {
        initForm(factura) {
            if (!factura) {
                this.form.reset();
                this.form.factura_id = null;
                return;
            }

            const today = new Date();
            const yyyy = today.getFullYear();
            const mm = String(today.getMonth() + 1).padStart(2, '0');
            const dd = String(today.getDate()).padStart(2, '0');

            this.form.fecha_cobro = `${yyyy}-${mm}-${dd}`;
            this.form.factura_id = factura.id;
            this.form.observaciones = '';
            this.form.total = this.totalPendiente;
        },
        formatCurrency(amount) {
            return new Intl.NumberFormat('es-ES', {
                style: 'currency',
                currency: 'EUR',
                minimumFractionDigits: 2
            }).format(amount || 0);
        },
        formatDate(dateString) {
            if (!dateString) return '-';
            return new Date(dateString).toLocaleDateString('es-ES', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
        },
        submit() {
            if (!this.selectedFactura) return;

            if (this.form.total > this.totalPendiente) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Importe no válido',
                    text: `El importe no puede ser mayor al pendiente (${this.formatCurrency(this.totalPendiente)}).`
                });
                return;
            }

            Swal.fire({
                title: '¿Confirmar cobro?',
                text: `Vas a registrar un cobro de ${this.formatCurrency(this.form.total)}.`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, guardar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (!result.isConfirmed) return;

                Swal.fire({
                    title: 'Cargando...',
                    text: 'Estamos procesando tu solicitud, por favor espera.',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                this.form.post(route('cobro.store', this.selectedFactura.id), {
                    onSuccess: () => {
                        Swal.fire({
                            icon: 'success',
                            title: 'Éxito',
                            text: '¡El cobro se registró con éxito!',
                            confirmButtonText: 'Aceptar'
                        }).then(() => {
                            location.reload();
                        });
                    },
                    onError: () => {
                        let message =
                            'Hubo un error en tu envío. Por favor revisa tus datos e inténtalo nuevamente.';
                        if (this.form.errors && Object.keys(this.form.errors).length > 0) {
                            message = Object.values(this.form.errors)[0];
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: message
                        });
                    }
                });
            });
        }
    }
};
</script>
