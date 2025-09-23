<script>
import MaterialButton from '@/Components/MaterialButton.vue';
import MaterialTable from "@/Components/MaterialTableGeneral.vue";
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Swal from 'sweetalert2';
import axios from 'axios';
import Create from './Create.vue';
import MaterialSelect from '@/Components/MaterialSelect.vue';
import Datepicker from "@vuepic/vue-datepicker";
import "@vuepic/vue-datepicker/dist/main.css";
import BootstrapModal from '@/Components/BootstrapModal.vue';

export default {
    components: {
        MaterialTable,
        MaterialButton,
        AuthenticatedLayout,
        Create,
        MaterialSelect,
        Datepicker,
        BootstrapModal,
    },
    props: {
        nominas: Array,
        users: Array,
        user: Object,
        datosUsuarioNomina: { type: Array, default: () => [] },
    },
    data() {
        return {
            dateRange: [],
            selected_usuario: null,
            selected_dni: null,
            selected_mes: null,
        }
    },
    computed: {
        filteredNominas() {
            let result = this.nominas;

            // 🔹 Filtro por fecha
            if (
                Array.isArray(this.dateRange) &&
                this.dateRange.length === 2 &&
                this.dateRange[0] &&
                this.dateRange[1]
            ) {
                const [start, end] = this.dateRange.map(d =>
                    new Date(d).setHours(0, 0, 0, 0)
                );
                result = result.filter(nomina => {
                    const createdAt = new Date(nomina.created_at).setHours(0, 0, 0, 0);
                    return createdAt >= start && createdAt <= end;
                });
            }

            // 🔹 Filtro por usuario
            if (this.selected_usuario !== null) {
                result = result.filter(nomina =>
                    nomina.user_id === Number(this.selected_usuario)
                );
            }

            // 🔹 Filtro por DNI
            if (this.selected_dni !== null) {
                result = result.filter(nomina =>
                    nomina.user?.dni === this.selected_dni
                );
            }

            // 🔹 Filtro por meses
            if (this.selected_mes !== null) {
                result = result.filter(nomina =>
                    nomina.mes === this.selected_mes
                );
            }

            return result;
        },
        usuarioOptions() {
            return this.datosUsuarioNomina.map(u => ({
                value: u.value,
                text: u.text,
            }));
        },
        dniOptions() {
            return this.datosUsuarioNomina.map(u => ({
                value: u.dni,
                text: u.dni,
            }));
        },
        mesesDisponibles() {
            const mesesUnicos = [...new Set(this.nominas.map(n => n.mes))].sort();
            return mesesUnicos.map(m => ({
                value: m,
                text: this.mesTexto(m),
            }));
        },
        filteredNominasRenderKey() {
            return `${this.selected_usuario}-${this.selected_dni}-${this.selected_mes}-${this.dateRange}`;
        },
    },
    methods: {
        formatDate(dateString) {
            const options = { year: 'numeric', month: 'long', day: 'numeric' };
            return new Date(dateString).toLocaleDateString('es-ES', options);
        },
        mesTexto(mes) {
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
                    this.deletenomina(id);
                }
            });
        },
        deletenomina(id) {
            axios.get(`/nominas/${id}`)
                .then(() => {
                    Swal.fire('Eliminado', 'La nómina fue eliminada.', 'success').then(() => {
                        window.location.reload();
                    });
                })
                .catch(() => {
                    Swal.fire('Error', 'No se pudo eliminar la nómina.', 'error');
                });
        },
    },
};
</script>

<template>
    <AuthenticatedLayout :user="user" title="Lista de nóminas">
        <div class="container-fluid overflow-visible">
            <div class="row mb-3">
                <div class="col">
                    <a href="javascript:history.back()" class="text-decoration-none fs-5">
                        <span class="material-icons align-middle me-1">arrow_back</span>
                        Volver
                    </a>
                </div>

                <div class="col d-flex justify-content-end" v-if="user.rol == 'admin' || user.rol == 'gestor'">
                    <a data-bs-toggle="modal" data-bs-target="#createnomina">
                        <MaterialButton>
                            <span class="material-icons me-2">add</span>
                            Crear nómina
                        </MaterialButton>
                    </a>
                </div>
            </div>

            <div class="row mb-3">
                <!-- Filtro por usuario -->
                <div class="col-lg-3 mb-2" v-if="user.rol === 'admin' || user.rol === 'gestor'">
                    <MaterialSelect id="selected_usuario" v-model="selected_usuario" name="usuario"
                        placeholder="Filtrar por Usuario" :options="usuarioOptions" />
                </div>

                <!-- Filtro por DNI -->
                <div v-if="user.rol === 'admin' || user.rol === 'gestor'" class="col-lg-3 mb-2">
                    <MaterialSelect id="selected_dni" v-model="selected_dni" name="dni" placeholder="Filtrar por DNI"
                        :options="dniOptions" />
                </div>

                <div class="col-lg-3 mb-2">
                    <MaterialSelect id="selected_mes" v-model="selected_mes" name="mes" placeholder="Filtrar por Mes"
                        :options="mesesDisponibles" />
                </div>

                <div class="col-lg-3 mb-2">
                    <Datepicker v-model="dateRange" range placeholder="Filtrar por fecha" :enable-time-picker="false"
                        :format="'dd/MM/yyyy'" locale="es" />
                </div>
            </div>

            <MaterialTable :key="filteredNominasRenderKey" title="NÓMINAS">
                <thead>
                    <tr>
                        <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-start ps-1">Fecha creación
                        </th>
                        <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-start ps-1">Periodo</th>
                        <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-start ps-1">Usuario</th>
                        <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-center ps-1">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="nomina in filteredNominas" :key="nomina.id">
                        <td class="text-xs font-weight-bold text-start">
                            {{ formatDate(nomina.created_at) }}
                        </td>
                        <td class="text-xs font-weight-bold text-start">
                            {{ mesTexto(nomina.mes) }} {{ nomina.anio }}
                        </td>
                        <td class="text-xs font-weight-bold text-start">
                            {{ nomina.user?.name || 'Sin nombre' }}
                        </td>
                        <td class="text-xs font-weight-bold text-center">
                            <a :href="`/storage/${nomina.archivo}`" target="_blank" class="me-2">
                                <span class="material-icons" title="Ver nómina">visibility</span>
                            </a>

                            <a href="#" @click.prevent="confirmDelete(nomina.id)" v-if="user.rol !== 'tecnico'">
                                <span class="material-icons text-danger" title="Eliminar">delete</span>
                            </a>
                        </td>
                    </tr>
                </tbody>
            </MaterialTable>
        </div>
        <Create :users="users" />
    </AuthenticatedLayout>
</template>
