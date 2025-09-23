<template>
    <AuthenticatedLayout :user="user" :title="'Vista Usuario'">
        <div class="container-fluid overflow-hidden">
            <div class="row mb-2">
                <Breadcrumb :items="breadcrumbItems" />
            </div>

            <!-- Main content -->
            <div class="row mt-1">
                <div class="col-12 px-4">
                    <div class="row">
                        <!-- ... contenido perfil ... -->
                        <div class="col-12 col-lg-3 order-1 mb-2">
                            <div class="card user-card shadow-sm border-0">
                                <img
                                    :src="getArchivoUrl(usuario.img)"
                                    class="card-img-top rounded-circle mx-auto mt-3"
                                    style="width: 180px; height: 180px; object-fit: cover;"
                                    alt="Profile"
                                />

                                <div class="card-block text-center p-3">
                                    <h5 class="mt-1 mb-2 fw-semibold text-gradient-unique">{{ usuario.name }}</h5>
                                    <h6 class="mb-2 small fst-italic mb-2 text-gradient-secondary">{{ usuario.email }}</h6>

                                    <ul class="list-unstyled text-start small ps-3">
                                        <li><i class="fas fa-phone me-2 text-secondary"></i>{{ usuario.telefono }}</li>
                                        <li><i class="fas fa-id-badge me-2 text-secondary"></i>Nº Seguridad: {{ usuario.num_seguridad }}</li>
                                        <li><i class="fas fa-birthday-cake me-2 text-secondary"></i>Nacimiento: {{ formatDate(usuario.fecha_nacimiento) }}</li>
                                        <li><i class="fas fa-calendar-alt me-2 text-secondary"></i>Alta: {{ formatDate(usuario.fecha_alta) }}</li>
                                        <li><i class="fas fa-map-marker-alt me-2 text-secondary"></i>{{ usuario.localidad }}, {{ usuario.direccion }}, {{ usuario.cp }}</li>
                                    </ul>

                                    <hr class="my-2" />

                                    <!-- Bloque de Rol, Puesto, DNI y Coste/h-->
                                    <div class="row text-center small">
                                        <div class="col-xl-4 col-4 border-end py-2">
                                            <div class="text-muted">Rol</div>
                                            <div class="fw-semibold" style="font-size: 0.8rem;">{{ usuario.rol }}</div>
                                        </div>
                                        <div class="col-xl-4 col-4 border-end  py-2">
                                            <div class="text-muted">Puesto</div>
                                            <div class="fw-semibold" style="font-size: 0.8rem;">{{ usuario.position }}</div>
                                        </div>
                                        <div class="col-xl-4  col-4 py-2">
                                            <div class="text-muted">DNI</div>
                                            <div class="fw-semibold" style="font-size: 0.8rem;">{{ usuario.dni }}</div>
                                        </div>
                                    </div>

                                    <hr class="my-2" />

                                    <div class="text-center small text-muted mt-2">
                                        Última aparición:
                                        <span :class="{
                                            'blinking-green text-success': usuario.last_appearance === 'activo',
                                            'text-warning': usuario.last_appearance !== 'activo'
                                        }">●</span> {{ usuario.last_appearance }}
                                    </div>
                                </div>
                            </div>


                            <div class="d-flex gap-1" v-if="user.rol === 'admin' || user.rol === 'gestor'">
                                <a class="btn my-3 unique_bg btn-sm" data-bs-toggle="modal"
                                data-bs-target="#resetPasswordAdminModal">
                                    <div class="text-center">RESTABLECER CONTRASEÑA</div>
                                </a>
                            </div>
                        </div>

                        <div class="col-12 col-lg-9 order-2">
                            <div class="row justify-content-start">
                                <!-- Últimos 7 días -->
                                <div class="col-xl-4 col-lg-6 col-md-12 col-12 min-width-card margin-fix-lg-only order-2 mb-2">
                                    <div class="card px-3 py-4 h-100">
                                        <h6 class="text-muted mb-4">
                                            <i class="fas fa-calendar-week me-2" style="color: #76a9ff;"></i>Últimos 7 días
                                        </h6>

                                        <ul class="list-unstyled mb-0">
                                            <li
                                                v-for="(day, index) in dailyLast7Days"
                                                :key="index"
                                                class="d-flex justify-content-between align-items-center py-2 px-1 border-bottom"
                                                style="font-size: 0.925rem;"
                                            >
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-circle me-2" style="font-size: 0.5rem; color: #76a9ff;"></i>
                                                <span class="text-secondary">{{ day.day }}</span>
                                            </div>
                                            <span class="fw-semibold">{{ day.time }}</span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                                <!-- Total de hoy escritorio -->
                                <div class="col-xl-4 col-12 min-width-card d-none d-xl-block mb-2">
                                    <RecordChart
                                        type="day"
                                        :hours-worked="hoursWorkedToday"
                                        :horario="horasDiariasEsperadas()"
                                        progress-color="#76a9ff"
                                    />
                                </div>

                                <!-- Total de esta semana escritorio -->
                                <div class="col-xl-4 col-12 min-width-card d-none d-xl-block mb-2">
                                    <RecordChart
                                        type="week"
                                        :hours-worked="hoursWorkedThisWeek"
                                        :horario="horasSemanalesEsperadas()"
                                        progress-color="#76a9ff"
                                    />
                                </div>

                                <!-- Total de hoy y esta semana en movil y tablet -->
                                <div class="col-12 order-1 min-width-card order-md-4 d-block d-xl-none mb-2">
                                    <RecordChartArrow
                                        :hours-worked-day="hoursWorkedToday"
                                        :hours-worked-week="hoursWorkedThisWeek"
                                        :horario-day="horasDiariasEsperadas()"
                                        :horario-week="horasSemanalesEsperadas()"
                                        progress-color="#76a9ff"
                                    />
                                </div>

                                <!-- Grafica de horarios -->
                                <div class="col-12 my-1 order-4 d-block">
                                    <chart-holder-card
                                            title="Horarios"
                                            :key="`record-chart-${Date.now()}`"
                                            id="horarios-chart"
                                            :subtitle="`Gráfico de la semana`"
                                            :update="user.last_appearance"
                                            color="white"
                                            class="fade-in"
                                        >

                                        <ReportsBarChartHorario :id="'record-chart'" :key="`record-chart-${Date.now()}`"
                                            :chart="{
                                                labels: chartData.labels,
                                                datasets: {
                                                label: 'Horario',
                                                data: chartData.data,
                                                pauseData: chartData.pause
                                                }
                                            }"
                                        />
                                    </chart-holder-card>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- BOTÓN PARTES -->
                    <div class="d-flex justify-content-end align-items-stretch gap-2 mt-2">
                        <button
                            class="btn btn-outline-secondary d-flex align-items-center gap-1 px-2 py-1 border-0 rounded-2 mb-0"
                            type="button"
                            @click="isParteCollapseOpen = !isParteCollapseOpen"
                            :aria-expanded="isParteCollapseOpen.toString()"
                            aria-controls="partesCollapse"
                            style="min-height: 36px; font-size: 0.75rem;"
                        >
                            <i
                                class="fa-solid fa-chevron-down chevron-icon"
                                :class="{ rotated: isParteCollapseOpen }"
                                style="font-size: 0.75rem;"
                            ></i>
                            <span class="fw-semibold">Partes</span>
                        </button>
                    </div>

                    <!-- CONTENIDO COLAPSABLE DE SISTEMA-->
                    <transition name="collapse-smooth">
                        <div v-show="isParteCollapseOpen" id="partesCollapse">
                            <div class="row my-3" v-if="usuario.rol === 'tecnico'">
                                <div class="col-12 mb-md-0 mb-4">
                                    <MaterialTable :key="filteredPartesRenderKey" title="PARTES">
                                        <thead>
                                            <tr>
                                            <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-sm-start p-1">Fecha</th>
                                            <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-sm-start p-1">Nº Parte</th>
                                            <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-sm-start p-1">Fecha Expedición</th>
                                            <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-sm-start p-1">Técnico</th>
                                            <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-sm-center p-1">Hora Inicio</th>
                                            <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-sm-center p-1">Hora Fin</th>
                                            <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-sm-center p-1">Tiempo Total</th>
                                            <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-sm-center p-1">Total</th>
                                            <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-sm-start p-1">Editado Por</th>
                                            <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-center">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="parte in usuario.partes" :key="parte.id" role="button">
                                            <td class="text-xs text-sm-start">{{ formatDate(parte.created_at) }}</td>
                                            <td class="text-xs text-sm-start">{{ parte.num_parte }}</td>
                                            <td class="text-xs text-sm-start">{{ formatDateDMY(parte.fecha) }}</td>
                                            <td class="text-xs text-sm-start">{{ parte.user?.name || '-' }}</td>
                                            <td class="text-xs text-sm-center">
                                                <span v-if="parte.started_at">{{ formatTimeHM(parte.started_at) }}</span>
                                                <span v-else class="badge rounded-pill px-2 py-1" style="background-color: rgb(243 248 253); color: #494949; border: 1px solid #dee2e6; font-weight: 500;">
                                                Manual
                                                </span>
                                            </td>
                                            <td class="text-xs text-sm-center">{{ parte.ended_at ? formatTimeHM(parte.ended_at) : '-' }}</td>
                                            <td class="text-xs text-sm-center">{{ formatSeconds(parte.time_seconds) }}</td>
                                            <td class="text-xs text-sm-center">{{ formatCurrency(parte.total) }}</td>
                                            <td class="text-xs text-sm-start">{{ parte.edited_by || '-' }}</td>
                                            <td class="text-xs text-center">
                                                <a :href="route('partes.show', parte.id)"><i class="material-icons">visibility</i></a>
                                            </td>
                                            </tr>
                                        </tbody>
                                    </MaterialTable>
                                </div>
                            </div>
                        </div>
                    </transition>
                </div>
            </div>
        </div>

        <ResetPasswordByAdminModal :usuarioId="usuario.id" />
    </AuthenticatedLayout>
