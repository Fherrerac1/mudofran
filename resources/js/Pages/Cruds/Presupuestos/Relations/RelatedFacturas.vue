<template>
    <!-- Facturas Modal -->
    <BootstrapModal -modal-id="facturas_modal" -modal-size="modal-xl" title="Facturas">
        <div class="d-flex justify-content-between">
            <a :href="route('facturas.generate', presupuesto.id)" v-if="presupuesto.estado == 2">
                <MaterialButton>
                    Generar factura
                </MaterialButton>
            </a>

            <a role="button" v-if="presupuesto.estado == 2 || presupuesto.estado == 3" data-bs-toggle="modal"
                data-bs-target="#fraccion_modal">
                <MaterialButton>
                    Fraccionar
                </MaterialButton>
            </a>
        </div>
        <MaterialTable title="Facturas">
            <thead>
                <tr>
                    <th class="text-uppercase text-xxs font-weight-bolder opacity-7">Fecha</th>
                    <th class="text-uppercase text-xxs font-weight-bolder opacity-7">NºFactura</th>
                    <th class="text-uppercase text-xxs font-weight-bolder opacity-7">Estado</th>
                    <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-center">Total</th>
                    <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="factura in presupuesto.facturas" :key="factura.id" role="button">
                    <td class="text-xs font-weight-bold">{{ formatDate(factura.fechaInicio) }}</td>
                    <td class="text-xs font-weight-bold">{{ factura.numFactura }}</td>
                    <td class="text-xs font-weight-bold"><strong>{{ estadoText(factura.estado) }}</strong></td>
                    <td class="text-xs font-weight-bold text-center">{{ formatCurrency(factura.total) }}</td>

                    <td class="text-xs font-weight-bold text-center">
                        <a :href="'/facturas/' + factura.id + '/mostrar'"><i class="material-icons">visibility</i></a>
                    </td>
                </tr>
            </tbody>
        </MaterialTable>
    </BootstrapModal>

    <FractionModal :presupuesto="presupuesto" />
</template>

<script>
import BootstrapModal from '@/Components/BootstrapModal.vue';
import MaterialButton from '@/Components/MaterialButton.vue';
import MaterialTable from '@/Components/MaterialTable.vue';
import FractionModal from './FractionModal.vue';

export default {
    components: {
        BootstrapModal,
        MaterialTable,
        MaterialButton,
        FractionModal
    },
    props: {
        presupuesto: Object,
    },
    methods: {
        formatDate(dateString) {
            const options = { year: 'numeric', month: 'long', day: 'numeric' };
            return new Date(dateString).toLocaleDateString('es-ES', options);
        },
        formatCurrency(amount) {
            if (isNaN(amount)) return ''; // Handle non-numeric input

            // Format the number with grouping for thousands, and replace the dot with a comma for decimals
            return amount
                .toFixed(2)  // Ensure 2 decimal places
                .replace('.', ',')  // Replace dot with comma for decimal separator
                .replace(/\B(?=(\d{3})+(?!\d))/g, '.') // Add dots for thousands separators
                + ' €';  // Add Euro symbol at the end
        }, estadoText(estado) {
            switch (estado) {
                case 0:
                    return 'Pendiente';
                case 1:
                    return 'Rechazada';
                case 2:
                    return 'Pagado';
                case 3:
                    return 'Rectificada';
                case 4:
                    return 'Aceptado';

                default:
                    return 'Estado desconocido';
            }
        },
    }
};
</script>