<script>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import MaterialTable from "@/Components/MaterialTableGeneral.vue";
import MaterialButton from "@/Components/MaterialButton.vue";
import MaterialInput from "@/Components/MaterialInput.vue";
import Datepicker from "@vuepic/vue-datepicker";
import "@vuepic/vue-datepicker/dist/main.css";
import MaterialSelect from "@/Components/MaterialSelect.vue";
import Swal from "sweetalert2";
import Multiselect from "vue-multiselect";
import "vue-multiselect/dist/vue-multiselect.min.css";

export default {
    props: {
        facturas: Array,
        user: Object,
        clientes: Array,
    },
    components: {
        MaterialTable,
        AuthenticatedLayout,
        MaterialButton,
        MaterialInput,
        Datepicker,
        MaterialSelect,
        Multiselect,
    },

    data() {
        return {
            isSubmitting: false,
            /////
            selectedFacturas: [],
            selected_cliente: null,
            selected_serie: null,
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
                    text: "Rechazada",
                },
                {
                    value: 2,
                    text: "Pagado",
                },
                {
                    value: 5,
                    text: "Pagado Parcialmente",
                },
            ],
            seriesOptions: [
                { value: "1", text: "serie 1 Factura normal" },
                {
                    value: "2",
                    text: "serie 2 facturas rectificativas de las normales",
                },
                { value: "5", text: "serie 5 autofacturación" },
                { value: "11", text: "serie 11 Borrador" },
            ],
        };
    },

    computed: {
        filteredFacturas() {
            // Extract series values if any are selected, converting them to numbers
            const selectedSeriesValues =
                this.selected_serie && this.selected_serie.length
                    ? this.selected_serie.map((item) => parseInt(item.value))
                    : [];

            return this.facturas.filter((factura) => {
                const isEstadoMatch =
                    this.selected_estado == null ||
                    factura.estado === parseInt(this.selected_estado);

                const isClienteMatch =
                    this.selected_cliente == null ||
                    factura.cliente?.id === this.selected_cliente;

                // Check if factura's serie is in the array of selected series values (or pass through if none selected)
                const isSerieMatch =
                    selectedSeriesValues.length === 0 ||
                    selectedSeriesValues.includes(parseInt(factura.serie));

                // Check if dateRange is valid and contains two dates
                const isDateInRange =
                    Array.isArray(this.dateRange) && this.dateRange.length === 2
                        ? new Date(factura.fechaInicio) >=
                        new Date(this.dateRange[0]) &&
                        new Date(factura.fechaInicio) <=
                        new Date(this.dateRange[1])
                        : true; // If dateRange is invalid or not provided, consider all dates as valid

                return (
                    isClienteMatch &&
                    isEstadoMatch &&
                    isDateInRange &&
                    isSerieMatch
                );
            });
        },
        filteredFacturasRenderKey() {
            return `${this.selected_cliente}-${this.selected_estado}-${this.dateRange?.[0] || ''}-${this.dateRange?.[1] || ''}`;
        },
        totalSeleccionado() {
            return this.selectedFacturas
                .map((id) => {
                    const factura = this.filteredFacturas.find(
                        (f) => f.id === id
                    );
                    return factura ? factura.total : 0;
                })
                .reduce((acc, total) => acc + total, 0);
        },
        totalSinIvaSeleccionado() {
            return this.selectedFacturas
                .map((id) => {
                    const factura = this.filteredFacturas.find(
                        (f) => f.id === id
                    );
                    return factura ? factura.total_sin_iva : 0;
                })
                .reduce((acc, total_sin_iva) => acc + total_sin_iva, 0);
        },
        /**
 * Determina si una fecha dada (facturaDate) es igual o está entre dos fechas dadas (startDate y endDate).
 *
 * @param {Date} facturaDate Fecha a comparar
 * @param {Date} startDate Fecha de inicio del rango
 * @param {Date} endDate Fecha de fin del rango
 * @returns {boolean} Verdadero si la fecha es igual o está entre el rango
 */
        isSameOrBetweenDates(facturaDate, startDate, endDate) {
            const normalize = (date) => {
                const d = new Date(date);
                d.setHours(0, 0, 0, 0);
                return d;
            };

            const facturaNormalized = normalize(facturaDate);
            const start = normalize(startDate);
            const end = normalize(endDate);

            return facturaNormalized >= start && facturaNormalized <= end;
        },
    },

    methods: {
        formatCurrency(amount) {
            return new Intl.NumberFormat('es-ES', {
                style: 'currency',
                currency: 'EUR',
                minimumFractionDigits: 2
            }).format(amount);
        },
        formatDate(dateString) {
            const options = { year: "numeric", month: "long", day: "numeric" };
            return new Date(dateString).toLocaleDateString("es-ES", options);
        },
        download(format) {
            if (!this.selectedFacturas || this.selectedFacturas.length === 0) {
                Swal.fire({
                    icon: "warning",
                    title: "Atención",
                    text: "Por favor, selecciona al menos una factura antes de continuar.",
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
                facturasIds: this.selectedFacturas,
                format: format,
            };

            // Send request
            axios
                .get(route("facturas.report"), {
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
                        : `informe_facturas.${extension}`;

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

        toggleAllSelection() {
            if (this.selectAll) {
                this.selectedFacturas = this.filteredFacturas.map(
                    (factura) => factura.id
                );
            } else {
                this.selectedFacturas = [];
            }
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
                    this.deleteFactura(id);
                }
            });
        },
        deleteFactura(id) {
            axios.get(`/facturas/${id}/delete`)
                .then(response => {
                    Swal.fire(
                        'Eliminado!',
                        'La factura ha sido eliminado.',
                        'success'
                    ).then(() => {
                        // Reload the page to reflect changes
                        window.location.reload();
                    });
                })
                .catch(error => {
                    Swal.fire(
                        'Error!',
                        'Hubo un problema al eliminar la factura.',
                        'error'
                    );
                });
        }
    },
    mounted() {

    },
};
</script>

<template>
    <AuthenticatedLayout :user="user" :title="'Lista de Borradores'">
        <div class="container-fluid overflow-visible">
            <div class="row" id="nav">
                <div class="col d-flex justify-content-between mb-3" id="nav">
                    <a class="d-flex text-decoration-none fs-5" href="javascript:history.go(-1);" role="button">
                        <i class="material-icons pe-2 pt-1"><span
                                class="material-symbols-outlined">arrow_back</span></i>Volver
                    </a>

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

                        <!-- <a data-bs-toggle="modal" data-bs-target="#LibroModal">
                            <material-button class="float-right btn btm-sm">
                                <i class="fas fa-book me-2"></i> Exportar libro
                            </material-button>
                        </a> -->

                        <a href="/facturas_borrador_create">
                            <material-button class="float-right btn btm-sm">
                                <i class="fas fa-plus me-2"></i> Agregar Borrador
                            </material-button>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Search Inputs -->
            <div class="row">
                <div class="form-group col-lg-3 my-2">
                    <MaterialSelect name="selected_cliente" id="estados" v-model="selected_cliente"
                        placeholder="Filtrar por Cliente" :options="clientes.map((cliente) => ({
                            value: cliente.id,
                            text: `${cliente.nombre} ${cliente.apellido_1 || ''
                                },${cliente.dni}`,
                        }))
                            " />
                </div>

                <div class="form-group col-lg-3 my-2">
                    <Datepicker v-model="dateRange" range placeholder="Filtrar por Fecha" :enable-time-picker="false"
                        :format="'dd/MM/yyyy'" locale="es" />
                </div>
            </div>

            <div id="facturas_div">
                <MaterialTable :key="filteredFacturasRenderKey" title="BORRADORES">
                    <thead>
                        <tr>
                            <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-start ps-1">
                                <input type="checkbox" v-model="selectAll" @change="toggleAllSelection" />
                                Fecha de expedición
                            </th>
                            <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-start ps-1">
                                NºFactura
                            </th>
                            <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-start ps-1">
                                Cliente
                            </th>

                            <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-start ps-1">
                                Total sin IVA
                            </th>
                            <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-start ps-1">
                                IVA
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
                        <tr v-for="factura in filteredFacturas" :key="factura.id" role="button">
                            <td class="text-xs font-weight-bold text-start" data-type="date">
                                <input type="checkbox" v-model="selectedFacturas" :value="factura.id" />
                                {{ formatDate(factura.fechaInicio) }}
                            </td>
                            <td class="text-xs font-weight-bold text-start">
                                {{ factura.numFactura }}
                            </td>
                            <td class="text-xs font-weight-bold uppercase text-start">
                                {{ factura.cliente?.nombre }}
                                {{ factura.cliente?.apellido_1 }}
                            </td>

                            <td class="text-xs font-weight-bold text-start">
                                {{ formatCurrency(factura.total_sin_iva) }}
                            </td>
                            <td class="text-xs font-weight-bold text-start">
                                {{ factura.iva }}%
                            </td>
                            <td class="text-xs font-weight-bold text-start">
                                {{ formatCurrency(factura.total) }}
                            </td>
                            <td class="text-xs font-weight-bold text-center">
                                <a v-if="user.rol === 'admin'" @click="confirmDelete(factura.id)"><i
                                        class="material-icons">delete</i></a>
                                <a v-if="factura.estado === 0" :href="route('facturas.borradores.edit', factura.id)">
                                    <i class="material-icons">edit</i>
                                </a>
                                <a :href="route('facturas.borradores.show', factura.id)">
                                    <i class="material-icons">visibility</i>
                                </a>
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
