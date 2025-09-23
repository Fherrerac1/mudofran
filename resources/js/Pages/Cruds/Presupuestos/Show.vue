<template>
    <AuthenticatedLayout :user="user" :title="'Vista de la Presupuesto'">
        <div class="container-fluid overflow-hidden">
            <div class="row">

                <div class="row" id="nav">
                    <a class="d-flex text-decoration-none fs-5" href="javascript:history.go(-1);" role="button">
                        <i class="material-icons pe-2 pt-1">
                            <span class="material-symbols-outlined">arrow_back</span>
                        </i>
                        Volver
                    </a>
                </div>



                <div class="col-lg-9 min-vh-100">
                    <iframe :src="'data:application/pdf;base64,' + pdfContent" width="100%" height="100%"
                        style="min-height: 400px;"></iframe>
                </div>

                <div class="col-lg-3 p-0 card">
                    <div class="p-3">
                        <MaterialButton type="button" class="btn btn-secondary dropdown-toggle"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            Opciones
                        </MaterialButton>
                        <ul class="dropdown-menu shadow">
                            <li v-if="presupuesto.facturas">
                                <a href="#" data-bs-toggle="modal" data-bs-target="#facturas_modal"
                                    class="dropdown-item d-flex align-items-center">
                                    <i class="material-icons me-2">receipt_long</i> Facturas
                                </a>
                            </li>
                            <li>
                                <a :href="route('facturas_presupuesto.borradores.generate', presupuesto.id)"
                                    class="dropdown-item d-flex align-items-center">
                                    <i class="material-icons me-2">insert_drive_file</i> Borrador
                                </a>
                            </li>
                            <li v-if="presupuesto.estado == 0 || presupuesto.estado == 2">
                                <a :href="editLink" class="dropdown-item d-flex align-items-center">
                                    <i class="material-icons me-2">edit</i> Editar
                                </a>
                            </li>
                            <li>
                                <a :href="route('presupuestos.duplicate', presupuesto.id)"
                                    class="dropdown-item d-flex align-items-center">
                                    <i class="material-icons me-2">content_copy</i> Duplicar
                                </a>
                            </li>
                            <li v-if="presupuesto.cliente">
                                <a role="button" data-bs-toggle="modal" data-bs-target="#email_modal"
                                    class="dropdown-item d-flex align-items-center">
                                    <i class="material-icons me-2">mail</i> Enviar
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <!-- Sidebar content -->
                        <div class="row">
                            <div class="col animated-element">

                                <div v-if="presupuesto.cliente">
                                    <p>Datos del Cliente</p>
                                    <p class="card-title">{{ presupuesto.cliente_nombre || presupuesto.cliente?.nombre
                                        }}
                                    </p>
                                    <p class="card-text d-flex align-items-center text-xs">
                                        <i class="material-icons">email</i> Email: {{ presupuesto.cliente_email ||
                                        presupuesto.cliente?.email }}
                                    </p>
                                    <p class="card-text d-flex align-items-center text-xs">
                                        <i class="material-icons">phone</i> Teléfono: {{
                                            presupuesto.cliente?.telefono_mobile }}
                                    </p>
                                    <p class="card-text d-flex align-items-center text-xs">
                                        <i class="material-icons">fingerprint</i> DNI: {{ presupuesto.cliente_dni ||
                                        presupuesto.cliente?.dni }}
                                    </p>

                                    <p class="card-text d-flex align-items-center text-xs">
                                        <i class="material-icons">location_on</i> Dirección:
                                        {{ presupuesto.cliente_localidad || presupuesto.cliente?.localidad }},
                                        {{ presupuesto.cliente_direccion || presupuesto.cliente?.direccion }},
                                        {{ presupuesto.cliente_cp || presupuesto.cliente?.cp }}
                                    </p>
                                </div>
                                <div v-else>Sin cliente asignado</div>

                                <p>Datos del Presupuesto</p>
                                <div class="col-12 d-flex justify-content-between">
                                    <p class="text-xs"><strong>Nº presupuesto:</strong> {{ presupuesto.numPresupuesto }}
                                    </p>
                                </div>
                                <div v-if="presupuesto.presupuesto_anexo" class="col-12 d-flex justify-content-between">
                                    <p class="text-xs"><strong>Es nu anexo del presupuesto: </strong>
                                        <a class="text-nowrap"
                                            :href="route('presupuestos.show', presupuesto.anexo?.id)">{{
                                                presupuesto.anexo.numPresupuesto }}</a>
                                    </p>
                                </div>
                                <div class="col-12 d-flex justify-content-between">
                                    <p class="text-xs"><strong>Expedición:</strong> {{ presupuesto.fechaInicio }}</p>
                                </div>

                                <div>

                                    <MaterialButton :disabled="presupuesto.estado !== 0 && presupuesto.estado !== 1"
                                        @click="toggleEditMode" :class="{
                                            'bg-warning': presupuesto.estado == 0,
                                            'bg-danger': presupuesto.estado == 1,
                                            'bg-success': presupuesto.estado == 2,
                                            'bg-info': presupuesto.estado == 3,
                                            'bg-secondary': presupuesto.estado == 4
                                        }" id="estado-container">
                                        {{ presupuesto.estado_text }}
                                    </MaterialButton>


                                    <div v-if="isEditMode" class="d-flex gap-3 mt-1">
                                        <button class="btn btn-secondary" data-bs-toggle="modal"
                                            data-bs-target="#GenerateOt_modal">Aceptar</button>
                                        <button class="btn btn-danger" :disabled="presupuesto.estado === 1"
                                            @click="updateEstado(1)">Rechazar</button>
                                    </div>
                                </div>


                                <p>Observaciones</p>
                                <p class="card-text d-flex align-items-center text-xs">{{ presupuesto.observaciones }}
                                </p>
                                <p>Pliegos y Condiciones</p>
                                <p class="card-text d-flex align-items-center text-xs">{{ presupuesto.condiciones }}</p>

                                <div>
                                    <h5>Adjuntos</h5>
                                    <div class="row g-3">
                                        <div v-for="(file, index) in presupuesto.fotos" :key="index" class="col">
                                            <div class="card h-100">
                                                <div class="card-body text-center">
                                                    <!-- Check if file is PDF -->
                                                    <template v-if="isPDF(file)">
                                                        <embed :src="'/storage/' + file" type="application/pdf" class="w-100"
                                                            style="height: 300px;" />
                                                        <p class="mt-2"><a :href="file" target="_blank"
                                                                class="btn btn-primary btn-sm">Open PDF</a></p>
                                                    </template>
                                                    <!-- Otherwise assume it's an image -->
                                                    <template v-else>
                                                        <img :src="'/storage/' + file" alt="Foto del Presupuesto"
                                                            class="img-fluid rounded" />
                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                <footer class="card mx-auto fixed-bottom align-items-center d-flex">
                    <div class="container">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        Total sin Iva: {{ formattedTotalSinIva }}€
                                    </div>
                                    <div>
                                        Iva ({{ presupuesto.iva }}%): {{ formattedIva }} €
                                    </div>
                                    <div>
                                        IRPF ({{ presupuesto.irpf }}%): {{ formattedIrpf }} €
                                    </div>
                                    <div>
                                        Total: <b>{{ formattedTotal }}€</b>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </footer>

            </div>
        </div>
        <RelatedFacturas :presupuesto="presupuesto"></RelatedFacturas>
        <GenerateOtModal :presupuesto="presupuesto" :clientes="clientes" />
        <MultiEmailsModal :presupuesto="presupuesto"></MultiEmailsModal>
    </AuthenticatedLayout>
