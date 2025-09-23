<script>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import MaterialButton from '@/Components/MaterialButton.vue';
import { ref } from 'vue';

export default {
    components: {
        AuthenticatedLayout,
        MaterialButton,
    },
    props: {
        nomina: Object,
        user: Object,
    },
    setup(props) {
        // Format date helper
        const formatDate = (dateString) => {
            const options = { year: 'numeric', month: 'long', day: 'numeric' };
            return new Date(dateString).toLocaleDateString('es-ES', options);
        };

        // Convert month number to text
        const mesTexto = (mes) => {
            const nombres = {
                '01': 'Enero',
                '02': 'Febrero',
                '03': 'Marzo',
                '04': 'Abril',
                '05': 'Mayo',
                '06': 'Junio',
                '07': 'Julio',
                '08': 'Agosto',
                '09': 'Septiembre',
                '10': 'Octubre',
                '11': 'Noviembre',
                '12': 'Diciembre',
            };
            return nombres[mes] || mes;
        };

        return { formatDate, mesTexto };
    }
};
</script>

<template>
    <AuthenticatedLayout :user="user" title="Detalle de Nómina">
        <div class="container py-4">
            <button class="btn btn-link mb-3" @click="$router.back()">
                <span class="material-icons align-middle me-1">arrow_back</span>
                Volver
            </button>

            <div class="card p-4 shadow-sm">
                <p><strong>Fecha creación:</strong> {{ formatDate(nomina.created_at) }}</p>
                <p><strong>Periodo:</strong> {{ mesTexto(nomina.mes) }} {{ nomina.anio }}</p>
                <p><strong>Usuario:</strong> {{ nomina.user?.name || 'Sin nombre' }}</p>
                <p><strong>DNI:</strong> {{ nomina.user?.dni || 'No disponible' }}</p>

                <div class="mt-4">
                    <a :href="`/storage/${nomina.archivo}`" target="_blank" class="btn btn-primary" rel="noopener">
                        <span class="material-icons me-2">visibility</span>
                        Ver/Descargar Nómina
                    </a>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

