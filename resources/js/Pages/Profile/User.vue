<template>
    <AuthenticatedLayout :user="user" :title="'MI ZONA'">
        <!-- Main content -->
        <div class="container-fluid overflow-hidden">
            <div class="row">
                <!-- ... contenido perfil ... -->
                <div class="col-12 col-lg-3 order-1">
                    <div class="card user-card shadow-sm border-0">
                        <img
                            :src="getArchivoUrl(user.img)" class="card-img-top rounded-circle mx-auto mt-3"
                            style="width: 180px; height: 180px; object-fit: cover;" alt="Profile"
                        />

                        <div class="card-block text-center p-3">
                            <h5 class="mt-1 mb-2 fw-semibold text-gradient-unique">{{ user.name }}</h5>
                            <h6 class="mb-2 small fst-italic mb-2 text-gradient-secondary">{{ user.email }}</h6>

                            <ul class="list-unstyled text-start small ps-3">
                                <li><i class="fas fa-phone me-2 text-secondary"></i>{{ user.telefono }}</li>
                                <li><i class="fas fa-id-badge me-2 text-secondary"></i>Nº Seguridad: {{ user.num_seguridad }}</li>
                                <li><i class="fas fa-birthday-cake me-2 text-secondary"></i>Nacimiento: {{ formatDate(user.fecha_nacimiento) }}</li>
                                <li><i class="fas fa-calendar-alt me-2 text-secondary"></i>Alta: {{ formatDate(user.fecha_alta) }}</li>
                                <li><i class="fas fa-map-marker-alt me-2 text-secondary"></i>{{ user.localidad }}, {{ user.direccion }}, {{ user.cp }}</li>
                            </ul>

                            <hr class="my-2" />

                            <div class="row text-center small">
                                <div class="col-xl-4 col-4 border-end py-2">
                                    <div class="text-muted">Rol</div>
                                    <div class="fw-semibold" style="font-size: 0.8rem;">{{ user.rol }}</div>
                                </div>
                                <div class="col-xl-4 col-4 border-end  py-2">
                                    <div class="text-muted">Puesto</div>
                                    <div class="fw-semibold" style="font-size: 0.8rem;">{{ user.position }}</div>
                                </div>
                                <div class="col-xl-4  col-4 py-2">
                                    <div class="text-muted">DNI</div>
                                    <div class="fw-semibold" style="font-size: 0.8rem;">{{ user.dni }}</div>
                                </div>
                            </div>

                            <hr class="my-2" />

                            <div class="text-center small text-muted mt-2">
                                Última aparición:
                                <span :class="{
                                    'blinking-green text-success': user.last_appearance === 'activo',
                                    'text-warning': user.last_appearance !== 'activo'
                                }">●</span> {{ user.last_appearance }}
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-1">
                        <a class="btn my-3 unique_bg btn-sm" data-bs-toggle="modal"
                            data-bs-target="#passwordModal">
                            <div class="text-center">Cambiar Contraseña</div>
                        </a>
                    </div>
                </div>

                <!-- ... contenido interno ... -->
                <div class="col-12 col-lg-9 order-2">
                        <!-- Escritorio y tablet: tres columnas -->
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

        <!-- Modals -->
        <ResetPasswordModal :user="user" />
    </AuthenticatedLayout>
</template>

<script>
import ResetPasswordModal from './sections/ResetPasswordModal.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import placeholder from '@/assets/img/placeholder.jpg';
import RecordChart from './sections/RecordChart.vue';
import RecordChartArrow from './sections/RecordChartArrow.vue';
import ChartHolderCard from '@/views/components/ChartHolderCard.vue';
import ReportsBarChartHorario from '@/examples/Charts/ReportsBarChartHorario.vue';
import UserList from '../Cruds/RRHH/TimeOff/UserList.vue';

export default {
    components: {
        ResetPasswordModal,
        AuthenticatedLayout,
        RecordChart,
        RecordChartArrow,
        ChartHolderCard,
        ReportsBarChartHorario,
        UserList
    },
    props: {
        user: { type: Object, required: true, },
        hoursWorkedToday: { type: Number, required: true,  },
        hoursWorkedThisWeek: { type: Number, required: true, },
        dailyLast7Days: { type: Array, required: true, },
        dailyChartData: { type: Array, required: true },
    },
    data() {
        return {
            currentMobileChart: 'day',
            placeholder,
            chartData: {
                labels: [],
                data: []
            }
        };
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
        formatDate(dateString) {
            const options = { year: 'numeric', month: 'long', day: 'numeric' };
            return new Date(dateString).toLocaleDateString('es-ES', options);
        },
        horasDiariasEsperadas() {
            const dias = this.user.dias_laborables ?? 5;
            return dias > 0 ? this.user.horario_semanal / dias : 8;
        },
        horasSemanalesEsperadas() {
            const horas = parseFloat(this.user.horario_semanal ?? 40);
            return Number.isInteger(horas) ? horas : parseFloat(horas.toFixed(2).replace(/\.?0+$/, ''));
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
    },
};
</script>

<style scoped>
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
