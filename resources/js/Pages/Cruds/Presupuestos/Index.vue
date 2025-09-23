<script>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import LibroModal from "./LibroModal.vue";
import MaterialTable from "@/Components/MaterialTableGeneral.vue";
import MaterialButton from "@/Components/MaterialButton.vue";
import MaterialSelect from "@/Components/MaterialSelect.vue";
import Datepicker from "@vuepic/vue-datepicker";
import "@vuepic/vue-datepicker/dist/main.css";
import Swal from "sweetalert2";

export default {
    props: {
        presupuestos: Array,
        user: Object,
        clientes: Array,
    },
    components: {
        MaterialTable,
        AuthenticatedLayout,
        LibroModal,
        MaterialButton,
        Datepicker,
        MaterialSelect,
    },
    data() {
        return {
            isSubmitting: false,
            /////
            selectedPresupuestos: [],
            selected_cliente: null,
            selectAll: false,
            dateRange: [],
            selected_estado: null,
            estados: [
                {
                    value: 0,
                    text: "Pendiente",
                },
                {
                    value: 1,
                    text: "Rechazado",
                },
                {
                    value: 2,
                    text: "Aceptado",
                },
                {
                    value: 3,
                    text: "Facturado Parcialmente",
                },
                {
                    value: 4,
                    text: "Facturado",
                },
            ],
        };
    },
    computed: {
        /**
         * Los presupuestos se ordenan por fecha descendente y luego por número de presupuesto descendente.
         */
        filteredPresupuestos() {
            return this.presupuestos
                .filter((presupuesto) => {
                    const isEstadoMatch =
                        this.selected_estado == null ||
                        presupuesto.estado === parseInt(this.selected_estado);

                    const isClienteMatch =
                        this.selected_cliente == null ||
                        presupuesto.cliente?.id === this.selected_cliente;

                    const isDateInRange =
                        Array.isArray(this.dateRange) && this.dateRange.length === 2
                            ? this.isSameOrBetweenDates(
                                presupuesto.fechaInicio,
                                this.dateRange[0],
                                this.dateRange[1]
                            )
                            : true;

                    return (
                        isClienteMatch &&
                        isEstadoMatch &&
                        isDateInRange);
                })
                .sort((a, b) => {
                    // Ordenar por fecha descendente
                    const dateA = new Date(a.fechaInicio);
                    const dateB = new Date(b.fechaInicio);
                    if (dateA.getTime() !== dateB.getTime()) {
                        return dateB - dateA;
                    }

                    // Luego por número de presupuesto descendente
                    const getNum = (num) => {
                        const match = num?.match(/(\d+)$/);
                        return match ? parseInt(match[1]) : 0;
                    };

                    const numA = getNum(a.numPresupuesto);
                    const numB = getNum(b.numPresupuesto);
                    return numB - numA;
                });
        },
        filteredPresupuestosRenderKey() {
            return `${this.selected_cliente}-${this.selected_estado}-${this.dateRange}`;
        },
        totalSeleccionado() {
            return this.selectedPresupuestos
                .map((id) => {
                    const presupuesto = this.filteredPresupuestos.find(
                        (f) => f.id === id
                    );
                    return presupuesto ? presupuesto.total : 0;
                })
                .reduce((acc, total) => acc + total, 0);
        },
        totalSinIvaSeleccionado() {
            return this.selectedPresupuestos
                .map((id) => {
                    const presupuesto = this.filteredPresupuestos.find(
                        (f) => f.id === id
                    );
                    return presupuesto ? presupuesto.total_sin_iva : 0;
                })
                .reduce((acc, total_sin_iva) => acc + total_sin_iva, 0);
        },
    },
    mounted() { },
    methods: {
        formatCurrency(amount) {
            if (isNaN(amount)) return ""; // Handle non-numeric input
            return (
                amount
                    .toFixed(2) // Ensure 2 decimal places
                    .replace(".", ",") // Replace dot with comma for decimal separator
                    .replace(/\B(?=(\d{3})+(?!\d))/g, ".") + // Add dots for thousands separators
                " €"
            ); // Add Euro symbol at the end
        },
        formatDate(dateString) {
            const options = { year: "numeric", month: "long", day: "numeric" };
            return new Date(dateString).toLocaleDateString("es-ES", options);
        },
        download(format) {
            if (
                !this.selectedPresupuestos ||
                this.selectedPresupuestos.length === 0
            ) {
                Swal.fire({
                    icon: "warning",
                    title: "Atención",
                    text: "Por favor, selecciona al menos un presupuesto antes de continuar.",
                });
                return;
            }

            Swal.fire({
                title: "Cargando...",
                text: "Estamos procesando tu solicitud, por favor espera.",
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                },
            });

            this.isSubmitting = true;

            // Prepare request parameters
            const data = {
                presupuestosIds: this.selectedPresupuestos,
                format: format,
            };

            // Send request
            axios
                .get(route("presupuestos.report"), {
                    params: data,
                    responseType: "blob",
                })
                .then((response) => {
                    // Determine file type from response headers
                    const contentType = response.headers["content-type"];

                    let extension = "pdf";
                    if (contentType.includes("application/pdf")) {
                        extension = "pdf";
                    } else if (
                        contentType.includes(
                            "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
                        )
                    ) {
                        extension = "xlsx";
                    } else {
                        throw new Error("Unknown file format");
                    }

                    // Extract filename from headers (fallback if missing)
                    const contentDisposition =
                        response.headers["content-disposition"];
                    const filenameMatch = contentDisposition
                        ? contentDisposition.match(/filename="(.+)"/)
                        : null;
                    const filename = filenameMatch
                        ? filenameMatch[1].trim()
                        : `informe_presupuestos.${extension}`;

                    // Create a blob and trigger download
                    const blob = new Blob([response.data], {
                        type: contentType,
                    });
                    const link = document.createElement("a");
                    link.href = URL.createObjectURL(blob);
                    link.download = filename;
                    link.click();

                    Swal.fire({
                        icon: "success",
                        title: "Descarga completada",
                        text: "El archivo se ha descargado correctamente.",
                    });
                })
                .catch((error) => {
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text:
                            error.response?.data?.message ||
                            "Ocurrió un error al procesar la solicitud.",
                    });
                })
                .finally(() => {
                    this.isSubmitting = false;
                });
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
        toggleAllSelection() {
            if (this.selectAll) {
                this.selectedPresupuestos = this.filteredPresupuestos.map(
                    (presupuesto) => presupuesto.id
                );
            } else {
                this.selectedPresupuestos = [];
            }
        },
        confirmDuplicate(id) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: "¡que desea duplicar!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, duplicar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    location.href = route('presupuestos.duplicate', id);
                }
            });
        },
    },
};
</script>