</template>

<script>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue';
import placeholder from '@/assets/img/placeholder.jpg';
import RecordChart from '@/Pages/Profile/sections/RecordChart.vue';
import RecordChartArrow from '@/Pages/Profile/sections/RecordChartArrow.vue';
import MaterialButton from '@/Components/MaterialButton.vue';
import ChartHolderCard from '@/views/components/ChartHolderCard.vue';
import ReportsBarChartHorario from '@/examples/Charts/ReportsBarChartHorario.vue';
import ResetPasswordByAdminModal from '@/Pages/Profile/sections/ResetPasswordByAdminModal.vue';
import MaterialTable from "@/Components/MaterialTableGeneral.vue";

export default {
    components: {
        AuthenticatedLayout,
        Breadcrumb,
        RecordChart,
        RecordChartArrow,
        MaterialButton,
        ChartHolderCard,
        ReportsBarChartHorario,
        ResetPasswordByAdminModal,
        MaterialTable,
    },
    props: {
        user: { type: Object, required: true, },
        usuario: { type: Object, required: true, },
        hoursWorkedToday: { type: Number,  required: true, },
        hoursWorkedThisWeek: { type: Number, required: true, },
        dailyLast7Days: { type: Array, required: true, },
        dailyChartData: { type: Array, required: true },
    },
    data() {
        return {
            breadcrumbItems: [
                { label: 'Dashboard', url: this.route('dashboard') },
                { label: 'Usuarios', url: this.route('user.index') },
                { label: `${this.usuario.name}`},
            ],
            placeholder,
            chartData: {
                labels: [],
                data: []
            },
            isParteCollapseOpen: false,
        };
    },
    computed: {
    },
    mounted() {
        this.prepareChartData();
    },
    methods: {
        prepareChartData() {
            const dias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];

            // Mapa auxiliar para acceder rápido por nombre de día
            const dataMap = this.dailyChartData.reduce((map, d) => {
                map[d.day] = d;
                return map;
            }, {});

            // Asegura que estén todos los días en orden, aunque no tengan datos
            this.chartData.labels = dias;
            this.chartData.data = dias.map(day => {
                const record = dataMap[day];
                return record ? record.workedHours : 0;
            });
            this.chartData.pause = dias.map(day => {
                const record = dataMap[day];
                return record ? +(record.pauseSeconds / 3600).toFixed(2) : 0;
            });
        },
        calculateTotalTime(group) {
            const totalMinutes = group.reduce((total, parte) => {
                if (!parte.tiempo || !parte.tiempo.includes(':')) return total;
                const [hours, minutes] = parte.tiempo.split(':').map(Number);
                return total + (hours * 60 + minutes);
            }, 0);

            const hours = Math.floor(totalMinutes / 60);
            const minutes = totalMinutes % 60;
            return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}`;
        },
        horasDiariasEsperadas() {
            const dias = this.usuario.dias_laborables ?? 5;
            return dias > 0 ? this.usuario.horario_semanal / dias : 8;
        },
        horasSemanalesEsperadas() {
            const horas = parseFloat(this.usuario.horario_semanal ?? 40);
            return Number.isInteger(horas) ? horas : parseFloat(horas.toFixed(2).replace(/\.?0+$/, ''));
        },
        formatCurrency(amount) {
            return new Intl.NumberFormat('es-ES', {
                style: 'currency',
                currency: 'EUR',
                minimumFractionDigits: 2
            }).format(amount);
        },
        formatDate(dateString) {
            const options = { year: 'numeric', month: 'long', day: 'numeric' };
            return new Date(dateString).toLocaleDateString('es-ES', options);
        },
        formatDateDMY(dateString) {
            if (!dateString) return '';
            const date = new Date(dateString);
            const day = String(date.getDate()).padStart(2, '0');
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const year = date.getFullYear();
            return `${day}-${month}-${year}`;
        },
        formatTimeHM(datetime) {
            if (!datetime) return '';
            const date = new Date(datetime);
            const hours = String(date.getHours()).padStart(2, '0');
            const minutes = String(date.getMinutes()).padStart(2, '0');
            return `${hours}:${minutes}`;
        },
        formatSeconds(seconds) {
            if (!seconds || isNaN(seconds)) return '-';

            const h = String(Math.floor(seconds / 3600)).padStart(2, '0');
            const m = String(Math.floor((seconds % 3600) / 60)).padStart(2, '0');
            const s = String(seconds % 60).padStart(2, '0');

            return `${h}:${m}:${s}`;
        },
        getArchivoUrl(archivo) {
            if (archivo !== null) {
                if (typeof archivo === 'string') {
                    const modifiedArchivo = archivo.replace("public/", ""); // Remove 'public/' segment
                    return "/storage/" + modifiedArchivo;
                } else if (archivo instanceof File) {
                    return URL.createObjectURL(archivo);
                } else {
                    console.error("Invalid file type");
                    return this.placeholder; // Return placeholder in case of an invalid file type
                }
            } else {
                return this.placeholder; // Return placeholder when archivo is null
            }
        },
        toggleChart(direction) {
            if (direction === 'prev' || direction === 'next') {
                this.currentMobileChart = this.currentMobileChart === 'day' ? 'week' : 'day';
            }
        },
        filteredPartes() {
            return this.usuario.partes.sort((a, b) => new Date(b.fecha) - new Date(a.fecha));
        }
    },
};
</script>

<style scoped>
/* Transición más suave y sin salto */
.collapse-smooth-enter-active,
.collapse-smooth-leave-active {
    max-height: 1000px;
    transition: max-height 0.6s ease, opacity 0.6s ease;
    overflow: hidden;
}

.collapse-smooth-enter-from,
.collapse-smooth-leave-to {
    max-height: 0;
    opacity: 0;
}

.collapse-smooth-enter-to,
.collapse-smooth-leave-from {
    max-height: 1000px;
    opacity: 1;
}

button:focus {
    box-shadow: none !important;
    outline: none !important;
}

label {
    line-height: 1 !important;
    font-size: 0.75rem !important;
}


@media (max-width: 767px) {
    .toggle-btn {
        padding: 8px 10px;
        font-size: 1rem;
    }
}

.min-width-card {
    min-width: 320px;
    flex: 1 1 320px;
}

.small-text-responsive {
    font-size: 0.8rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.user-card .card-block .user-image {
    position: relative;
    margin: 0 auto;
    display: inline-block;
    padding: 5px;
    width: 110px;
    height: 110px;
}

.user-card .card-block .user-image img {
    position: absolute;
    top: 5px;
    left: 5px;
    width: 100px;
    height: 100px;
}

.img-radius {
    border-radius: 50%;
}

.blinking-green {
    animation: blink-green 1.5s steps(1, start) infinite;
}

@keyframes blink-green {
    50% {
        opacity: 0;
    }
}

/* Botón individual */
.toggle-btn {
    background: transparent;
    border: none;
    padding: 4px 6px;
    border-radius: 4px;
    color: #ccc; /* tono claro inicial */
    font-size: 0.75rem;
    transition: transform 0.2s ease, color 0.2s ease, background-color 0.2s ease;
}

.toggle-btn:hover {
    background-color: rgba(0, 0, 0, 0.05);
    transform: scale(1.1);
    color: #333; /* más oscuro al pasar el ratón */
}

.toggle-btn i {
    pointer-events: none;
}

.d-show-below-xl {
    display: block !important;
}

@media (min-width: 1200px) {
    .d-show-below-xl {
        display: none !important;
    }
}

</style>