</template>

<script>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Swal from 'sweetalert2';
import MaterialButton from "@/Components/MaterialButton.vue";
import RelatedFacturas from "./Relations/RelatedFacturas.vue";
import GenerateOtModal from "./Relations/GenerateOtModal.vue";
import MultiEmailsModal from "./MultiEmailsModal.vue";

export default {
    components: {
        AuthenticatedLayout,
        MaterialButton,
        RelatedFacturas,
        GenerateOtModal,
        MultiEmailsModal
    },
    props: {
        presupuesto: {
            type: Object,
            required: true,
        },
        pdfContent: {
            type: String,
            required: true,
        },
        clientes: {
            type: Array,
            required: true,
        },
        user: {
            type: Object,
            required: true,
        },
    },
    data() {
        return {
            isEditMode: false,
            selectedEstado: this.presupuesto.estado,
        };
    },
    computed: {
        editLink() {
            return `/presupuestos/${this.presupuesto.id}/editar`;
        },
        formattedTotalSinIva() {
            return this.formatNumber(this.presupuesto.total_sin_iva);
        },
        formattedIva() {
            const totalIva = (this.presupuesto.total_sin_iva * this.presupuesto.iva) / 100;
            return this.formatNumber(totalIva);
        },
        formattedIrpf() {
            const totalIrpf = (this.presupuesto.total_sin_iva * this.presupuesto.irpf) / 100;
            return this.formatNumber(totalIrpf);
        },
        formattedTotal() {
            return this.formatNumber(this.presupuesto.total);
        },
    },
    methods: {
        isPDF(file) {
            return file.toLowerCase().endsWith('.pdf');
        },
        toggleEditMode() {
            this.isEditMode = !this.isEditMode;
        },
        formatNumber(number) {
            return number.toLocaleString('es-ES', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },
        updateEstado(newEstado) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: '¿Quieres actualizar el estado de este presupuesto?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, actualizar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    axios.put(`/presupuestos/${this.presupuesto.id}/estado`, { estado: newEstado })
                        .then((response) => {
                            Swal.fire({
                                title: '¡Éxito!',
                                text: 'Estado actualizado correctamente.',
                                icon: 'success',
                                confirmButtonText: 'Aceptar'
                            }).then(() => location.reload());
                        })
                        .catch((error) => {
                            Swal.fire({
                                title: '¡Error!',
                                text: 'Hubo un problema al actualizar el estado.',
                                icon: 'error',
                                confirmButtonText: 'Aceptar'
                            });
                            console.error('Error actualizando estado:', error);
                        });
                }
            });
        },
        confirmDelete(id) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: "¡No podrás revertir esta acción!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, eliminarlo',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.deletePresupuesto(id);
                }
            });
        },
        deletePresupuesto(id) {
            axios.get(`/presupuestos/${id}/delete`)
                .then(response => {
                    Swal.fire(
                        'Eliminado!',
                        'La presupuesto ha sido eliminado.',
                        'success'
                    ).then(() => {
                        // Reload the page to reflect changes
                        window.location.reload();
                    });
                })
                .catch(error => {
                    Swal.fire(
                        'Error!',
                        'Hubo un problema al eliminar La presupuesto.',
                        'error'
                    );
                });
        }, getArchivoUrl(archivo) {
            const storageBaseUrl = '/storage'; // Replace with your actual storage base URL

            if (archivo) {
                // Append a unique timestamp to bypass caching
                return `${storageBaseUrl}/${archivo}?t=${Date.now()}`;
            } else {
                return '/noFile';
            }
        }
    },
};
</script>