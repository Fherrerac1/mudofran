<template>
    <AuthenticatedLayout :user="user" :title="'factura-' + factura.numFactura">
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
                    <iframe :src="'data:application/pdf;base64,' + pdfContent" width="100%" height="100%"></iframe>
                </div>

                <div class="col-lg-3 p-0 card">
                    <div class="p-3">
                        <MaterialButton type="button" class="btn btn-secondary dropdown-toggle"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            Opciones
                        </MaterialButton>
                        <ul class="dropdown-menu shadow">
                            <li>
                                <a href="#" data-bs-toggle="modal" data-bs-target="#cobros_modal"
                                    class="dropdown-item d-flex align-items-center">
                                    <i class="material-icons me-2">payments</i>
                                    Cobros
                                </a>
                            </li>
                            <li>
                                <a :href="route('facturas.duplicate', factura.id)
                                    " class="dropdown-item d-flex align-items-center">
                                    <i class="material-icons me-2">content_copy</i>
                                    Duplicar
                                </a>
                            </li>
                            <li>
                                <a v-if="
                                    factura.estado == 2 ||
                                    factura.estado == 5 ||
                                    factura.estado == 0
                                " :href="route(
                                    'facturas.rectificate',
                                    factura.id
                                )
                                    " class="dropdown-item d-flex align-items-center">
                                    <i class="material-icons me-2">recycling</i>
                                    Rectificar
                                </a>
                            </li>
                            <li v-if="factura.estado == 0 || factura.serie == 7">
                                <a :href="editLink" class="dropdown-item d-flex align-items-center">
                                    <i class="material-icons me-2">edit</i>
                                    Editar
                                </a>
                            </li>
                            <li v-if="factura.cliente">
                                <a role="button" data-bs-toggle="modal" data-bs-target="#email_modal"
                                    class="dropdown-item d-flex align-items-center">
                                    <i class="material-icons me-2">mail</i>
                                    Enviar
                                </a>
                            </li>
                            <li v-if="[1, 2, 5].includes(factura.serie)">
                                <a role="button" data-bs-toggle="modal" data-bs-target="#facturas-recurrentes-Modal"
                                    class="dropdown-item d-flex align-items-center">
                                    <i class="material-icons me-2">replay</i>
                                    Facturas Recurrentes
                                </a>
                            </li>

                            <li>
                                <a role="button" data-bs-toggle="modal" data-bs-target="#facturae_modal"
                                    class="dropdown-item d-flex align-items-center">
                                    <i class="material-icons me-2">download</i>
                                    Facturae
                                </a>
                            </li>
                        </ul>
                    </div>

                    <div class="card-body">
                        <!-- Sidebar content -->
                        <div class="row">
                            <div class="col animated-element">

                                <div v-if="factura.cliente">
                                    <p>Datos del Cliente</p>
                                    <p class="card-title">{{ factura.cliente_nombre || factura.cliente?.nombre
                                    }}
                                    </p>
                                    <p class="card-text d-flex align-items-center text-xs">
                                        <i class="material-icons">email</i> Email: {{ factura.cliente_email ||
                                            factura.cliente?.email }}
                                    </p>
                                    <p class="card-text d-flex align-items-center text-xs">
                                        <i class="material-icons">phone</i> Teléfono: {{
                                            factura.cliente?.telefono_mobile }}
                                    </p>
                                    <p class="card-text d-flex align-items-center text-xs">
                                        <i class="material-icons">fingerprint</i> DNI: {{ factura.cliente_dni ||
                                            factura.cliente?.dni }}
                                    </p>

                                    <p class="card-text d-flex align-items-center text-xs">
                                        <i class="material-icons">location_on</i> Dirección:
                                        {{ factura.cliente_localidad || factura.cliente?.localidad }},
                                        {{ factura.cliente_direccion || factura.cliente?.direccion }},
                                        {{ factura.cliente_cp || factura.cliente?.cp }}
                                    </p>
                                </div>

                                <hr>
                                <p>Datos de la factura</p>
                                <div v-if="factura.presupuesto" class="col-12">
                                    <p class="text-xs">
                                        <strong>Esta factura está relacionada con
                                            el presupuesto :</strong><br />
                                        <a :href="route(
                                            'presupuestos.show',
                                            factura.presupuesto?.id
                                        )
                                            ">{{
                                                factura.presupuesto
                                                    ?.numPresupuesto
                                            }}</a>
                                    </p>
                                </div>
                                <div class="col-12 d-flex justify-content-between">
                                    <p class="text-xs">
                                        <strong>Nº factura:</strong>
                                        {{ factura.numFactura }}
                                    </p>
                                </div>
                                <div v-if="factura.nativa" class="col-12">
                                    <p class="text-xs">
                                        <strong>es una factura rectificativa de
                                            factura :</strong>
                                        {{ factura.nativa?.numFactura }}
                                    </p>
                                </div>
                                <div class="col-12 d-flex justify-content-between">
                                    <p class="text-xs">
                                        <strong>Expedición:</strong>
                                        {{ factura.fechaInicio }}
                                    </p>
                                </div>

                                <div>
                                    <MaterialButton :disabled="factura.estado !== 0" @click="toggleEditMode" :class="{
                                        'bg-warning': factura.estado == 0,
                                        'bg-danger': factura.estado == 1,
                                        'bg-success': factura.estado == 2,
                                        'bg-info': factura.estado == 3,
                                        'bg-secondary': factura.estado == 4,
                                        'bg-info': factura.estado == 5,
                                    }" id="estado-container">
                                        {{ factura.estado_text }}
                                    </MaterialButton>

                                    <select class="form-select border px-3" v-model="selectedEstado" v-show="isEditMode"
                                        @change="updateEstado">
                                        <option value="0">Pendiente</option>
                                        <option value="1">Anular</option>
                                        <option v-if="factura.serie == 7" value="4">
                                            Aceptado
                                        </option>
                                    </select>
                                </div>

                                <p v-if="factura.porcentaje !== 100">
                                    es un fraccion de {{ factura.porcentaje }}%
                                    del presupuesto
                                </p>

                                <div v-if="factura.concepto">
                                    <p>Concepto</p>
                                    <p class="card-text d-flex align-items-center text-xs">
                                        {{ factura.concepto }}
                                    </p>
                                </div>
                                <p>Observaciones</p>
                                <p class="card-text d-flex align-items-center text-xs">
                                    {{ factura.observaciones }}
                                </p>
                                <p>Pliegos y Condiciones</p>
                                <p class="card-text d-flex align-items-center text-xs">
                                    {{ factura.condiciones }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <footer class="card mx-auto fixed-bottom align-items-center d-none d-md-flex">
                    <div class="container">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        Total sin Iva:
                                        {{ formattedTotalSinIva }}€
                                    </div>
                                    <div>
                                        Iva ({{ factura.iva }}%):
                                        {{ formattedIva }} €
                                    </div>
                                    <div>
                                        IRPF ({{ factura.irpf }}%):
                                        {{ formattedIrpf }} €
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

        <Cobros :factura="factura" />
        <MultiEmailsModal :factura="factura" />
        <Facturae :factura="factura" />
        <FacturaRecurrenteModalCreate :factura="factura" :user="user" />

    </AuthenticatedLayout>
</template>

<script>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Swal from "sweetalert2";
import MaterialButton from "@/Components/MaterialButton.vue";
import Cobros from "./Relations/Cobros.vue";
import MultiEmailsModal from "./MultiEmailsModal.vue";
import Facturae from "./Relations/Facturae.vue";
import FacturaRecurrenteModalCreate from "../Recurrentes/FacturaRecurrenteModalCreate.vue";

export default {
    components: {
        AuthenticatedLayout,
        MaterialButton,
        Cobros,
        MultiEmailsModal,
        Facturae,
        FacturaRecurrenteModalCreate
    },
    props: {
        factura: {
            type: Object,
            required: true,
        },
        pdfContent: {
            type: String,
            required: false,
        },
        user: {
            type: Object,
            required: true,
        },
    },
    data() {
        return {
            isEditMode: false,
            selectedEstado: this.factura.estado,
        };
    },
    computed: {
        editLink() {
            return `/facturas/${this.factura.id}/editar`;
        },
        formattedTotalSinIva() {
            return this.formatNumber(this.factura.total_sin_iva);
        },
        formattedIva() {
            const totalIva =
                (this.factura.total_sin_iva * this.factura.iva) / 100;
            return this.formatNumber(totalIva);
        },
        formattedIrpf() {
            const totalIrpf =
                (this.factura.total_sin_iva * this.factura.irpf) / 100;
            return this.formatNumber(totalIrpf);
        },
        formattedTotal() {
            return this.formatNumber(this.factura.total);
        },
    },
    methods: {
        toggleEditMode() {
            this.isEditMode = !this.isEditMode;
        },
        formatNumber(number) {
            return number.toLocaleString("es-ES", {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            });
        },
        updateEstado() {
            axios
                .put(`/facturas/${this.factura.id}/estado`, {
                    estado: this.selectedEstado,
                })
                .then((response) => {
                    Swal.fire({
                        title: "Éxito!",
                        text: "Estado actualizado correctamente.",
                        icon: "success",
                        confirmButtonText: "Aceptar",
                    }).then(location.reload());
                })
                .catch((error) => {
                    Swal.fire({
                        title: "Error!",
                        text: "Hubo un problema al actualizar el estado.",
                        icon: "error",
                        confirmButtonText: "Aceptar",
                    });
                    console.error("Error actualizando estado:", error);
                });
        },
        confirmDelete(id) {
            Swal.fire({
                title: "¿Estás seguro?",
                text: "¡No podrás revertir esta acción!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Sí, eliminarlo",
                cancelButtonText: "Cancelar",
            }).then((result) => {
                if (result.isConfirmed) {
                    this.deletefactura(id);
                }
            });
        },
        deletefactura(id) {
            axios
                .get(`/facturas/${id}/delete`)
                .then((response) => {
                    Swal.fire(
                        "Eliminado!",
                        "La factura ha sido eliminado.",
                        "success"
                    ).then(() => {
                        // Reload the page to reflect changes
                        window.location.reload();
                    });
                })
                .catch((error) => {
                    Swal.fire(
                        "Error!",
                        "Hubo un problema al eliminar La factura.",
                        "error"
                    );
                });
        },
        getArchivoUrl(archivo) {
            const storageBaseUrl = "/storage"; // Replace with your actual storage base URL

            if (archivo) {
                // Append a unique timestamp to bypass caching
                return `${storageBaseUrl}/${archivo}?t=${Date.now()}`;
            } else {
                return "/noFile";
            }
        },
    },
    mounted() {
        //console.log(this.factura);
    },
};
</script>
