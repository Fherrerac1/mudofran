<template>
    <!-- Botón de inicio centrado -->
    <div v-if="!isClockedIn" class="d-flex justify-content-center px-2">
        <button ref="ficharButton" class="btn unique_bg" style="min-width: 180px; max-width: 220px;" @click="fichar"
            :disabled="hasFinalizadoHoy"
            :title="hasFinalizadoHoy ? 'Ya has finalizado tu jornada hoy' : 'Iniciar jornada'">
            <i class="material-icons">play_arrow</i>
            <span>Inicio Jornada</span>
        </button>

    </div>

    <!-- Bloque principal de jornada -->
    <div v-if="isClockedIn" class="px-2">
        <!-- Tiempo y botón de finalizar -->
        <div class="d-flex justify-content-center">
            <div class="d-inline-flex align-items-center bg-dark rounded px-3 pe-2 shadow-sm" style="gap: 5px;">
                <span class="text-white fw-semibold" style="font-size: 0.9rem;">
                    {{ elapsedTime }}
                </span>
                <!-- Botón de finalizar jornada -->
                <div class="d-flex align-items-center justify-content-center bg-dark"
                    :class="{ 'opacity-25': isPaused || isMealPaused }"
                    style="border-radius: 15px; padding: 3px; cursor: pointer;" :title="isPaused
                        ? 'No disponible durante la pausa'
                        : isMealPaused
                            ? 'No disponible durante la comida'
                            : 'Finalizar jornada'" :data-bs-toggle="(!isPaused && !isMealPaused) ? 'modal' : null"
                    :data-bs-target="(!isPaused && !isMealPaused) ? '#confirmarFinalizarJornada' : null">
                    <i class="material-icons text-danger" style="font-size: 28px;">
                        stop
                    </i>
                </div>
            </div>
        </div>

        <!-- Botones de pausa -->
        <div class="mt-3 d-flex flex-wrap justify-content-center" style="gap: 12px;">
            <div class="d-flex justify-content-center" style="flex: 0 0 calc(33.333% - 8px);">
                <div class="d-flex align-items-center justify-content-center bg-dark"
                    :class="{ 'opacity-25': isMealPaused }"
                    style="border-radius: 10px; padding: 10px; width: 100%; cursor: pointer;"
                    :title="isMealPaused ? 'No disponible durante la comida' : (isPaused ? 'Reanudar pausa' : 'Pausar')"
                    :data-bs-toggle="(!isPaused && !isMealPaused) ? 'modal' : null"
                    :data-bs-target="(!isPaused && !isMealPaused) ? '#confirmarPausa' : null"
                    @click.prevent="(!isMealPaused && isPaused) && togglePause()">
                    <i class="material-icons fs-5" style="color: #F59E0B;">
                        {{ isPaused ? 'stop' : 'free_breakfast' }}
                    </i>
                </div>
            </div>

            <div class="d-flex justify-content-center" style="flex: 0 0 calc(33.333% - 8px);">
                <div class="d-flex align-items-center justify-content-center bg-dark"
                    :class="{ 'opacity-25': isPaused }"
                    style="border-radius: 10px; padding: 10px; width: 100%; cursor: pointer;" :title="isPaused
                        ? 'No disponible durante la pausa'
                        : isMealFinished
                            ? 'Comida finalizada'
                            : isMealPaused
                                ? 'Reanudar comida'
                                : 'Iniciar comida'"
                    :data-bs-toggle="(!isMealPaused && !isMealFinished && !isPaused) ? 'modal' : null"
                    :data-bs-target="(!isMealPaused && !isMealFinished && !isPaused) ? '#confirmarComida' : null"
                    @click.prevent="(!isMealFinished && !isPaused && isMealPaused) && toggleComida()">
                    <i class="material-icons fs-5" style="color: #3B82F6;">
                        {{ isMealFinished ? 'check' : (isMealPaused ? 'stop' : 'restaurant') }}
                    </i>
                </div>
            </div>
        </div>

        <!-- Info de inicio -->
        <div v-if="startTime" class="pt-3 px-2">
            <div class="d-flex align-items-center justify-content-between bg-dark px-3 py-2 rounded shadow-sm"
                style="min-width: 220px; border-left: 4px solid #0dcaf0; font-size: 0.85rem;">
                <span class="text-white-50 fw-semibold">Inicio jornada:</span>
                <span class="fw-semibold">{{ formatTime(startTime) }}</span>
            </div>
        </div>

        <!-- Info de pausa -->
        <div v-if="isPaused || basePauseSeconds > 0" class="pt-2 px-2">
            <div class="d-flex align-items-center justify-content-between bg-dark px-3 py-2 rounded shadow-sm"
                style="min-width: 220px; border-left: 4px solid #f59e0b; font-size: 0.85rem;">
                <span class="text-white-50 fw-semibold">Pausa:</span>
                <span class="fw-semibold">{{ pauseElapsed }}</span>
            </div>
        </div>

        <!-- Info de comida -->
        <div v-if="isMealPaused || baseMealSeconds > 0" class="pt-2 px-2">
            <div class="d-flex align-items-center justify-content-between bg-dark px-3 py-2 rounded shadow-sm"
                style="min-width: 220px; border-left: 4px solid #3B82F6; font-size: 0.85rem;">
                <div class="text-white-50 fw-semibold">Comida:</div>
                <div class="text-end">
                    <div class="fw-semibold">{{ mealElapsed }}</div>
                    <div v-if="mealStartedAt" class="text-white-50 small">
                        desde {{ formatDateTime(mealStartedAt) }}
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Modal Finalizar Jornada -->
    <Teleport to="body">
        <BootstrapModal :ModalId="'confirmarFinalizarJornada'" :ModalSize="'modal-sm modal-dialog-centered'"
            :title="'Finalizar Jornada'">
            <div class="text-center">
                <p class="mb-3 fw-medium" style="font-size: 1rem;">
                    ¿Seguro que quieres <strong>finalizar la jornada</strong>?
                </p>

                <div class="d-flex justify-content-center gap-2">
                    <button class="btn btn-sm btn-light border" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button class="btn btn-sm btn-danger" @click="confirmarFinalizarJornada" data-bs-dismiss="modal">
                        Sí, finalizar
                    </button>
                </div>
            </div>
        </BootstrapModal>

        <!-- Modal Confirmar Pausa -->
        <BootstrapModal :ModalId="'confirmarPausa'" :ModalSize="'modal-sm modal-dialog-centered'" :title="'Pausa'">
            <div class="text-center">
                <p class="mb-3 fw-medium" style="font-size: 1rem;">
                    ¿Seguro que quieres <strong>{{ isPaused ? 'reanudar la jornada' : 'iniciar una pausa' }}</strong>?
                </p>

                <div class="d-flex justify-content-center gap-2">
                    <button class="btn btn-sm btn-light border" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button class="btn btn-sm btn-warning" @click="confirmarPausa" data-bs-dismiss="modal">
                        Sí, {{ isPaused ? 'reanudar' : 'pausar' }}
                    </button>
                </div>
            </div>
        </BootstrapModal>

        <!-- Modal Confirmar Comida -->
        <BootstrapModal :ModalId="'confirmarComida'" :ModalSize="'modal-sm modal-dialog-centered'" :title="'Comida'">
            <div class="text-center px-3 pb-2">
                <p class="mb-4 fw-semibold" style="font-size: 0.95rem;">
                    ¿Seguro que quieres <strong>{{ isMealPaused ? 'reanudar la comida' : 'iniciar la comida'
                        }}</strong>?
                </p>

                <div class="d-flex justify-content-center gap-2">
                    <button class="btn btn-sm btn-outline-secondary px-3 text-uppercase" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button class="btn btn-sm px-3 text-white" :class="isMealPaused ? 'bg-success' : 'bg-danger'"
                        @click="confirmarComida" data-bs-dismiss="modal">
                        Sí, {{ isMealPaused ? 'reanudar' : 'pausar' }}
                    </button>
                </div>
            </div>
        </BootstrapModal>

        <!-- Modal Observaciones -->
        <BootstrapModal :ModalId="'modalObservaciones'" :ModalSize="'modal-md modal-dialog-centered'"
            :title="obsUI.title">
            <form @submit.prevent="enviarObservacion" class="p-2">
                <BaseInput id="obs_titulo" label="Título" placeholder="Ej: Pausa de Jornada" v-model="obsTitulo"
                    :required="true" :asterisk="true" :error="!!obsErrors.titulo" :errorMessage="obsErrors.titulo" />


                <!-- TEXTAREA -->
                <BaseInput id="obs_descripcion" label="Detalles" v-model="obsDescripcion" type="textarea" rows="6"
                    :required="true" :asterisk="true" :error="!!obsErrors.descripcion"
                    :errorMessage="obsErrors.descripcion" placeholder="Describe el motivo de tu observación..."
                    class="mt-3" />


                <div class="d-flex justify-content-end gap-2 mt-3">
                    <MaterialButton type="submit" class="align-items-center gap-2 w-100 text-center">
                        <div class="d-flex justify-content-center align-items-center gap-2">
                            <i class="material-icons" style="font-size: 18px;">save</i>
                            <span>{{ obsUI.submitLabel }}</span>
                        </div>
                    </MaterialButton>
                    <!-- Botón oculto para cerrar el modal por ref -->
                    <button type="button" ref="cerrarObs" class="d-none" data-bs-dismiss="modal"></button>
                </div>
            </form>
        </BootstrapModal>


    </Teleport>

    <!-- Indicador de estado (excepto fuera de jornada) -->
    <div v-if="estadoActual !== 'Fuera de jornada'" class="my-2 px-3">
        <div class="bg-dark px-3 py-2 rounded shadow-sm" :style="{
            borderLeft: estadoActualColor.border,
            minWidth: '220px',
            fontSize: '0.85rem',
        }">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="text-white-50 fw-semibold">Estado:</span>
                <span class="fw-semibold" :style="{ color: estadoActualColor.text }">
                    {{ estadoActual }}
                </span>
            </div>

            <!-- Si la jornada ha finalizado, mostrar resumen -->
            <template v-if="hasFinalizadoHoy && startTime && timeOut">
                <div class="d-flex justify-content-between text-white-50 fw-semibold">
                    <span>Hora Inicio:</span>
                    <span class="text-white">{{ formatTime(startTime) }}</span>
                </div>
                <div class="d-flex justify-content-between text-white-50 fw-semibold mt-1">
                    <span>Hora Salida:</span>
                    <span class="text-white">{{ formatTime(timeOut) }}</span>
                </div>
                <div class="d-flex justify-content-between text-white-50 fw-semibold mt-1">
                    <span>Tiempo Efectivo:</span>
                    <span class="text-white">{{ tiempoTrabajado }}</span>
                </div>
                <div class="d-flex justify-content-between text-white-50 fw-semibold mt-1">
                    <span>Tiempo Pausa:</span>
                    <span class="text-white">{{ formatSeconds(basePauseSeconds) }}</span>
                </div>
            </template>

        </div>
    </div>


    <!-- Botón / tarjeta Observaciones (solo si jornada activa) -->
    <div v-if="isClockedIn" class="px-2 pb-2">
        <div class="bg-dark px-3 py-2 rounded shadow-sm mt-2 mx-auto" :style="{
            borderLeft: '4px solid #916fff',
            fontSize: '0.85rem',
            minWidth: '220px',
            maxWidth: '220px',
            cursor: 'pointer'
        }" @click="abrirModalObservaciones"
            :title="hasObservacion ? 'Editar observación de hoy' : 'Añadir observación'">
            <div class="d-flex justify-content-between align-items-center">
                <div class="text-white-50 fw-semibold d-flex align-items-center" style="gap: 6px;">
                    <span>Observaciones</span>
                </div>

                <!-- ✅ Ícono dinámico -->
                <i class="material-icons" style="font-size: 1rem;">
                    {{ hasObservacion ? 'edit' : 'add' }}
                </i>
            </div>
        </div>
    </div>


    <!-- Botón oculto para abrir/cerrar el modal por ref -->
    <button type="button" class="d-none" ref="abrirObs" data-bs-toggle="modal"
        data-bs-target="#modalObservaciones"></button>


    <!-- Botón de refrescar página solo cuando jornada activa -->
    <div v-if="isClockedIn && !hasFinalizadoHoy" class="mt-3 d-flex justify-content-center">
        <button class="btn d-flex align-items-center justify-content-center shadow-sm" style="
            background-color: transparent;
            border: 1px solid #4b5563;
            border-radius: 8px;
            color: #d1d5db;
            font-weight: 500;
            padding: 8px 20px;
            min-width: 160px;
            transition: all 0.2s ease-in-out;
            " @click="reloadPage" title="Recargar página" @mouseover="hover = true" @mouseleave="hover = false">
            <i class="fas fa-sync-alt me-2"></i>
            Refrescar
        </button>
    </div>
