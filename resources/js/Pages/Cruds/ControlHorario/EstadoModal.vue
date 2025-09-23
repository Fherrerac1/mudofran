<template>
    <BootstrapModal :id="'estadoModal'" :ModalSize="'modal-xl'" :title="'Control Horario'">
        <div class="row text-secondary">
            <!-- ======================== INFO EXTRA ======================== -->
            <div class="info-extra d-flex justify-content-center align-items-center flex-wrap gap-2 mb-1">
                <!-- Fecha -->
                <div class="info-item d-flex align-items-center px-3 py-1 rounded-pill">
                    <span class="material-icons-outlined me-1">today</span>
                    <span class="fw-medium">{{ formatDate(horario?.created_at) || 'Fecha no disponible' }}</span>
                </div>

                <!-- Usuario -->
                <div class="info-item d-flex align-items-center px-3 py-1 rounded-pill">
                    <span class="material-icons-outlined me-1">person</span>
                    <span class="fw-medium">{{ horario?.user?.name || 'Sin usuario' }}</span>
                </div>
            </div>

            <!-- ======================== BLOQUE VISUAL ======================== -->
            <div class="text-center mt-2">
                <!-- Jornada y tiempo -->
                <div class="d-flex justify-content-center align-items-center flex-wrap">
                    <div
                        class="bloque-jornada d-flex justify-content-center align-items-center flex-wrap text-center p-2">
                        <span class="material-icons-outlined me-2 text-primary">timer</span>
                        <span class="fw-bold me-1">
                            {{ horario?.start }} - {{ horario?.out || 'En Curso' }}
                        </span>

                        <span v-if="horario?.out" class="badge rounded-pill bg-light text-secondary ms-2">
                            {{ horario?.total || '--:--' }}
                        </span>
                    </div>
                </div>

                <!-- Botón Editar Jornada -->
                <div v-if="horario?.estado === 0 && !EditMode" class="mb-2">
                    <a role="button" class="editar-pill" @click="toggleEditMode">
                        Editar Jornada
                    </a>
                </div>

                <!-- ======================== FORMULARIO EDITAR JORNADA ======================== -->
                <form v-if="EditMode" enctype="multipart/form-data" @submit.prevent="updateHorario"
                    class="text-center form-editar">
                    <div class="d-flex justify-content-center">
                        <div class="row">
                            <div>
                                <MaterialInput label="Inicio" id="time_in" type="time" v-model="horario.time_in" />
                                <MaterialInput label="Final" id="time_out" type="time" v-model="horario.time_out" />
                            </div>
                            <div>
                                <button class="btn btn-sm btn-gradient-unique mt-3 me-3" type="submit">
                                    Actualizar
                                </button>
                                <button class="btn-cancelar me-3" type="button" @click="toggleEditMode">
                                    Cancelar
                                </button>
                            </div>
                        </div>
                    </div>
                </form>

                <!-- BLOQUE COMIDA -->
                <div v-if="horario?.meal_started_at" class="d-flex justify-content-center align-items-center flex-wrap">
                    <div
                        class="bloque-comida d-flex justify-content-center align-items-center flex-wrap text-center p-2">
                        <span class="material-icons-outlined me-2 text-success">restaurant</span>
                        <span class="fw-bold me-1">
                            {{ formatTimeHM(horario.meal_started_at) || '--:--' }}
                        </span>

                        <span class="badge rounded-pill bg-light text-secondary ms-2">
                            {{ formatSeconds(horario.meal_total_seconds) || '--:--' }}
                        </span>
                    </div>
                </div>

                <!-- Botón Editar Comida -->
                <div v-if="horario?.meal_started_at && !EditMealMode && (horario?.estado === 0 || horario?.estado === 1)"
                    class="text-center mb-2">
                    <a role="button" class="editar-pill" @click="openEditMeal">
                        Editar Comida
                    </a>
                </div>
            </div>

            <!-- ======================== FORMULARIO EDITAR COMIDA ======================== -->
            <form v-if="EditMealMode" enctype="multipart/form-data" @submit.prevent="updateMeal"
                class="text-center mb-3 form-editar">
                <div class="d-flex justify-content-center">
                    <div class="row">
                        <div>
                            <MaterialInput label="Inicio Comida" id="meal_started_at" type="time"
                                v-model="mealForm.meal_started_at" />
                            <MaterialInput label="Duración Comida (HH:MM)" id="meal_duration" type="time"
                                v-model="mealForm.meal_duration" />
                        </div>

                        <div class="mt-2">
                            <button class="btn btn-sm btn-gradient-unique mt-3 me-3" type="submit">
                                Actualizar Comida
                            </button>

                            <button class="btn btn-sm btn-danger mt-3 me-3" type="button" @click="deleteMeal">
                                Eliminar Comida
                            </button>
                            <button class="btn-cancelar me-3" type="button" @click="EditMealMode = false">
                                Cancelar
                            </button>

                        </div>
                    </div>
                </div>
            </form>

            <!-- ======================== MAPA ======================== -->
            <div class="row mt-0 g-3">
                <!-- Mapa Entrada -->
                <div class="col-md-6 text-center">
                    <h6 class="map-title">Ubicación Entrada</h6>
                    <div v-if="hasCoordinatesIn" style="height: 250px; width: 100%;" class="map-wrapper">
                        <l-map ref="mapIn" v-model:zoom="zoom" :center="mapCenterIn">
                            <l-tile-layer url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png" layer-type="base"
                                name="OpenStreetMap" />
                            <l-marker :lat-lng="mapCenterIn" />
                        </l-map>
                    </div>
                    <div v-else>Sin coordenadas de entrada</div>
                </div>

                <!-- Mapa Salida -->
                <div class="col-md-6 text-center">
                    <h6 class="map-title">Ubicación Salida</h6>
                    <div v-if="hasCoordinatesOut" style="height: 250px; width: 100%;" class="map-wrapper">
                        <l-map ref="mapOut" v-model:zoom="zoom" :center="mapCenterOut">
                            <l-tile-layer url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png" layer-type="base"
                                name="OpenStreetMap" />
                            <l-marker :lat-lng="mapCenterOut" />
                        </l-map>
                    </div>
                    <div v-else>Sin coordenadas de salida</div>
                </div>
            </div>

            <hr class="mt-4" />

            <!-- ======================== OBSERVACIONES ======================== -->
            <div class="mb-3">
                <h5 class="fw-bold text-start text-uppercase  my-3">
                    Observaciones
                </h5>

                <template v-if="observacionesParsed">
                    <div class="card border-0 shadow-sm p-3 py-5" style="border: 1px solid #928a8a61 !important;">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h5 class="fw-bold mb-0">{{ observacionesParsed.titulo }}</h5>
                            <div class="d-flex align-items-center text-muted small f-register-ob">
                                <small class="material-icons-outlined me-1" style="font-size: 1.1rem;">event</small>
                                <span>Registrado el {{ formatDate(observacionesParsed.fecha) }}</span>
                            </div>

                        </div>
                        <p class="text-muted mb-0">{{ observacionesParsed.descripcion }}</p>
                    </div>
                </template>

                <template v-else>
                    <div class="alert alert-light border rounded py-2 text-center">
                        <span class="text-muted">No existen observaciones registradas para esta fecha.</span>
                    </div>
                </template>
            </div>


            <!-- ======================== ACTIONS ======================== -->
            <div class="d-flex justify-content-end">
                <button v-if="!EditMode" @click="confirmDelete(horario.id)" class="btn btn-danger me-2">
                    Eliminar
                </button>

                <!-- Botón Finalizar Jornada -->
                <button v-if="horario?.estado === 1 && !EditMode && !horario?.out" @click="finishHorario(horario.id)"
                    class="btn btn-info me-2">
                    Finalizar Jornada
                </button>

                <button v-if="horario?.estado === 0 && !EditMode" @click="approveHorario(horario.id)"
                    class="btn btn-success">
                    Aprobar
                </button>
            </div>
        </div>
    </BootstrapModal>
