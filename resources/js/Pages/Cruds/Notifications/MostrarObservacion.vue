<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue';

import { defineProps, ref } from 'vue';

const props = defineProps({
    user: Object,
    record: Object,
    observacion: Object,
});

const breadcrumbItems = ref([
    { label: 'Dashboard', url: route('dashboard') },
    { label: 'Detalle de observación' }
]);


const formatDateTime = (iso) => {
    const opts = {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    };
    return new Date(iso).toLocaleString('es-ES', opts);
};

</script>

<template>
    <AuthenticatedLayout :user="user" title="Detalle de Observación">
        <div class="container py-4">
            <!-- Breadcrumb en lugar del botón Volver -->
            <div class="row mb-3">
                <Breadcrumb :items="breadcrumbItems" />
            </div>

            <div class="card p-4 shadow-sm">
                <h2 class="h5 mb-3">{{ observacion.titulo }}</h2>

                <p class="mt-3 mb-0">
                    <strong>Descripción:</strong>
                </p>
                <div class="text-gray-700 whitespace-pre-line">
                    {{ observacion.descripcion }}
                </div>

                <hr class="my-4" />

                <div class="contain-info-user">
                    <div class="autor-registro-ob d-flex flex-column">
                        <small class="mb-0">
                            <strong>Autor:</strong> {{ record.user?.name || '—' }}
                        </small>
                        <small class="mb-0">
                            <strong>Correo:</strong> {{ record.user?.email || '—' }}
                        </small>
                        <small>
                            <strong>Fecha del registro:</strong> {{ formatDateTime(observacion.fecha) }}
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