</template>

<script>
import axios from 'axios';
import BootstrapModal from '@/Components/BootstrapModal.vue';
import Swal from 'sweetalert2';
import MaterialInput from '@/Components/MaterialInput.vue';
import MaterialButton from '@/Components/MaterialButton.vue';
import BaseInput from '@/Components/Propios/BaseInput.vue';

export default {
    components: { BootstrapModal, MaterialInput, MaterialButton, BaseInput },
    props: {
        observacionHoy: { type: Object, default: null },
    },
    data() {
        return {
            hasFinalizadoHoy: false,
            startTime: null,
            timeOut: null,
            isClockedIn: 0,
            elapsedTime: '00:00:00',
            pollingInterval: null,
            gpsCoordinates: null,
            isPaused: false,
            isMealPaused: false,
            pauseStartTimestamp: null,
            pauseInterval: null,
            pauseElapsed: '00:00:00',
            basePauseSeconds: 0,
            baseElapsedTime: 0,
            isMealFinished: false,
            baseMealSeconds: 0,
            mealStartedAt: null,
            mealElapsed: '00:00:00',
            mealStartTimestamp: null,
            mealInterval: null,
            observacionActual: this.observacionHoy,
            obsTitulo: '', // Campo Observaciones - Titulo
            obsDescripcion: '', // Campo Observaciones - Descripción
            obsErrors: { titulo: null, descripcion: null },
            obsMode: 'new', // new, edit

        };
    },
    mounted() {
        this.fetchClockStatus();
    },
    watch: {
        isClockedIn(newVal) {
            if (newVal && !this.isPaused && !this.isMealPaused) {
                this.startPolling();
            } else {
                this.stopPolling();
            }
        },
        basePauseSeconds(newVal) {
            if (newVal > 0) {
                this.pauseElapsed = this.formatSeconds(newVal);
            }
        },
        observacionActual: {
            immediate: true,
            handler(val) {
                this.obsTitulo = val?.titulo ?? '';
                this.obsDescripcion = val?.descripcion ?? '';
                this.obsMode = (this.obsTitulo || this.obsDescripcion) ? 'edit' : 'new';
            }
        },
    },
    computed: {

        hasObservacion() {
            return !!(this.observacionActual?.titulo || this.observacionActual?.descripcion);
        },
        obsUI() {
            return {
                title: this.obsMode === 'edit' ? 'Editar observación' : 'Nueva observación',
                submitLabel: this.obsMode === 'edit' ? 'Guardar cambios' : 'Crear observación',
            };
        },
        estadoActual() {
            if (this.isPaused) return 'En Pausa';
            if (this.isMealPaused) return 'En Comida';
            if (this.isClockedIn) return 'En Jornada';
            if (this.hasFinalizadoHoy) return 'Jornada Finalizada';
            return 'Fuera de jornada';
        },
        estadoActualColor() {
            if (this.isPaused) {
                return { border: '4px solid #f59e0b', text: '#f59e0b' };
            }
            if (this.isMealPaused) {
                return { border: '4px solid #3B82F6', text: '#3B82F6' };
            }
            if (this.isClockedIn) {
                return { border: '4px solid #10B981', text: '#10B981' };
            }
            if (this.hasFinalizadoHoy) {
                return { border: '4px solid #6B7280', text: '#fff' };
            }
            return { border: '4px solid #1f2937', text: '#9ca3af' };
        },
        tiempoTrabajado() {
            const total = this.getSecondsFromTimes(this.startTime, this.timeOut)
            return this.formatSeconds(total - this.basePauseSeconds)
        },
    },
    methods: {
        formatTime(str) {
            if (!str) return '';
            const [h, m] = str.split(':');
            return `${h}:${m}`;
        },
        getLocation() {
            return new Promise((resolve, reject) => {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(
                        pos => {
                            this.gpsCoordinates = {
                                latitude: pos.coords.latitude,
                                longitude: pos.coords.longitude,
                            };
                            resolve(this.gpsCoordinates);
                        },
                        err => {
                            alert('GPS location is required to fichar.');
                            reject(err);
                        }
                    );
                } else {
                    alert('Geolocation is not supported by this browser.');
                    reject();
                }
            });
        },
        async fichar() {
            try {
                await this.getLocation();
                if (!this.gpsCoordinates) return;

                const response = await axios.post('/fichar', this.gpsCoordinates);
                const msg = response.data.message;

                if (msg === 'ENTRADA FICHADA') {
                    this.isClockedIn = 1;
                    this.isPaused = false;
                    this.isMealPaused = false;
                    this.isMealFinished = false;
                    this.basePauseSeconds = 0;
                    this.baseMealSeconds = 0;
                    this.pauseElapsed = '00:00:00';
                    this.mealElapsed = '00:00:00';

                    this.clearStorage([
                        'fichaje_paused_time',
                        'pause_start_timestamp',
                        'fichaje_elapsed_time',
                        'meal_elapsed_backup',
                        'meal_start_timestamp'
                    ]);

                    this.startTime = response.data.time_in || new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                }
                else if (msg === 'SALIDA FICHADA') {
                    this.isClockedIn = 0;
                    this.isPaused = false;
                    this.basePauseSeconds = 0;
                    this.pauseElapsed = '00:00:00';
                    this.baseMealSeconds = 0;
                    this.mealElapsed = '00:00:00';
                    this.isMealPaused = false;
                    this.isMealFinished = false;

                    // Limpiar todos los localStorage utilizados
                    this.clearStorage([
                        'fichaje_paused_time',
                        'pause_start_timestamp',
                        'fichaje_elapsed_time',
                        'meal_elapsed_backup',
                        'meal_start_timestamp'
                    ]);

                    await this.fetchClockStatus();
                }
            }
            catch (e) {
                if (e.response && e.response.data && e.response.data.message) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Atención',
                        text: e.response.data.message,
                    });
                }
                else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Ocurrió un error inesperado.',
                    });
                }
            }
        },
        async fetchClockStatus() {
            try {
                const res = await axios.get('/get_record_status');

                this.hasFinalizadoHoy = res.data.hasFinalizadoHoy;

                this.isClockedIn = res.data.record;
                this.startTime = res.data.time_in || null;
                this.timeOut = res.data.time_out || null;
                this.isPaused = res.data.pause_started_at !== null;
                this.observacionActual = res.data.observaciones || null;



                // === COMIDA ===
                const mealStarted = res.data.meal_started_at !== null;
                const mealSeconds = res.data.meal_total_seconds || 0;
                this.isMealFinished = mealStarted && mealSeconds > 0;
                this.isMealPaused = mealStarted && mealSeconds === 0;
                this.baseMealSeconds = mealSeconds;
                this.mealElapsed = this.formatSeconds(mealSeconds);
                this.mealStartedAt = res.data.meal_started_at;

                // === PAUSA (esto debe ejecutarse siempre) ===
                const pausedTime = localStorage.getItem('fichaje_paused_time');
                const pauseTimestamp = localStorage.getItem('pause_start_timestamp');
                this.basePauseSeconds = res.data.pause_total_seconds || 0;
                this.pauseElapsed = this.formatSeconds(this.basePauseSeconds);

                const mealBackup = localStorage.getItem('meal_elapsed_backup');
                const mealTimestamp = localStorage.getItem('meal_start_timestamp');

                if (this.isMealPaused && mealBackup) {
                    this.stopPolling();

                    // Restaurar cronómetro principal (elapsedTime)
                    this.elapsedTime = mealBackup;
                    const [h, m, s] = mealBackup.split(':').map(Number);
                    this.baseElapsedTime = h * 3600 + m * 60 + s;

                    // Restaurar cronómetro comida
                    this.mealStartTimestamp = mealTimestamp ? parseInt(mealTimestamp) : Date.now();
                    this.updateMealTime();
                    this.mealInterval = setInterval(this.updateMealTime, 1000);

                    return; // ← Salimos, pero ya se ejecutó la lógica de pausa arriba
                }

                // === Restaurar pausa si estaba activa ===
                if (this.isPaused && pausedTime) {
                    this.stopPolling();

                    this.elapsedTime = pausedTime;
                    const [h, m, s] = pausedTime.split(':').map(Number);
                    this.baseElapsedTime = h * 3600 + m * 60 + s;

                    this.pauseStartTimestamp = pauseTimestamp ? parseInt(pauseTimestamp) : Date.now();
                    this.updatePauseTime();
                    this.pauseInterval = setInterval(this.updatePauseTime, 1000);

                    return;
                }

                // === ESTADO NORMAL (ni pausa ni comida) ===
                if (this.isClockedIn) {
                    await this.fetchData();
                    this.startPolling();
                }

            } catch (e) {
                console.error('Error status:', e);
            }
        },
        async fetchData() {
            try {
                const res = await axios.get('/live-fichaje');

                // Parseamos tiempo total de la jornada
                const [h, m, s] = res.data.current.split(':').map(Number);
                let totalSeconds = h * 3600 + m * 60 + s;

                // Restamos los segundos de comida (si hay)
                const mealSeconds = this.baseMealSeconds || 0;
                totalSeconds = Math.max(0, totalSeconds - mealSeconds); // nunca negativo

                // Guardamos
                this.baseElapsedTime = totalSeconds;
                this.elapsedTime = this.formatSeconds(totalSeconds);
            } catch (e) {
                console.error('Error data:', e);
            }
        },
        startPolling() {
            if (!this.pollingInterval) {
                this.fetchData();
                this.pollingInterval = setInterval(() => {
                    this.baseElapsedTime++;
                    this.elapsedTime = this.formatSeconds(this.baseElapsedTime);
                }, 1000);
            }
        },
        stopPolling() {
            if (this.pollingInterval) {
                clearInterval(this.pollingInterval);
                this.pollingInterval = null;
            }
        },
        updatePauseTime() {
            const diff = Math.floor((Date.now() - this.pauseStartTimestamp) / 1000);
            const total = (this.basePauseSeconds || 0) + diff;
            this.pauseElapsed = this.formatSeconds(total);
        },
        formatSeconds(sec) {
            const h = String(Math.floor(sec / 3600)).padStart(2, '0');
            const m = String(Math.floor((sec % 3600) / 60)).padStart(2, '0');
            const s = String(sec % 60).padStart(2, '0');
            return `${h}:${m}:${s}`;
        },
        formatDateTime(dateString) {
            if (!dateString) return '';
            const date = new Date(dateString);
            const hours = String(date.getHours()).padStart(2, '0');
            const minutes = String(date.getMinutes()).padStart(2, '0');
            return `${hours}:${minutes}`;
        },
        async togglePause() {
            try {
                const res = await axios.post('/toggle-pause');
                this.isPaused = res.data.message === 'Pausado';

                if (this.isPaused) {
                    this.stopPolling();
                    const now = Date.now();
                    this.pauseStartTimestamp = now;
                    localStorage.setItem('pause_start_timestamp', now.toString());
                    const pausedTime = this.formatSeconds(this.baseElapsedTime);
                    localStorage.setItem('fichaje_paused_time', pausedTime);
                    this.elapsedTime = pausedTime;
                    this.updatePauseTime();
                    this.pauseInterval = setInterval(this.updatePauseTime, 1000);
                } else {
                    this.startPolling();
                    localStorage.removeItem('fichaje_paused_time');
                    localStorage.removeItem('pause_start_timestamp');
                    this.pauseStartTimestamp = null;
                    clearInterval(this.pauseInterval);
                    this.basePauseSeconds = res.data.pause_total_seconds || 0;
                    this.pauseElapsed = this.formatSeconds(this.basePauseSeconds);
                    await this.fetchClockStatus();
                    await this.fetchData();
                }
            } catch (e) {
                console.error('Error pause:', e);
            }
        },
        confirmarFinalizarJornada() {
            if (this.isPaused || this.isMealPaused) return;

            this.fichar();
        },
        abrirModalPausa() {
            if (!this.isPaused) {
                const modal = document.getElementById('confirmarPausa');
                if (modal) bootstrap.Modal.getOrCreateInstance(modal).show();
            } else {
                this.togglePause();
            }
        },
        confirmarPausa() {
            this.togglePause();
        },
        async toggleComida() {
            try {
                const res = await axios.post('/toggle-meal');

                // Comprobamos si entró en modo comida (pausa activa)
                this.isMealPaused = res.data.message.toLowerCase().includes('comida');

                if (this.isMealPaused) {
                    this.stopPolling();

                    // Guardar el estado actual del cronómetro principal
                    const nowElapsed = this.elapsedTime;
                    localStorage.setItem('meal_elapsed_backup', nowElapsed);

                    // Guardar timestamp de inicio de comida
                    const now = Date.now();
                    localStorage.setItem('meal_start_timestamp', now.toString());

                    this.mealStartTimestamp = now;
                    this.updateMealTime();
                    this.mealInterval = setInterval(this.updateMealTime, 1000);
                } else {
                    this.startPolling();

                    localStorage.removeItem('meal_elapsed_backup');
                    localStorage.removeItem('meal_start_timestamp');

                    this.mealStartTimestamp = null;
                    clearInterval(this.mealInterval);
                    this.baseMealSeconds = res.data.meal_total_seconds || 0;
                    this.mealElapsed = this.formatSeconds(this.baseMealSeconds);
                }

                await this.fetchClockStatus();
            } catch (error) {
                console.error('Error al alternar comida:', error);
            }
        },
        confirmarComida() {
            this.toggleComida();
        },
        updateMealTime() {
            // Verificamos que mealStartTimestamp sea un número válido
            if (!this.mealStartTimestamp || isNaN(this.mealStartTimestamp)) return;

            const diff = Math.floor((Date.now() - this.mealStartTimestamp) / 1000);
            const total = (this.baseMealSeconds || 0) + diff;
            this.mealElapsed = this.formatSeconds(total);
        },
        clearStorage(keys = []) {
            keys.forEach(key => localStorage.removeItem(key));
        },
        getSecondsFromTimes(start, end) {
            if (!start || !end) return 0
            const s = new Date(`1970-01-01T${start}`)
            const e = new Date(`1970-01-01T${end}`)
            return Math.max(0, Math.floor((e - s) / 1000))
        },
        reloadPage() {
            window.location.reload();
        },
        // Dentro de methods: (asegúrate de que el método anterior termina con coma ,)
        async abrirModalObservaciones() {
            const obs = this.observacionActual;

            // helpers para render seguro
            const esc = (s) => (s || '').replace(/</g, '&lt;').replace(/>/g, '&gt;');
            const nl2br = (s) => esc(s).replace(/\n/g, '<br>');

            // ——— Caso A: YA existe observación ———
            if (obs?.titulo || obs?.descripcion) {
                const result = await Swal.fire({
                    icon: 'info',
                    title: 'Observación de hoy',
                    html: `
        <div class="text-center mb-2 text-muted" style="font-size:1rem;">
          Ya tienes una observación registrada hoy. Puedes revisarla y, si es necesario, actualizar su contenido.
        </div>
        
      `,
                    showCancelButton: true,
                    confirmButtonText: 'Editar observación',
                    confirmButtonColor: '#28A745',
                    cancelButtonText: 'Cerrar',
                    reverseButtons: true,
                });

                if (result.isConfirmed) {
                    this.obsTitulo = obs.titulo || '';
                    this.obsDescripcion = obs.descripcion || '';
                    this.obsMode = 'edit';
                    this.$refs.abrirObs?.click();
                }
                return;
            }

            // ——— Caso B: NO existe  ———
            const confirmNew = await Swal.fire({
                icon: 'question',
                title: 'Sin observación hoy',
                html: `
                        <div class="text-center mb-2 text-muted" style="font-size:1rem;">
          ¿Quieres crear una nueva ahora?
        </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Sí, crear',
                confirmButtonColor: '#28A745',
                cancelButtonText: 'Cancelar',
                reverseButtons: true,
            });

            if (confirmNew.isConfirmed) {
                this.obsTitulo = '';
                this.obsDescripcion = '';
                this.obsMode = 'new';
                this.$refs.abrirObs?.click();
            }
        },
        async enviarObservacion() {
            try {
                this.obsErrors = { titulo: null, descripcion: null };
                if (!this.obsTitulo.trim()) this.obsErrors.titulo = 'El título es obligatorio';
                if (!this.obsDescripcion.trim()) this.obsErrors.descripcion = 'La descripción es obligatoria';
                if (this.obsErrors.titulo || this.obsErrors.descripcion) return;

                const payload = {
                    titulo: this.obsTitulo.trim(),
                    descripcion: this.obsDescripcion.trim()
                };

                const res = await axios.post('/observaciones', payload);

                await Swal.fire({
                    icon: 'success',
                    title: this.obsMode === 'edit' ? 'Cambios guardados' : 'Observación registrada',
                    text: res?.data?.message || 'Se ha guardado correctamente.',
                    confirmButtonColor: '#28A745'
                });

                this.observacionActual = { titulo: this.obsTitulo, descripcion: this.obsDescripcion, fecha: new Date().toISOString() };
                this.obsMode = 'edit'; // desde ahora, siempre edit
                this.$refs.cerrarObs?.click?.();

            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.response?.data?.message || 'Ocurrió un error al guardar la observación.'
                });
            }
        }

    },

}
</script>