</template>

<script>
import Swal from 'sweetalert2'
import axios from 'axios'
import L from 'leaflet'
import "leaflet/dist/leaflet.css"
import { LMap, LTileLayer, LMarker } from "@vue-leaflet/vue-leaflet"

import MaterialInput from '@/Components/MaterialInput.vue'
import BootstrapModal from '@/Components/BootstrapModal.vue'
import MaterialButton from '@/Components/MaterialButton.vue'

import markerIcon from 'leaflet/dist/images/marker-icon.png'
import markerShadow from 'leaflet/dist/images/marker-shadow.png'

// Fix Leaflet default marker
delete L.Icon.Default.prototype._getIconUrl
L.Icon.Default.mergeOptions({
    iconUrl: markerIcon,
    shadowUrl: markerShadow
})

export default {
    components: {
        MaterialInput,
        BootstrapModal,
        MaterialButton,
        LMap,
        LTileLayer,
        LMarker
    },
    props: {
        horario: {
            type: Object,
            required: false,
        },
    },
    data() {
        return {
            EditMode: false,
            EditMealMode: false,
            zoom: 30,
            mealForm: {
                meal_started_at: '', // HH:mm
                meal_duration: '',   // HH:mm (se convertirá a segundos)
            },
        }
    },
    mounted() {
        this.$nextTick(() => {
            const modal = document.getElementById('estadoModal');
            if (modal) {
                modal.addEventListener('shown.bs.modal', () => {
                    setTimeout(() => {
                        this.invalidateMapSize();
                    }, 300);
                });
            }
        });
    },
    computed: {
        hasCoordinates() {
            return this.horario?.latitude != null && this.horario?.longitude != null
        },
        mapCenter() {
            const lat = this.horario?.latitude
            const lng = this.horario?.longitude
            return [lat, lng];
        },
        hasCoordinatesIn() {
            return this.horario?.latitude != null && this.horario?.longitude != null
        },
        hasCoordinatesOut() {
            return this.horario?.latitude_out != null && this.horario?.longitude_out != null
        },
        mapCenterIn() {
            return [this.horario?.latitude, this.horario?.longitude]
        },
        mapCenterOut() {
            return [this.horario?.latitude_out, this.horario?.longitude_out]
        },
        observacionesParsed() {
            try {
                return this.horario?.observaciones
                    ? JSON.parse(this.horario.observaciones)
                    : null;
            } catch (e) {
                console.error("Error al parsear observaciones:", e);
                return null;
            }
        }


    },
    methods: {
        invalidateMapSize() {
            if (this.$refs.mapIn && this.$refs.mapIn.leafletObject) {
                this.$refs.mapIn.leafletObject.invalidateSize();
            }
            if (this.$refs.mapOut && this.$refs.mapOut.leafletObject) {
                this.$refs.mapOut.leafletObject.invalidateSize();
            }
        },
        formatDate(dateString) {
            if (!dateString) return null
            const options = { year: 'numeric', month: 'long', day: 'numeric' }
            return new Date(dateString).toLocaleDateString('es-ES', options)
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
        secondsToHM(seconds) {
            if (!seconds) return '00:00'
            const h = String(Math.floor(seconds / 3600)).padStart(2, '0')
            const m = String(Math.floor((seconds % 3600) / 60)).padStart(2, '0')
            return `${h}:${m}`
        },
        HMToSeconds(HM) {
            if (!HM) return 0
            const [h, m] = HM.split(':').map(Number)
            return (h * 3600) + (m * 60)
        },
        toggleEditMode() {
            this.EditMealMode = false
            this.EditMode = !this.EditMode
        },
        openEditMeal() {
            this.mealForm.meal_started_at = this.formatTimeHM(this.horario.meal_started_at)
            this.mealForm.meal_duration = this.secondsToHM(this.horario.meal_total_seconds)
            this.EditMealMode = true
        },
        confirmDelete(id) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: '¡No podrás revertir esta acción!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, eliminarlo',
                cancelButtonText: 'Cancelar'
            }).then(result => {
                if (result.isConfirmed) {
                    this.deleteHorario(id)
                }
            })
        },

        deleteHorario(id) {
            axios.get(`/horario/${id}/delete`)
                .then(() => {
                    Swal.fire('Eliminado!', 'El Horario ha sido eliminado.', 'success')
                        .then(() => window.location.reload())
                })
                .catch(() => {
                    Swal.fire('Error!', 'Hubo un problema al eliminar el Horario.', 'error')
                })
        },

        updateHorario() {
            axios.post(route('record.update', this.horario?.id), this.horario)
                .then(response => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Horario actualizado',
                        text: response.data.message,
                    }).then(() => window.location.reload())
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: error.response?.data?.message || 'No se pudo actualizar el horario.',
                    })
                })
        },

        updateMeal() {
            // 👉 Obtén la fecha original (por si quieres conservar la parte de fecha)
            const originalDate = new Date(this.horario.meal_started_at);

            // 👉 Divide HH:mm del formulario
            const [hours, minutes] = this.mealForm.meal_started_at.split(':');

            // 👉 Combina con fecha existente
            originalDate.setHours(parseInt(hours));
            originalDate.setMinutes(parseInt(minutes));
            originalDate.setSeconds(0);

            // 👉 Formatea: YYYY-MM-DD HH:MM:SS en hora local (no UTC)
            const year = originalDate.getFullYear();
            const month = String(originalDate.getMonth() + 1).padStart(2, '0');
            const day = String(originalDate.getDate()).padStart(2, '0');
            const localTime = `${year}-${month}-${day} ${hours}:${minutes}:00`;

            const data = {
                meal_started_at: localTime, // ⚡️ Formato sin Z => sin UTC
                meal_total_seconds: this.HMToSeconds(this.mealForm.meal_duration)
            };

            axios.post(`/horario/${this.horario.id}/update-meal`, data)
                .then(() => {
                    Swal.fire('Guardado!', 'Los datos de comida se actualizaron.', 'success')
                        .then(() => window.location.reload());
                })
                .catch(() => {
                    Swal.fire('Error!', 'No se pudo actualizar la comida.', 'error');
                });
        },

        finishHorario(id) {
            Swal.fire({
                title: '¿Finalizar Jornada?',
                text: 'Se registrará la salida con la hora actual.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, finalizar',
                cancelButtonText: 'Cancelar'
            }).then(result => {
                if (result.isConfirmed) {
                    axios.post(`/horario/${id}/finalizarJornada`)
                        .then(response => {
                            const data = response.data;
                            Swal.fire({
                                icon: 'success',
                                title: '¡Jornada Finalizada!',
                                html: `
                        <p><strong>Hora de entrada:</strong> ${data.time_in || '--:--'}</p>
                        <p><strong>Hora de salida:</strong> ${data.time_out || '--:--'}</p>`
                            }).then(() => window.location.reload());
                        })
                        .catch(() => {
                            Swal.fire('Error!', 'No se pudo finalizar la jornada.', 'error');
                        });
                }
            });
        },

        deleteMeal() {
            Swal.fire({
                title: '¿Eliminar Comida?',
                text: 'Se eliminarán los datos de la pausa de comida.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
            }).then(result => {
                if (result.isConfirmed) {
                    axios.post(`/horario/${this.horario.id}/delete-meal`)
                        .then(() => {
                            Swal.fire('Eliminado!', 'La comida ha sido eliminada.', 'success')
                                .then(() => window.location.reload())
                        })
                        .catch(() => {
                            Swal.fire('Error!', 'No se pudo eliminar la comida.', 'error')
                        })
                }
            })
        },

        approveHorario(id) {
            Swal.fire({
                title: '¿Estás seguro de aprobar este horario?',
                icon: 'info',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, aprobar',
                cancelButtonText: 'Cancelar'
            }).then(result => {
                if (result.isConfirmed) {
                    this.approveHorarioRequest(id)
                }
            })
        },

        approveHorarioRequest(id) {
            axios.post(`/horario/${id}/approve`)
                .then(() => {
                    Swal.fire('Aprobado!', 'El Horario ha sido aprobado.', 'success')
                        .then(() => window.location.reload())
                })
                .catch(() => {
                    Swal.fire('Error!', 'Hubo un problema al aprobar el Horario.', 'error')
                })
        },
    }
}
</script>

