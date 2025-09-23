<template>
    <AuthenticatedLayout :user="user" :title="'Mostrar producto'">
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
                    <div class="container p-0 mb-4">
                        <div class="row d-flex justify-content-center">
                            <div class="col-lg-8 col-md-10">
                                <div class="card shadow-sm">
                                    <div class="card-header style_color text-center py-3">
                                        <h3 class="style_color mb-0">{{ producto.nombre }}</h3>
                                    </div>
                                    <div class="card-body py-4 d-flex flex-column gap-3">
                                        <!-- producto Details -->
                                        <div class="d-flex flex-wrap">
                                            <span class="fw-bold col-lg-6">Fecha:</span>
                                            <span class="col-lg-6">{{ formatDate(producto.updated_at) }}</span>
                                        </div>
                                        <div class="d-flex flex-wrap">
                                            <span class="fw-bold col-lg-6">Precio:</span>
                                            <span class="col-lg-6">{{ formatCurrency(producto.precio) }}</span>
                                        </div>

                                        <div class="d-flex flex-wrap">
                                            <span class="fw-bold col-lg-6">Observaciones : </span>
                                            <span class="col-lg-6">{{ producto.observaciones || 'Sin observaciones'
                                                }}</span>
                                        </div>
                                        <div class="d-flex flex-wrap">
                                            <span class="fw-bold col-lg-6">Servicio : </span>
                                            <span class="col-lg-6">{{ producto.servicio?.nombre || 'No asignada'
                                                }}</span>
                                        </div>

                                        <div class="d-flex flex-wrap">

                                            <div class="d-flex flex-wrap">
                                                <span class="fw-bold col-lg-6">Archivos:</span>
                                                <span class="col-lg-6">
                                                    <div v-if="producto.documents.length">
                                                        <div class="d-flex flex-wrap gap-3">
                                                            <div v-for="(file, index) in producto.documents"
                                                                :key="index" class="file-item">
                                                                <!-- Display image -->
                                                                <a :href="getFileUrl(file)" target="_blank"
                                                                    rel="noopener noreferrer">
                                                                    <img :src="getFileUrl(file)" :alt="'Image ' + index"
                                                                        class="img-fluid rounded-3"
                                                                        style="max-width: 200px; max-height: 200px;">
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <p v-else class="mb-0">Sin archivos adjuntos</p>
                                                </span>
                                            </div>
                                        </div>
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
import MaterialButton from '@/Components/MaterialButton.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

export default {
    props: {
        user: {
            type: Object,
            required: true
        },
        producto: {
            type: Object,
            required: true
        },
    },
    components: {
        AuthenticatedLayout,
        MaterialButton,
    },
    methods: {
        formatCurrency(amount) {
            if (isNaN(amount)) return ''; // Handle non-numeric input

            // Format the number with grouping for thousands, and replace the dot with a comma for decimals
            return amount
                .toFixed(2)  // Ensure 2 decimal places
                .replace('.', ',')  // Replace dot with comma for decimal separator
                .replace(/\B(?=(\d{3})+(?!\d))/g, '.') // Add dots for thousands separators
                + ' €';  // Add Euro symbol at the end
        },
        getFileUrl(filePath) {
            const modifiedArchivo = filePath.replace("public/", ""); // Remove 'public/' segment
            return "/storage/" + modifiedArchivo;
        },
        formatDate(dateString) {
            const options = { year: 'numeric', month: 'long', day: 'numeric' };
            return new Date(dateString).toLocaleDateString('es-ES', options);
        },
    }
};
</script>
