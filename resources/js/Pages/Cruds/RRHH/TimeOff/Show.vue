<script>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import MaterialButton from '@/Components/MaterialButton.vue';
import Swal from 'sweetalert2';

// Import Calendar component from v-calendar@next
import { Calendar } from 'v-calendar';
import 'v-calendar/style.css';

export default {
    components: {
        AuthenticatedLayout,
        MaterialButton,
        Calendar,  // register calendar
    },
    props: {
        user: Object,
        timeOff: Object,
    },
    computed: {
        selectedRange() {
            return {
                start: new Date(this.timeOff.from),
                end: new Date(this.timeOff.to),
            };
        },
    },
    methods: {
        formatDate(dateString) {
            const options = { year: 'numeric', month: 'long', day: 'numeric' };
            return new Date(dateString).toLocaleDateString('es-ES', options);
        },
        estadoLabel(estado) {
            const map = {
                0: 'Pendiente',
                1: 'Aprobado',
                2: 'Rechazado',
                3: 'Cancelado',
            };
            return map[estado] || 'Desconocido';
        },
        updateRequestStatus(estado) {
            const estadoLabel = estado === 1 ? 'aprobar' : 'rechazar';

            Swal.fire({
                title: `¿Estás seguro de que deseas ${estadoLabel} esta solicitud?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, confirmar',
                cancelButtonText: 'Cancelar',
            }).then(result => {
                if (result.isConfirmed) {
                    this.$inertia.put(`/time-off/${this.timeOff.id}`, { estado }, {
                        onSuccess: () => {
                            Swal.fire('Éxito', `La solicitud ha sido ${estadoLabel}da.`, 'success');
                        },
                        onError: () => {
                            Swal.fire('Error', 'No se pudo actualizar la solicitud.', 'error');
                        }
                    });
                }
            });
        },
        canManage() {
            return (this.user.rol === 'admin' || this.user.rol === 'gestor') && this.timeOff.estado === 0;
        },
    }
}
</script>

<template>
    <AuthenticatedLayout :user="user" title="Detalle de Solicitud de Permiso">
        <div class="container py-4">
            <div class="mb-3">
                <a href="javascript:history.back()" class="text-decoration-none fs-5">
                    <span class="material-icons align-middle me-1">arrow_back</span>
                    Volver
                </a>
            </div>

            <div class="card shadow border-radius-lg p-4 mb-4">
                <h5 class="mb-4">Información de la Solicitud</h5>

                <div class="row mb-2">
                    <div class="col-md-4">
                        <strong>Usuario:</strong>
                        <div>{{ timeOff.user?.name || 'Desconocido' }}</div>
                    </div>
                    <div class="col-md-4">
                        <strong>Desde:</strong>
                        <div>{{ formatDate(timeOff.from) }}</div>
                    </div>
                    <div class="col-md-4">
                        <strong>Hasta:</strong>
                        <div>{{ formatDate(timeOff.to) }}</div>
                    </div>
                </div>

                <div class="row mb-2">
                    <div class="col-md-4">
                        <strong>Estado:</strong>
                        <div>{{ estadoLabel(timeOff.estado) }}</div>
                    </div>
                    <div class="col-md-4">
                        <strong>Fecha de solicitud:</strong>
                        <div>{{ formatDate(timeOff.created_at) }}</div>
                    </div>
                    <div class="col-md-4" v-if="timeOff.accepted_by">
                        <strong>Aprobado por:</strong>
                        <div>{{ timeOff.accepted_by.name }}</div>
                    </div>
                </div>
            </div>

            <!-- Calendar showing selected days -->
            <div class="card shadow border-radius-lg p-4 mb-4">
                <h5 class="mb-4">Días seleccionados</h5>
                <Calendar class="w-100" :attributes="[
                    {
                        key: 'selected',
                        highlight: true,
                        dates: { start: selectedRange.start, end: selectedRange.end }
                    }
                ]" is-range :min-date="new Date()" />
            </div>

            <!-- Gestión de estado -->
            <div class="mt-4" v-if="canManage()">
                <MaterialButton color="success" class="me-2" @click="updateRequestStatus(1)">
                    <span class="material-icons me-2">check_circle</span> Aprobar
                </MaterialButton>

                <MaterialButton color="warning" @click="updateRequestStatus(2)">
                    <span class="material-icons me-2">cancel</span> Rechazar
                </MaterialButton>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
