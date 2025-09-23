<script>
import MaterialButton from '@/Components/MaterialButton.vue';
import MaterialTable from "@/Components/MaterialTableGeneral.vue";
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Swal from 'sweetalert2';

export default {
    components: {
        MaterialTable,
        MaterialButton,
        AuthenticatedLayout
    },
    props: {
        user: {
            type: Object,
            required: true
        }
    },
    methods: {
        formatDate(dateString) {
            const options = { year: 'numeric', month: 'long', day: 'numeric' };
            return new Date(dateString).toLocaleDateString('es-ES', options);
        }
    }
}
</script>

<template>
    <AuthenticatedLayout :user="user" :title="'RRHH'">
        <div class="container-fluid">
            <div class="row mb-3">
                <a class="col-auto d-flex text-decoration-none fs-5" href="javascript:history.go(-1);" role="button">
                    <i class="material-icons pe-2 pt-1">
                        <span class="material-icons-outlined">arrow_back</span>
                    </i>Volver
                </a>
            </div>

            <div class="row">
                <!-- Sidebar -->
                <div class="col-12 col-md-3 mb-3">
                    <div class="d-none d-md-block rounded p-3 shadow-sm">
                        <h5 class="mb-3">Recursos Humanos</h5>
                        <ul class="nav flex-column">
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center" href="/nominas">
                                    <span class="material-icons-outlined me-2">description</span> Nóminas
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center" href="/list_horarios">
                                    <span class="material-icons-outlined me-2">schedule</span> Control Horario
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center" href="/documentos">
                                    <span class="material-icons-outlined me-2">folder</span> Documentos y Certificados
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center" href="/vacaciones">
                                    <span class="material-icons-outlined me-2">beach_access</span> Petición de
                                    Vacaciones
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center" href="/ausencias">
                                    <span class="material-icons-outlined me-2">schedule_send</span> Ausencias / Horas
                                    Extra
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center" href="/bajas">
                                    <span class="material-icons-outlined me-2">medical_information</span> Bajas
                                </a>
                            </li>
                        </ul>
                    </div>

                    <!-- Mobile navbar dropdown -->
                    <div class="d-md-none mb-3">
                        <div class="dropdown">
                            <button class="btn btn-primary dropdown-toggle w-100" type="button"
                                data-bs-toggle="dropdown">
                                Menú RRHH
                            </button>
                            <ul class="dropdown-menu w-100">
                                <li>
                                    <a class="dropdown-item d-flex align-items-center" href="/nominas">
                                        <span class="material-icons-outlined me-2">description</span> Nóminas
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center" href="/list_horarios">
                                        <span class="material-icons-outlined me-2">schedule</span> Control Horario
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center" href="/documentos">
                                        <span class="material-icons-outlined me-2">folder</span> Documentos y
                                        Certificados
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center" href="/vacaciones">
                                        <span class="material-icons-outlined me-2">beach_access</span> Petición de
                                        Vacaciones
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center" href="/ausencias">
                                        <span class="material-icons-outlined me-2">schedule_send</span> Ausencias /
                                        Horas Extra
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center" href="/bajas">
                                        <span class="material-icons-outlined me-2">medical_information</span> Bajas
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="col-12 col-md-9">
                    <div class="bg-white rounded shadow p-4">
                        <h1 class="h4 mb-3">Bienvenido a Recursos Humanos</h1>
                        <p>Seleccione una opción en el menú para comenzar.</p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