<template>
    <AuthenticatedLayout :user="user" :title="'Lista de Presupuestos'">
        <div class="container-fluid overflow-visible">
            <div class="row" id="nav">
                <div class="col d-flex justify-content-between" id="nav">
                    <a class="d-flex text-decoration-none fs-5" href="javascript:history.go(-1);" role="button"><i
                            class="material-icons pe-2 pt-1"><span
                                class="material-symbols-outlined">arrow_back</span></i>Volver</a>

                    <div class="d-flex col justify-content-end gap-3">
                        <!-- Dropdown for Excel and PDF options -->
                        <div class="dropdown">
                            <MaterialButton :disabled="isSubmitting" class="dropdown-toggle" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                <i class="material-icons">cloud_download</i>
                            </MaterialButton>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" @click="download('excel')">Excel</a>
                                </li>
                                <li>
                                    <a class="dropdown-item" @click="download('pdf')">PDF</a>
                                </li>
                            </ul>
                        </div>

                        <a href="/presupuestos/create" v-if="
                            user.rol == 'admin' ||
                            user.rol == 'decano' ||
                            user.rol == 'gestor' ||
                            user.rol == 'secretario'
                        ">
                            <material-button data-bs-toggle="modal" data-bs-target="#addModal"
                                class="float-right btn btm-sm">
                                <i class="fas fa-plus me-2"></i>
                                Agregar presupuesto
                            </material-button>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Search Inputs -->
            <div class="row">
                <div class="form-group col-lg-3 mt-3">
                    <MaterialSelect name="selected_cliente" id="select_cliente" v-model="selected_cliente"
                        placeholder="Filtrar por Cliente" :options="clientes.map((cliente) => ({
                            value: cliente.id,
                            text: `${cliente.nombre} ${cliente.apellido_1 || ''
                                },${cliente.dni}`,
                        }))
                            " />
                </div>

                <div class="form-group col-lg-3 mt-3">
                    <MaterialSelect name="selected_estado" id="estados" v-model="selected_estado"
                        placeholder="Filtrar por Estado" :options="estados" />
                </div>

                <div class="form-group col-lg-3 mt-3">
                    <Datepicker v-model="dateRange" range placeholder="Filtrar por Fecha" :enable-time-picker="false"
                        :format="'dd/MM/yyyy'" locale="es" />
                </div>
            </div>

            <div id="facturas_div" class="mt-4">
                <MaterialTable :key="filteredPresupuestosRenderKey" title="PRESUPUESTOS">
                    <thead>
                        <tr>
                            <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-start ps-1">
                                <input type="checkbox" v-model="selectAll" @change="toggleAllSelection" />
                                Fecha de expedición
                            </th>
                            <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-start ps-1">
                                Nº presupuesto
                            </th>
                            <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-start ps-1">
                                Cliente
                            </th>
                            <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-start ps-1">
                                Estado
                            </th>
                            <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-start ps-1">
                                Total Sin Iva
                            </th>
                            <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-start ps-1">
                                Iva
                            </th>
                            <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-start ps-1">
                                Total
                            </th>
                            <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-center">
                                Acciones
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="presupuesto in filteredPresupuestos" :key="presupuesto.id" role="button">
                            <td class="text-xs font-weight-bold text-start" data-type="date">
                                <input type="checkbox" v-model="selectedPresupuestos" :value="presupuesto.id" />
                                {{ formatDate(presupuesto.fechaInicio) }}
                            </td>
                            <td class="text-xs font-weight-bold text-start">
                                {{ presupuesto.numPresupuesto }}
                            </td>
                            <td class="text-xs font-weight-bold uppercase text-start">
                                {{ presupuesto.cliente?.nombre || presupuesto.contacto?.nombre }}
                                {{ presupuesto.cliente?.apellido_1 || presupuesto.contacto?.apellido_1 }}
                            </td>
                            <td class="text-xs font-weight-bold text-start">
                                {{ presupuesto.estado_text }}
                            </td>
                            <td class="text-xs font-weight-bold uppercase text-start">
                                {{ formatCurrency(presupuesto.total_sin_iva) }}
                            </td>
                            <td class="text-xs font-weight-bold uppercase text-start">
                                {{ presupuesto.iva }}%
                            </td>

                            <td class="text-xs font-weight-bold text-start">
                                {{ formatCurrency(presupuesto.total) }}
                            </td>

                            <td class="text-xs font-weight-bold text-center">
                                <a v-if="presupuesto.estado === 0" :href="route('presupuestos.edit', presupuesto.id)"><i
                                        class="material-icons">edit</i></a>
                                <a class="me-1" href="#" @click="confirmDuplicate(presupuesto.id)" title="Duplicar">
                                    <i class="material-icons">content_copy</i>
                                </a>
                                <a :href="route('presupuestos.show', presupuesto.id)"><i
                                        class="material-icons">visibility</i></a>
                            </td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td class="text-right font-weight-bolder text-start">
                                Total seleccionado
                            </td>
                            <td colspan="3"></td>
                            <td class="text-start font-weight-bolder">
                                {{ formatCurrency(totalSinIvaSeleccionado) }}
                            </td>
                            <td></td>
                            <td class="text-start font-weight-bolder">
                                {{ formatCurrency(totalSeleccionado) }}
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </MaterialTable>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style>
/* #facturas_div .dt-search {
    display: none;
} */

@media (max-width: 1024px) {
    .dt-length {
        visibility: hidden;
    }
}
</style>
