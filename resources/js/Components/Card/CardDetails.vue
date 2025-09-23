<template>
    <div :class="['obra-card', estadoBorderColor, 'p-3', 'px-4', 'rounded-4', 'bg-white', 'col-12', 'position-relative', 'border-right', 'mb-2']">
        <!-- Cabecera: nombre, estado y tipo -->
        <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
            <div class="d-flex align-items-center gap-2">
                <h6 class="fw-bold mb-0 text-muted" style="font-size: 1.2rem;">{{ titulo }}</h6>
                <button
                    :class="estadoColor"
                    data-bs-toggle="modal"
                    :data-bs-target="`#${modalId}`"
                >
                    {{ estadoTexto }}
                </button>
            </div>

            <!-- Usuarios -->
            <div class="d-flex align-items-center position-relative">
                <div
                    v-for="(usuario, i) in primerosUsuarios"
                        :key="i"
                        class="user-avatar-img"
                        :title="usuario.nombre"
                    >
                    <img :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(usuario.nombre)}&background=random&size=32&rounded=true`" alt="avatar" />
                </div>

                <div class="position-relative">
                    <div
                        v-if="usuariosRestantes.length"
                        :class="['user-avatar-img', 'extra-avatar', 'fw-semibold', 'd-flex', 'align-items-center', 'justify-content-center', { active: mostrarExtra }]"
                        @click.stop="toggleExtra"
                        style="font-size: 0.6rem"
                    >
                        +{{ usuariosRestantes.length }}
                    </div>

                    <transition name="fade">
                        <div v-if="mostrarExtra" class="extra-users-card animate">
                            <h6><i class="fas fa-users me-1"></i>Todos los usuarios</h6>

                            <div class="search-wrapper">
                                <i class="fas fa-search search-icon"></i>
                                <input type="text" v-model="buscador" placeholder="Buscar usuario..." />
                            </div>

                            <ul class="list-unstyled mb-0">
                                <li v-for="(usuario, i) in usuariosFiltrados" :key="'usuario-' + i" class="d-flex align-items-center gap-2 mb-2">
                                    <div class="user-avatar-img-sm">
                                        <img :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(usuario.nombre)}&background=random&size=32&rounded=true`" alt="avatar" />
                                    </div>

                                    <div class="extra-user-nombre">{{ usuario.nombre }}</div>
                                </li>
                                <li v-if="usuariosFiltrados.length === 0" class="text-muted small px-1">
                                    <i class="fas fa-user-slash me-1"></i> Sin coincidencias
                                </li>
                            </ul>
                        </div>
                    </transition>
                </div>
            </div>
        </div>

        <!-- Datos -->
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-2 mb-1">
            <div v-for="(dato, i) in datos" :key="i" class="col">
                <div class="d-flex align-items-start gap-2 p-2">
                    <i :class="['fas', dato.icono, estadoActual?.iconColor, 'dato-icon']" />
                    <div>
                        <div class="dato-label small fw-bold fst-italic">
                            {{ dato.label }}
                        </div>

                        <div class="dato-valor small text-muted" style="font-size: 1em;">
                            {{ formateaValor(dato.valor) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <BootstrapModal :ModalId="modalId" :ModalSize="'modal-dialog-centered'" :title="'Cambiar estado de la OT'">
        <div class="modal-body pt-0" style="min-height: 140px;">
            <label class="form-label small fw-bold">Nuevo estado</label>
            <MaterialSelect
                :id="`estado-select-${modalId}`"
                v-model="selectedEstado"
                name="estado"
                placeholder="Selecciona nuevo estado"
                :options="estadosOptions"
            />
        </div>

        <div class="text-end" style="min-height: 70px;">
            <button type="button" class="btn btn-cancelar btn-sm me-2 mt-2" data-bs-dismiss="modal">
                Cancelar
            </button>

            <button type="button" class="btn btn-sm btn-gradient-unique mt-2" @click="emitirCambio">
                Actualizar
            </button>
        </div>
        </BootstrapModal>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import BootstrapModal from '@/Components/BootstrapModal.vue'
import MaterialSelect from '@/Components/MaterialSelect.vue';
import axios from 'axios'
import Swal from 'sweetalert2'

const props = defineProps({
    titulo: String,
    datos: Array,
    usuarios: Array,
    estados: {
        type: Array,
        default: () => [
            { value: 0, label: 'Abierta', clase: 'estado-abierta', borderColor: 'border-abierta', iconColor: 'icon-estado-abierta' },
            { value: 1, label: 'En ejecución', clase: 'estado-ejecucion', borderColor: 'border-ejecucion', iconColor: 'icon-estado-ejecucion' },
            { value: 2, label: 'Finalizada', clase: 'estado-finalizada', borderColor: 'border-finalizada', iconColor: 'icon-estado-finalizada' },
            { value: 3, label: 'Cancelada', clase: 'estado-cancelada', borderColor: 'border-cancelada', iconColor: 'icon-estado-cancelada' },
        ]
    },
    estadoActual: { type: Number, default: 0 },
    estadoUrl: { type: String, required: true },
    modalId: { type: String, default: 'modalCambioEstado' },
})

const mostrarExtra = ref(false)
const buscador = ref('')
const selectedEstado = ref(props.obra.estado)

const primerosUsuarios = computed(() => props.usuarios.slice(0, 3))
const usuariosRestantes = computed(() => props.usuarios.slice(3))
const usuariosFiltrados = computed(() =>
    props.usuarios.filter(u =>
        u.nombre.toLowerCase().includes(buscador.value.toLowerCase())
    )
)

const estadoActual = computed(() =>
    props.estados.find(e => e.value === selectedEstado.value)
)
const estadoColor = computed(() =>
    ['estado-badge', estadoActual.value?.clase].filter(Boolean).join(' ')
)
const estadoTexto = computed(() =>
    estadoActual.value?.label || 'Desconocido'
)

const estadoBorderColor = computed(() =>
    estadoActual.value?.borderColor || 'border-secondary'
)

const estadosOptions = computed(() =>
    props.estados.map(e => ({
        value: e.value,
        text: e.label
    }))
)

function toggleExtra() {
    mostrarExtra.value = !mostrarExtra.value
    if (!mostrarExtra.value) buscador.value = ''
}

function handleClickOutside(event) {
    const button = document.querySelector('.extra-avatar')
    const tooltip = document.querySelector('.extra-users-card')
    if (
        mostrarExtra.value &&
        !button?.contains(event.target) &&
        !tooltip?.contains(event.target)
    ) {
        mostrarExtra.value = false
        buscador.value = ''
    }
}

function emitirCambio() {
    axios.put(props.estadoUrl, { estado: selectedEstado.value })
        .then(response => {
            Swal.fire({
                title: '¡Actualizado!',
                text: 'Estado actualizado correctamente.',
                icon: 'success',
            }).then(() => location.reload());
        })
        .catch(error => {
            if (error.response && error.response.data && error.response.data.errors) {
                const messages = Object.values(error.response.data.errors).flat().join('\n');
                Swal.fire({
                    title: 'Error de validación',
                    text: messages,
                    icon: 'warning',
                });
            } else {
                Swal.fire({
                    title: 'Error al actualizar el estado',
                    text: 'Inténtalo de nuevo más tarde.',
                    icon: 'error',
                });
            }
        });
}


function formateaValor(valor) {
    if (!valor || valor === 'null' || valor === 'undefined') return ''
    if (typeof valor === 'string') {
        const limpio = valor
        .split(',')
        .map(part => part.trim())
        .filter(part => part && part.toLowerCase() !== 'null')
        .join(', ')
        return limpio || 'Dato no disponible'
    }
    if (typeof valor === 'string' && valor.trim().length <= 1) return 'Dato no disponible'
    return valor
}

onMounted(() => {
    document.addEventListener('click', handleClickOutside)
})
onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside)
})
</script>

