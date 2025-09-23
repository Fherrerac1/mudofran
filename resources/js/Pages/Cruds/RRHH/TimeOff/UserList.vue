<template>
    <div class="card mt-4">
        <div class="card-header">
            <h6 class="mb-0">Mis solicitudes existentes</h6>
        </div>
        <div class="card-body">
            <template v-if="user.time_offs && user.time_offs.length">
                <ul class="list-group">
                    <li class="list-group-item d-flex justify-content-between align-items-center"
                        v-for="item in user.time_offs" :key="item.id">
                        <div>
                            <strong>{{ formatDate(item.from) }}</strong> - <strong>{{ formatDate(item.to) }}</strong>
                        </div>
                        <div class="d-flex align-items-center">
                            <span class="badge me-3" :class="estadoBadgeClass(item.estado)">
                                {{ estadoLabel(item.estado) }}
                            </span>

                            <!-- Cancelar solo si está pendiente -->
                            <button v-if="item.estado === 0" @click="cancelRequest(item.id)"
                                class="btn btn-sm btn-outline-danger" title="Cancelar">
                                <span class="material-icons" style="font-size: 18px;">cancel</span>
                            </button>
                        </div>
                    </li>

                </ul>
            </template>
            <template v-else>
                <div class="text-center text-muted my-3">
                    <span class="material-icons fs-1 text-secondary">event_busy</span>
                    <p class="mt-2 mb-0">No tienes solicitudes registradas aún.</p>
                </div>
            </template>

            <div class="text-center mt-3">
                <a class="btn bg-info rounded text-light" data-bs-toggle="modal" data-bs-target="#createTimeOff">
                    <span class="material-icons align-middle me-1">add_circle</span>
                    Nueva Solicitud
                </a>
            </div>
        </div>
    </div>
    <TimeOffRequest :user="user" />
</template>

<script>
import Swal from 'sweetalert2';
import MaterialInput from '@/Components/MaterialInput.vue';
import MaterialButton from '@/Components/MaterialButton.vue';
import { useForm } from '@inertiajs/vue3';
import TimeOffRequest from './TimeOffRequest.vue';

export default {
    components: {
        MaterialInput,
        MaterialButton,
        TimeOffRequest
    },
    props: {
        user: {
            type: Object,
            required: true
        },
    },
    data() {
        const today = new Date().toISOString().split('T')[0];
        return {
            today,
            form: useForm({
                from: '',
                to: '',
            })
        };
    },
    methods: {
        cancelRequest(id) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: 'Esta acción cancelará tu solicitud de permiso.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, cancelar',
                cancelButtonText: 'No',
            }).then((result) => {
                if (result.isConfirmed) {
                    this.$inertia.put(route('time_off.update', id), {
                        estado: 3 // estado cancelado
                    }, {
                        preserveScroll: true,
                        onSuccess: () => {
                            Swal.fire({
                                icon: 'success',
                                title: 'Cancelado',
                                text: 'La solicitud ha sido cancelada.',
                            }).then(() => window.location.reload());
                        },
                        onError: () => {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'No se pudo cancelar la solicitud.',
                            });
                        }
                    });
                }
            });
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
        estadoBadgeClass(estado) {
            switch (estado) {
                case 0: return 'bg-warning';
                case 1: return 'bg-success';
                case 2: return 'bg-danger';
                case 3: return 'bg-secondary';
                default: return 'bg-dark';
            }
        },
        formatDate(dateString) {
            const options = { year: 'numeric', month: 'long', day: 'numeric' };
            return new Date(dateString).toLocaleDateString('es-ES', options);
        },
    }
};
</script>
