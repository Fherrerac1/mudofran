<script>
import MaterialButton from '@/Components/MaterialButton.vue';
import MaterialTable from "@/Components/MaterialTableGeneral.vue";
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Swal from 'sweetalert2';
import { router } from '@inertiajs/vue3';
import MaterialSelect from '@/Components/MaterialSelect.vue';
import Datepicker from "@vuepic/vue-datepicker";
import "@vuepic/vue-datepicker/dist/main.css";

export default {
    components: {
        MaterialTable,
        MaterialButton,
        AuthenticatedLayout,
        MaterialSelect,
        Datepicker,
    },
    props: {
        user: Object,
        users: Array,
        timeOffs: Array,
    },
    data() {
        return {
            selected_usuario: null,
            selected_estado: null,
            dateRange: [],
        };
    },
    computed: {
        estadoOptions() {
            return [
                { value: 0, text: 'Pendiente' },
                { value: 1, text: 'Aprobado' },
                { value: 2, text: 'Rechazado' },
                { value: 3, text: 'Cancelado' },
            ];
        },
        usuarioOptions() {
            return this.users.map(u => ({
                value: u.id,
                text: u.name,
            }));
        },
        filteredTimeOffs() {
            return this.timeOffs.filter(to => {
                const matchesUser = this.selected_usuario === null || to.user_id === Number(this.selected_usuario);
                const matchesEstado = this.selected_estado === null || to.estado === Number(this.selected_estado);
                const matchesDateRange = (() => {
                    if (!Array.isArray(this.dateRange) || this.dateRange.length !== 2) return true;
                    if (!this.dateRange[0] || !this.dateRange[1]) return true;
                    const start = new Date(this.dateRange[0]).setHours(0, 0, 0, 0);
                    const end = new Date(this.dateRange[1]).setHours(0, 0, 0, 0);
                    const createdAt = new Date(to.created_at).setHours(0, 0, 0, 0);
                    return createdAt >= start && createdAt <= end;
                })();

                return matchesUser && matchesEstado && matchesDateRange;
            });
        }
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
        confirmDelete(id) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: 'Esta acción no se puede deshacer.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
            }).then((result) => {
                if (result.isConfirmed) {
                    this.deleteTimeOff(id);
                }
            });
        },
        deleteTimeOff(id) {
            router.delete(`/time-off/${id}`, {
                onSuccess: () => {
                    Swal.fire('Eliminado', 'La solicitud fue eliminada.', 'success')
                        .then(() => location.reload());
                },
                onError: () => {
                    Swal.fire('Error', 'No se pudo eliminar la solicitud.', 'error');
                }
            });
        },
    },
};
</script>

<template>
    <AuthenticatedLayout :user="user" title="Solicitudes de Permiso">
        <div class="container-fluid">
            <div class="row mb-3">
                <div class="col">
                    <a href="javascript:history.back()" class="text-decoration-none fs-5">
                        <span class="material-icons align-middle me-1">arrow_back</span>
                        Volver
                    </a>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-lg-3 mb-2" v-if="user.rol === 'admin' || user.rol === 'gestor'">
                    <MaterialSelect id="selected_usuario" v-model="selected_usuario" name="usuario"
                        placeholder="Filtrar por Usuario" :options="usuarioOptions" />
                </div>
                <div class="col-lg-3 mb-2">
                    <MaterialSelect id="selected_estado" v-model="selected_estado" name="estado"
                        placeholder="Filtrar por Estado" :options="estadoOptions" />
                </div>
                <div class="col-lg-3 mb-2">
                    <Datepicker v-model="dateRange" range placeholder="Filtrar por fecha" :enable-time-picker="false"
                        :format="'dd/MM/yyyy'" locale="es" />
                </div>
            </div>

            <MaterialTable title="SOLICITUDES DE PERMISO">
                <thead>
                    <tr>
                        <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-start ps-1">Fecha creación
                        </th>
                        <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-start ps-1">Desde</th>
                        <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-start ps-1">Hasta</th>
                        <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-start ps-1">Estado</th>
                        <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-start ps-1">Usuario</th>
                        <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-center ps-1">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="item in filteredTimeOffs" :key="item.id">
                        <td class="text-xs font-weight-bold">{{ formatDate(item.created_at) }}</td>
                        <td class="text-xs font-weight-bold">{{ formatDate(item.from) }}</td>
                        <td class="text-xs font-weight-bold">{{ formatDate(item.to) }}</td>
                        <td class="text-xs font-weight-bold">{{ estadoLabel(item.estado) }}</td>
                        <td class="text-xs font-weight-bold">{{ item.user?.name || 'Sin usuario' }}</td>
                        <td class="text-xs font-weight-bold text-center">
                            <a :href="route('time_off.show', item.id)" class="me-2" title="Ver solicitud">
                                <span class="material-icons text-primary">visibility</span>
                            </a>
                            <a href="#" @click.prevent="confirmDelete(item.id)" title="Eliminar">
                                <span class="material-icons text-danger">delete</span>
                            </a>
                        </td>
                    </tr>
                </tbody>
            </MaterialTable>
        </div>
    </AuthenticatedLayout>
</template>