<style scoped>
.dato-icon {
    font-size: 1rem;
    min-width: 1.5rem;
}

.icon-estado-abierta { color: #ffdd8f; background: rgba(255, 193, 7, 0.1); border-radius: 50%; padding: 0.4rem; }
.icon-estado-ejecucion { color: #9ac9ff; background: rgba(33, 150, 243, 0.1); border-radius: 50%; padding: 0.4rem; }
.icon-estado-finalizada { color: #86d3a5; background: rgba(76, 175, 80, 0.1); border-radius: 50%; padding: 0.4rem; }
.icon-estado-cancelada{ color: #cf8b8b; background: rgba(244, 67, 54, 0.1); border-radius: 50%; padding: 0.4rem; }

.obra-card {
    max-width: 100%;
    font-size: 0.75rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05) !important;
    border-bottom-right-radius: 0;
    border-bottom-left-radius: 0;
}

.user-avatar-img {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    overflow: hidden;
    margin-right: -6px;
    border: 2px solid #fff;
    background: #f0f0f0;
    position: relative;
    transition: all 0.2s ease-in-out;
}
.user-avatar-img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.user-avatar-img-sm {
    width: 26px;
    height: 26px;
    border-radius: 50%;
    overflow: hidden;
}
.user-avatar-img-sm img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.extra-avatar {
    cursor: pointer;
    transition: background-color 0.2s ease;
}
.extra-avatar:hover {
    background-color: #e2ecff;
}
.extra-avatar.active {
    background-color: #e7f1ff;
    color: var(--unique-color);
    box-shadow: 0 0 0 2px rgba(13, 110, 253, 0.1);
    transform: scale(1.02);
}
.extra-users-card {
    position: absolute;
    top: 110%;
    right: 0;
    z-index: 1000;
    background: #ffffff;
    border: 1px solid #dee2e6;
    border-radius: 1rem;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.12);
    padding: 1rem;
    min-width: 240px;
    max-height: 260px;
    overflow-y: auto;
    backdrop-filter: blur(6px);
    transition: all 0.3s ease-in-out;
}
.extra-user-nombre {
    font-size: 0.72rem;
    color: #333;
    white-space: nowrap;
    text-overflow: ellipsis;
    overflow: hidden;
}
.search-wrapper {
    position: relative;
    margin-bottom: 0.6rem;
}
.search-wrapper input[type="text"] {
    font-size: 0.7rem;
    padding: 0.25rem 0.5rem 0.25rem 1.8rem;
    width: 100%;
    border: 1px solid #ccc;
    border-radius: 0.5rem;
    outline: none;
    transition: border-color 0.2s, box-shadow 0.2s;
}
.search-wrapper input[type="text"]:focus {
    border-color:var(--unique-color);
    box-shadow: 0 0 0 2px rgba(13, 110, 253, 0.1);
}
.search-icon {
    position: absolute;
    left: 0.6rem;
    top: 50%;
    transform: translateY(-50%);
    color: #aaa;
    font-size: 0.75rem;
    pointer-events: none;
}
.extra-users-card::-webkit-scrollbar {
    width: 6px;
}
.extra-users-card::-webkit-scrollbar-thumb {
    background-color: #bbb;
    border-radius: 8px;
}
.extra-users-card::-webkit-scrollbar-thumb:hover {
    background-color: #888;
}
.fade-enter-active,
.fade-leave-active {
    transition: all 0.25s ease;
}
.fade-enter-from {
    opacity: 0;
    transform: translateY(-10px) scale(0.95);
}
.fade-leave-to {
    opacity: 0;
    transform: translateY(-10px) scale(0.95);
}
</style>