<style scoped>
.info-extra .info-item {
    background: #f8f9fa;
    /* gris claro neutro */
    color: #333;
    /* texto más oscuro */
    font-size: 0.92rem;
    border: 1px solid #e4e6eb;
}

.info-extra .material-icons-outlined {
    font-size: 1rem;
    color: #555;
    /* icono gris oscuro */
}

.info-extra .info-item:hover {
    background: #f1f3f5;
}

.form-editar {
    background-color: #fdfdfd;
    /* Fondo blanco casi total */
    border: 1.5px dashed #ccc;
    /* Borde dashed suave */
    padding: 1.2rem 2rem;
    /* Espacio contenido */
    border-radius: 12px;
    /* Esquinas suaves */
    max-width: 600px;
    /* No se expande demasiado */
    margin: 1.5rem auto;
    /* Centrado */
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
    /* Sombra ligera */
    transition: all 0.3s ease;
}

.form-editar:hover {
    background-color: #f9f9f9;
    /* Ligeramente más oscuro al pasar el cursor */
    border-color: #bbb;
    /* Tono más visible */
}

.editar-pill {
    color: #007bff;
    /* Azul Bootstrap */
    font-weight: 600;
    padding: 0.35rem 0.9rem;
    font-size: 0.92rem;
    text-decoration: none;
    transition: all 0.2s ease;

}

.editar-pill:hover {
    color: #0056b3;
    text-decoration: none;
}

.map-wrapper {
    height: 250px;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
}

.map-title {
    font-weight: 600;
    font-size: 0.95rem;
    margin-bottom: 0.5rem;
}

.f-register-ob {
    position: absolute;
    top: 0;
    right: 0;
    padding: 1rem;
}
</style>
