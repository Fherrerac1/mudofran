<script>

import BootstrapModal from "@/Components/BootstrapModal.vue";
import MaterialButton from "@/Components/MaterialButton.vue";
import MaterialTable from "@/Components/MaterialTable.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

export default {

    components: {
        MaterialButton,
        AuthenticatedLayout,
        MaterialTable,
        BootstrapModal
    },
    props: {
        facturas: {
            type: Array,
            required: true
        },
    },

    data() {
        return {
        };
    },

    computed: {

    },
    methods: {
        formatDate(dateString) {
            const options = { year: 'numeric', month: 'long', day: 'numeric' };
            return new Date(dateString).toLocaleDateString('es-ES', options);
        },
        formatCurrency(amount) {
            return new Intl.NumberFormat('es-ES', {
                style: 'currency',
                currency: 'EUR',
                minimumFractionDigits: 2
            }).format(amount);
        },
    }
}
</script>

<template>
    <BootstrapModal -modal-id="facturasModal" title="facturas" -modal-size="modal-lg">
        <MaterialTable title="Facturas">
            <thead>
                <tr>
                    <th class="text-uppercase text-xxs font-weight-bolder opacity-7">Fecha</th>
                    <th class="text-uppercase text-xxs font-weight-bolder opacity-7">NºFactura</th>
                    <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-center">Estado</th>
                    <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-center">Total sin iva</th>
                    <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="factura in facturas" :key="factura.id" role="button">
                    <td class="text-xs font-weight-bold">{{ formatDate(factura.fechaInicio) }}</td>
                    <td class="text-xs font-weight-bold">{{ factura.numFactura }}</td>
                    <td class="text-xs font-weight-bold text-center"><strong>{{ factura.estado_text }}</strong></td>
                    <td class="text-xs font-weight-bold text-center">{{ formatCurrency(factura.total_sin_iva) }}</td>

                    <td class="text-xs font-weight-bold text-center">
                        <a :href="route('facturas.show', factura.id)"><i class="material-icons">visibility</i></a>
                    </td>
                </tr>
            </tbody>
        </MaterialTable>
    </BootstrapModal>
</template>
