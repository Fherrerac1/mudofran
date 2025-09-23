<script>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import MaterialButton from "@/Components/MaterialButton.vue";
import MaterialSelect from "@/Components/MaterialSelect.vue";
import Datepicker from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css';
import Swal from 'sweetalert2';
import EstadoModal from "./EstadoModal.vue";
import MaterialTable from "@/Components/MaterialTableGeneral.vue";
import axios from "axios";

export default {
    components: {
        AuthenticatedLayout,
        MaterialButton,
        MaterialSelect,
        Datepicker,
        EstadoModal,
        MaterialTable,
    },
    props: {
        horarios: { type: Array, required: true, },
        users: { type: Array, required: true, },
        user: { type: Object, required: true, },
        puestos: { type: Array, default: () => [] },
        estados: { type: Array, default: () => [] },
    },
    data() {
        return {
            selectedHorario: null,
            selectedHorarios: [],
            isSubmitting: false,
            user_id: '',
            selected_nombre: null,
            selected_puesto: null,
            selected_estado: null,
            dateRange: [],
            selectAll: false,
        };
    },
    computed: {
        filteredHorarios() {
            let result = this.horarios;

            // 🔹 Filtro por fecha
            if (
                Array.isArray(this.dateRange) &&
                this.dateRange.length === 2 &&
                this.dateRange[0] &&
                this.dateRange[1]
            ) {
                const [startDate, endDate] = this.dateRange.map(d =>
                    new Date(d).setHours(0, 0, 0, 0)
                );
                result = result.filter((horario) => {
                    const createdAt = new Date(horario.created_at).setHours(0, 0, 0, 0);
                    return createdAt >= startDate && createdAt <= endDate;
                });
            }

            // 🔹 Filtro por nombre de usuario
            if (this.selected_nombre !== null) {
                result = result.filter(horario =>
                    horario.user?.name === this.selected_nombre
                );
            }

            // 🔹 Filtro por puesto
            if (this.selected_puesto !== null) {
                result = result.filter(horario =>
                    horario.user?.position === this.selected_puesto
                );
            }

            // 🔹 Filtro por estado
            if (this.selected_estado !== null) {
                result = result.filter(horario =>
                    horario.estado === Number(this.selected_estado)
                );
            }

            return result;
        },
        filteredHorariosRenderKey() {
            return `${this.selected_nombre}-${this.selected_puesto}-${this.selected_estado}-${this.dateRange}`;
        },
        nombreOptions() {
            const nombres = this.horarios
                .map(h => h.user?.name)
                .filter(n => !!n);

            const unique = [...new Set(nombres)];

            return unique.map(name => ({ value: name, text: name }));
        },
        sumaTotalSeleccionado() {
            if (this.selectedHorarios.length === 0) return '0:00';

            let totalMinutos = 0;

            this.filteredHorarios.forEach(horario => {
                if (this.selectedHorarios.includes(horario.id) && horario.total) {
                    const partes = horario.total.toString().split(':');
                    const horas = parseInt(partes[0]) || 0;
                    const minutos = parseInt(partes[1]) || 0;
                    totalMinutos += horas * 60 + minutos;
                }
            });

            const totalHoras = Math.floor(totalMinutos / 60);
            const minutosRestantes = totalMinutos % 60;

            return `${totalHoras}:${minutosRestantes.toString().padStart(2, '0')}`;
        },
        sumaPausaSeleccionado() {
            let totalSegundos = 0;

            this.filteredHorarios.forEach(horario => {
                if (this.selectedHorarios.includes(horario.id) && horario.pause_total_formatted) {
                    const partes = horario.pause_total_formatted.split(':');
                    const h = parseInt(partes[0]) || 0;
                    const m = parseInt(partes[1]) || 0;
                    const s = parseInt(partes[2]) || 0;
                    totalSegundos += h * 3600 + m * 60 + s;
                }
            });

            const horas = Math.floor(totalSegundos / 3600);
            const minutos = Math.floor((totalSegundos % 3600) / 60);
            const segundos = totalSegundos % 60;

            return `${horas.toString().padStart(2, '0')}:${minutos.toString().padStart(2, '0')}:${segundos.toString().padStart(2, '0')}`;
        },
        sumaTrabajadoSeleccionado() {
            let totalSegundos = 0;

            this.filteredHorarios.forEach(horario => {
                if (this.selectedHorarios.includes(horario.id) && horario.worked_time_formatted) {
                    const partes = horario.worked_time_formatted.split(':');
                    const h = parseInt(partes[0]) || 0;
                    const m = parseInt(partes[1]) || 0;
                    const s = parseInt(partes[2]) || 0;
                    totalSegundos += h * 3600 + m * 60 + s;
                }
            });

            const horas = Math.floor(totalSegundos / 3600);
            const minutos = Math.floor((totalSegundos % 3600) / 60);
            return `${horas}:${minutos.toString().padStart(2, '0')}`;
        },
    },
    watch: {
        /**
         * Observa los cambios en selectedHorarios y actualiza selectAll.
         * Si la longitud de selectedHorarios coincide con la de filteredHorarios,
         * establece selectAll como verdadero; de lo contrario, como falso.
         */
        selectedHorarios(newVal) {
            this.selectAll = newVal.length === this.filteredHorarios.length;
        }
    }, // <== agregado
    methods: {
        accptarPendientes() {
            const pendientes = this.filteredHorarios.filter(horario => horario.estado === 0);

            if (pendientes.length === 0) {
                Swal.fire({
                    icon: 'info',
                    title: 'No hay pendientes',
                    text: 'No hay registros pendientes para aceptar.',
                });
                this.isSubmitting = false;
                return;
            }

            const pendienteIds = pendientes.map(horario => horario.id);

            Swal.fire({
                title: '¿Estás seguro?',
                text: `Vas a aceptar ${pendienteIds.length} registros pendientes.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, aceptar',
                cancelButtonText: 'Cancelar',
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Procesando...',
                        text: 'Por favor, espera mientras procesamos tu solicitud.',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading(),
                    });
                    this.isSubmitting = true;
                    axios.post(route('accept.all.records'), { ids: pendienteIds })
                        .then(() => {
                            Swal.fire({
                                icon: 'success',
                                title: 'Registros aceptados',
                                text: `${pendienteIds.length} registros han sido aceptados.`,
                            }).then(() => {
                                location.reload();
                            });
                        })
                        .catch((error) => {
                            this.isSubmitting = false;
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: error.response?.data?.message || 'Ocurrió un error al aceptar los registros.',
                            });
                        });
                }
            });
        },
        download(format) {
            Swal.fire({
                title: 'Cargando...',
                text: 'Estamos procesando tu solicitud, por favor espera.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            this.isSubmitting = true;

            //
            const ids = this.selectedHorarios.length > 0 // Coge los horarios seleccionados si los hay
                ? this.selectedHorarios // Sino coge los horarios filtrados
                : this.filteredHorarios.map(horario => horario.id);  //

            const data = {
                horarios: ids,
                format: format,
            };

            axios.get(route('record.report'), { params: data, responseType: 'blob' })
                .then((response) => {
                    const blob = new Blob([response.data], { type: response.headers['content-type'] });

                    const contentDisposition = response.headers['content-disposition'];
                    const filenameMatch = contentDisposition.match(/filename="(.+)"/);
                    const filename = filenameMatch ? filenameMatch[1].trim() : 'file.pdf';

                    const link = document.createElement('a');
                    link.href = URL.createObjectURL(blob);
                    link.download = filename;
                    link.click();

                    Swal.fire({
                        icon: 'success',
                        title: 'Fecha Inicializada',
                        text: 'Fecha Inicializada correctamente.',
                    }).then(() => {
                        location.reload();
                    });
                })
                .catch((error) => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: error.response?.data?.message || 'An error occurred while processing your request.',
                    });
                    this.isSubmitting = false;
                });
        },
        formatDate(dateString) {
            const options = { year: "numeric", month: "long", day: "numeric" };
            return new Date(dateString).toLocaleDateString("es-ES", options);
        },
        /**
         * Determina si una fecha dada (facturaDate) es igual o está entre dos fechas dadas (startDate y endDate).
         *
         * @param {Date} facturaDate Fecha a comparar
         * @param {Date} startDate Fecha de inicio del rango
         * @param {Date} endDate Fecha de fin del rango
         * @returns {boolean} Verdadero si la fecha es igual o está entre el rango
         */
        isSameOrBetweenDates(fecha, inicio, fin) {
            const normalize = (date) => {
                const d = new Date(date);
                d.setHours(0, 0, 0, 0);
                return d;
            };

            const dFecha = normalize(fecha);
            const dInicio = normalize(inicio);
            const dFin = normalize(fin);

            return dFecha >= dInicio && dFecha <= dFin;
        },
        /**
         * Alterna la selección de todos los horarios.
         * Si selectAll es verdadero, selecciona todos los horarios filtrados.
         * De lo contrario, limpia la selección de horarios.
         */
        toggleAllSelection() {
            if (this.selectAll) {
                this.selectedHorarios = this.filteredHorarios.map(
                    (horario) => horario.id
                );
            }
            else {
                this.selectedHorarios = [];
            }
        }
    },
    mounted() {
    
    }

};
</script>

<template>
    <AuthenticatedLayout :user="user" :title="'CONTROL DE HORARIOS'">
        <div class="container-fluid overflow-visible">
            <!-- Zona de Filtros + Botones -->
            <div class="row align-items-center justify-content-between pt-2 p-4 px-2">
                <!-- Botón Volver -->
                <div class="col-auto">
                    <a class="d-flex text-decoration-none fs-5 align-items-center" href="javascript:history.go(-1);"
                        role="button">
                        <i class="material-icons pe-2 pt-1">
                            <span class="material-symbols-outlined">arrow_back</span>
                        </i>
                        Volver
                    </a>
                </div>

                <!-- Botones Excel/PDF -->
                <div class="col-auto d-flex gap-2">
                    <MaterialButton :disabled="isSubmitting" @click="download('excel')">
                        <i class="material-icons">file_download</i> Excel
                    </MaterialButton>

                    <MaterialButton :disabled="isSubmitting" @click="download('pdf')">
                        <i class="material-icons">picture_as_pdf</i> PDF
                    </MaterialButton>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-3 mb-2">
                    <MaterialSelect id="nombre" v-model="selected_nombre" name="nombre" placeholder="Filtrar por Usuario"
                        :options="nombreOptions" />
                </div>

                <div class="col-lg-3 mb-2">
                    <MaterialSelect id="puesto" v-model="selected_puesto" name="puesto" placeholder="Filtrar por Puesto"
                        :options="puestos" />
                </div>

                <div class="col-lg-3 mb-2">
                    <MaterialSelect id="estado" v-model="selected_estado" name="estado" placeholder="Filtrar por Estado"
                        :options="estados" />
                </div>

                <div class="col-lg-3">
                    <Datepicker v-model="dateRange" range placeholder="Filtrar por Fecha" :enable-time-picker="false"
                        :format="'dd/MM/yyyy'" locale="es" />
                </div>
            </div>

            <div id="horarios_div">
                <div class="my-0 mb-3" style="display: flex; justify-content: flex-end;">
                    <button class="btn btn-sm btn-success m-0" :disabled="isSubmitting" @click="accptarPendientes">
                        <i class="material-icons text-lg">checklist</i> Aceptar Pendientes
                    </button>
                </div>

                <MaterialTable :key="filteredHorariosRenderKey" title="CONTROL DE HORARIOS">
                    <thead>
                        <tr>
                            <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-start ps-1">
                                <input type="checkbox" v-model="selectAll" @change="toggleAllSelection" />
                                Nombre
                            </th>
                            <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-start ps-1">Puesto
                            </th>
                            <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-start ps-1">Fecha
                            </th>
                            <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-start ps-1">Inicio
                            </th>
                            <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-start ps-1">Fin
                            </th>
                            <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-start ps-1">Comida
                            </th>
                            <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-start ps-1">Pausa
                            </th>
                            <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-start ps-1">Tiempo
                                Trabajado</th>
                            <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-start ps-1">Tiempo
                                Total</th>
                            <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-center">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="table-horario-tbody">
                        <tr v-for="horario in filteredHorarios" :key="horario.id">
                            <td class="text-xs font-weight-bold text-start">
                                <input type="checkbox" v-model="selectedHorarios" :value="horario.id" />
                                {{ horario.user?.name }}
                            </td>

                            <td class="text-xs font-weight-bold text-start">
                                {{ horario.user?.position }}
                            </td>
                            <td class="text-xs font-weight-bold text-start" data-type="date">{{
                                formatDate(horario.created_at) }}
                            </td>
                            <td class="text-xs font-weight-bold text-start">{{ horario.time_in }}</td>
                            <td class="text-xs font-weight-bold text-start">{{ horario.time_out || 'En curso' }}</td>

                            <td class="text-xs font-weight-bold text-start">
                                {{ horario.meal_total_formatted === '00:00:00' ? '-' : horario.meal_total_formatted
                                }}
                            </td>


                            <td class="text-xs font-weight-bold text-start">
                                {{ !horario.pause_total_formatted || horario.pause_total_formatted === '00:00:00' ?
                                    '-' : horario.pause_total_formatted }}
                            </td>

                            <td class="text-xs font-weight-bold text-start">{{ horario.worked_time_formatted || '-' }} h
                            </td>
                            <td class="text-xs font-weight-bold text-start">{{ horario.total || '-' }} h</td>

                            <td class="text-xs font-weight-bold text-center">
                                <span role="button" @click="selectedHorario = horario" data-bs-toggle="modal"
                                    data-bs-target="#estadoModal" class="p-1 rounded text-light" :class="{
                                        'bg-primary': horario.estado === 0,
                                        'bg-info': horario.estado === 1,
                                        'bg-success': horario.estado === 2
                                    }">
                                    {{ horario.estado_text }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td class="text-right font-weight-bolder text-start">
                                Total seleccionado
                            </td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td class="font-weight-bolder">{{ sumaPausaSeleccionado }} h</td>
                            <td class="font-weight-bolder">{{ sumaTrabajadoSeleccionado }} h</td>
                            <td class="font-weight-bolder">{{ sumaTotalSeleccionado }} h</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </MaterialTable>
            </div>
        </div>

        <EstadoModal v-show="selectedHorario" :horario="selectedHorario" />
    </AuthenticatedLayout>
</template>


<style>
.table-horario-tbody tr td {
    padding-top: 10px !important;
    padding-bottom: 10px !important;
}

.mb-custom-responsive {
    margin-bottom: -38px !important;
}

/* #horarios_div .dt-search {
    display: none;
} */

@media (max-width: 1024px) {
    .dt-length {
        visibility: hidden;
    }
}
</style>
