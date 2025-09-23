<template>
    <AuthenticatedLayout :user="user" :title="'Administración'">
        <!-- Main content -->
        <div class="container-fluid">
            <div class="row mt-2">
                <!-- Content Section -->
                <div class="row">
                    <!-- User Profile Information -->
                    <div class="col-md-2">
                        <div class="card shadow">
                            <img :src="getArchivoUrl(user.img)" class="card-img-top" alt="Profile" />
                            <div class="card-body p-2">
                                <h5 class="card-title mb-2 text-center">{{ user.nombre }}</h5>

                                <ul class="list-unstyled small mb-0">
                                    <li><i class="fas fa-envelope me-1 text-secondary"></i> {{ user.email }}</li>
                                    <li><i class="fas fa-briefcase me-1 text-secondary"></i> {{ user.position }}</li>
                                    <li><i class="fas fa-user-shield me-1 text-secondary"></i> {{ user.rol }}</li>
                                    <li><i class="fas fa-phone me-1 text-secondary"></i> {{ user.telefono }}</li>
                                    <li><i class="fas fa-id-card me-1 text-secondary"></i> {{ user.nif }}</li>
                                    <li v-if="user.rol === 'admin' && licencia">
                                        <i class="fas fa-certificate me-1 text-secondary"></i> Licencia: {{ licencia.expiracion }}
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="d-flex gap-1">
                            <a class="btn my-3 unique_bg btn-sm" data-bs-toggle="modal" data-bs-target="#passwordModal">
                                <div class="text-center">Cambiar Contraseña</div>
                            </a>
                        </div>

                        <PlantillaMaestra :plantilla="plantilla" />
                    </div>

                    <div class="col-md-10">
                        <div class="col">
                            <MethodsTable :methods="metodos" />
                        </div>

                    </div>
                </div>
            </div>
        </div>
        <!-- Modals -->
        <ResetPasswordModal :user="user" />
    </AuthenticatedLayout>
</template>

<script>
import ResetPasswordModal from './sections/ResetPasswordModal.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PlantillaMaestra from './sections/PlantillaMaestra.vue';
import MethodsTable from './sections/MethodsTable.vue';
import placeholder from '@/assets/img/placeholder.jpg';

export default {
    components: {
        ResetPasswordModal,
        PlantillaMaestra,
        AuthenticatedLayout,
        MethodsTable,
    },
    props: {
        user: { type: Object, required: true, },
        existingCoste: { type: Object, required: false, },
        metodos: {  type: Array, required: false, },
        configuration: {  type: Object, required: false,  },
        plantilla: {
            type: Array,
            required: true,
        },
    },
    data() {
        return {
            licencia: false
        }
    },
    mounted() {
        this.checkUser(this.configuration.serial_num, this.configuration.tax_id, this.configuration.url_app);
    },
    methods: {
        async checkUser(numero_serie, nif, url_app) {
            const params = {
                numero_serie: numero_serie,
                nif: nif,
                url_app: url_app
            };
            try {
                const response = await axios.post('https://api.elayudante.es/api/check', params, {
                    headers: {
                        'Content-Type': 'application/json'
                    }
                });
                if (response.data.codigo == 1) {
                    this.licencia = response.data;
                }
                return response.data;
            } catch (error) {
                console.error('API Error:', error);
            }
        },
        getArchivoUrl(archivo) {
            if (archivo !== null) {
                if (typeof archivo === 'string') {
                    const modifiedArchivo = archivo.replace("public/", ""); // Remove 'public/' segment
                    return "/storage/" + modifiedArchivo;
                } else if (archivo instanceof File) {
                    return URL.createObjectURL(archivo);
                } else {
                    return placeholder; // Return placeholder in case of an invalid file type
                }
            } else {
                return placeholder; // Return placeholder when archivo is null
            }
        }
    }
};
</script>
