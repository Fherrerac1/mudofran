<script>
import MaterialButton from "@/Components/MaterialButton.vue";
import MaterialTable from "@/Components/MaterialTableDashboard.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Swal from "sweetalert2";
import CreateServicio from "./CreateServicio.vue";
import UpdateServicio from "./UpdateServicio.vue";
import CreateProducto from "./Productos/CreateProducto.vue";
import UpdateProducto from "./Productos/UpdateProducto.vue";

export default {
    components: {
        MaterialButton,
        AuthenticatedLayout,
        MaterialTable,
        CreateServicio,
        UpdateServicio,
        CreateProducto,
        UpdateProducto,
    },
    props: {
        servicios: {
            type: Array,
            required: true,
        },
        productos: {
            type: Array,
            required: true,
        },
        pageType: {
            type: String,
            default: "servicios_page",
        },
        user: {
            type: Object,
            required: true,
        },
    },
    data() {
        return {
            isSubmitting: false,
            page: this.pageType,
            seletedServicio: [],
            seletedProducto: [],
            selectAll: false,  // Nuevo
            selectedServicios: [], // Nuevo
            selectedProductos: [], // Nuevo
        };
    },
    methods: {
        formatCurrency(amount) {
            if (isNaN(amount)) return ""; // Handle non-numeric input

            // Format the number with grouping for thousands, and replace the dot with a comma for decimals
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
        confirmDelete(id, type) {
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
                    this.deleteItem(id, type);
                }
            });
        },
        deleteItem(id, type) {
            axios
                .get(`/${type}/${id}/delete`) // Use `type` to dynamically determine the endpoint
                .then(() => {
                    Swal.fire(
                        "Eliminado!",
                        `El ${type} ha sido eliminado.`,
                        "success"
                    ).then(() => {
                        window.location.reload();
                    });
                })
                .catch(() => {
                    Swal.fire(
                        "Error!",
                        `Hubo un problema al eliminar el ${type}.`,
                        "error"
                    );
                });
        },
        download(format) {
            const selectedItems =
                this.page === "servicios_page"
                    ? this.seletedServicio
                    : this.seletedProducto;

            if (!selectedItems || selectedItems.length === 0) {
                Swal.fire({
                    icon: "warning",
                    title: "Atención",
                    text: "Por favor, selecciona al menos un producto o servicio antes de continuar.",
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

            // Procesar los IDs de los elementos seleccionados
            const processedItems = selectedItems.map(item => item);

            // Preparar los datos para la solicitud
            const data = {
                itemsIds: processedItems,
                format: format,
            };

            // Determina la ruta dependiendo de si es un servicio o un producto
            const routeName = this.page === "servicios_page" ? "servicios.report" : "productos.report";

            axios
                .get(route(routeName), {
                    params: data,
                    responseType: "blob",
                })
                .then((response) => {
                    const contentType = response.headers["content-type"];
                    let extension = contentType.includes("pdf")
                        ? "pdf"
                        : "xlsx";

                        const contentDisposition = response.headers["content-disposition"];
                        const filenameMatch = contentDisposition
                            ? contentDisposition.match(/filename="?([^"]+)"?/)
                            : null;
                        const filename = filenameMatch
                            ? filenameMatch[1].trim()
                            : `reporte.${extension}`;

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
         * Alterna la selección de todos los servicios.
         * Si se marca la casilla de selección, se seleccionan todos los servicios.
         * Si se desmarca, se deseleccionan todos los servicios.
         */
        toggleAllSelection() {
            if (this.selectAll) {
                this.seletedServicio = this.servicios.map(
                    (servicio) => servicio.id
                );
            }
            else {
                this.seletedServicio = [];
            }
        },
        /**
         * Alterna la selección de todos los productos.
         * Si 'selectAll' está activado, selecciona todos los IDs de productos.
         * De lo contrario, deselecciona todos los productos.
         */
        toggleAllSelectionProductos() {
            if (this.selectAll) {
                this.seletedProducto = this.productos.map(
                    (producto) => producto.id
                );
            }
            else {
                this.seletedProducto = [];
            }
        }
    },
};
</script>

<template>
    <AuthenticatedLayout :user="user" :title="'Lista de servicios & Productos'">
        <div class="container-fluid overflow-visible">
            <div class="row" id="nav">
                <a
                    class="col d-flex text-decoration-none fs-5"
                    href="javascript:history.go(-1);"
                    role="button"
                >
                    <i class="material-icons pe-2 pt-1">
                        <span class="material-symbols-outlined"
                            >arrow_back</span
                        >
                    </i>
                    Volver
                </a>
                <div class="col d-flex justify-content-end">
                    <!-- Dropdown for Excel and PDF options -->
                    <div class="dropdown me-2">
                        <MaterialButton
                            :disabled="isSubmitting"
                            class="dropdown-toggle"
                            data-bs-toggle="dropdown"
                            aria-expanded="false"
                        >
                            <i class="material-icons">cloud_download</i>
                        </MaterialButton>
                        <ul class="dropdown-menu">
                            <li>
                                <a
                                    class="dropdown-item"
                                    @click="download('excel')"
                                    >Excel</a
                                >
                            </li>
                            <li>
                                <a
                                    class="dropdown-item"
                                    @click="download('pdf')"
                                    >PDF</a
                                >
                            </li>
                        </ul>
                    </div>

                    <a :href="route('servicios.index')">
                        <button
                            class="btn btn-info me-2"
                            :disabled="page === 'servicios_page'"
                            :class="{ active: page === 'servicios_page' }"
                        >
                            Servicios
                        </button>
                    </a>
                    <a :href="route('productos.index')">
                        <button
                            class="btn btn-info"
                            :class="{ active: page === 'productos_page' }"
                            :disabled="page === 'productos_page'"
                        >
                            Productos
                        </button>
                    </a>
                </div>
            </div>

            <!-- servicios -->
            <div v-if="page === 'servicios_page'">
                <div class="col d-flex justify-content-end">
                    <MaterialButton
                        class="d-flex align-items-center"
                        data-bs-toggle="modal"
                        data-bs-target="#createServicioModal"
                    >
                        <i class="material-icons pe-2">
                            <span class="material-symbols-outlined">add</span>
                        </i>
                        Agregar servicios
                    </MaterialButton>
                </div>

                    <MaterialTable title="SERVICIOS" class="flex-fill">
                        <thead>
                            <tr>
                                <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-start ps-1"  >
                                    <input type="checkbox" v-model="selectAll" @change="toggleAllSelection" />
                                    Fecha
                                </th>
                                <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-start ps-1" > Nombre </th>
                                <th  class="text-uppercase text-xxs font-weight-bolder opacity-7 text-start ps-1" > Observaciones </th>
                                <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-center"  > Acciones </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="servicio in servicios" :key="servicio.id">
                                <td class="text-xs font-weight-bold text-start" data-type="date" >
                                    <input
                                        type="checkbox"
                                        v-model="seletedServicio"
                                        :value="servicio.id"
                                    />

                                    {{ formatDate(servicio.updated_at) }}
                                </td>
                                <td class="text-xs font-weight-bold text-start"> {{ servicio.nombre }} </td>
                                <td class="text-xs font-weight-bold text-truncate text-start" :title="servicio.observaciones" style="max-width: 100px" >  {{ servicio.observaciones }} </td>
                                <td class="text-xs font-weight-bold text-center">
                                <a
                                        @click="seletedServicio = servicio"
                                        role="button"
                                        data-bs-toggle="modal"
                                        data-bs-target="#updateServicioModal"
                                        ><i class="material-icons">edit</i></a
                                    >
                                    <a
                                        @click="
                                            confirmDelete(servicio.id, 'servicios')
                                        "
                                        role="button"
                                    >
                                        <i class="material-icons">delete</i>
                                    </a>
                                </td>
                            </tr>
                        </tbody>
                    </MaterialTable>

            </div>
            <!-- create servicio modal -->
            <CreateServicio />
            <!-- create servicio modal -->

            <!-- update servicio modal -->
            <UpdateServicio :servicio="seletedServicio" />
            <!-- update servicio modal -->

            <!-- productos -->
            <div v-if="page === 'productos_page'">
                <div
                    class="col d-flex justify-content-end mb-4"
                    data-bs-toggle="modal"
                    data-bs-target="#createProductoModal"
                >
                    <MaterialButton class="d-flex align-items-center">
                        <i class="material-icons pe-2">
                            <span class="material-symbols-outlined">add</span>
                        </i>
                        Agregar productos
                    </MaterialButton>
                </div>

                <MaterialTable title="PRODUCTOS">
                    <thead>
                        <tr>
                            <th
                                class="text-uppercase text-xxs font-weight-bolder opacity-7 text-start ps-1"
                            >
                            <input type="checkbox" v-model="selectAll" @change="toggleAllSelectionProductos" />
                                Fecha
                            </th>
                            <th
                                class="text-uppercase text-xxs font-weight-bolder opacity-7 text-start ps-1"
                            >
                                Nombre
                            </th>
                            <th
                                class="text-uppercase text-xxs font-weight-bolder opacity-7 text-start ps-1"
                            >
                                Precio
                            </th>
                            <th
                                class="text-uppercase text-xxs font-weight-bolder opacity-7 text-start ps-1"
                            >
                                Servicio
                            </th>
                            <th
                                class="text-uppercase text-xxs font-weight-bolder opacity- text-start ps-1"
                            >
                                Observaciones
                            </th>
                            <th
                                class="text-uppercase text-xxs font-weight-bolder opacity-7 text-center"
                            >
                                Acciones
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="producto in productos" :key="producto.id">
                            <td
                                class="text-xs font-weight-bold text-start"
                                data-type="date"
                            >
                            <input
                                    type="checkbox"
                                    v-model="seletedProducto"
                                    :value="producto.id"
                                />
                                {{ formatDate(producto.updated_at) }}
                            </td>
                            <td class="text-xs font-weight-bold text-start">
                                {{ producto.nombre }}
                            </td>
                            <td class="text-xs font-weight-bold text-start">
                                {{ formatCurrency(producto.precio) }}
                            </td>
                            <td class="text-xs font-weight-bold text-start">
                                {{ producto.servicio?.nombre }}
                            </td>
                            <td
                                class="text-xs font-weight-bold text-truncate text-start"
                                :title="producto.observaciones"
                                style="max-width: 100px"
                            >
                                {{ producto.observaciones }}
                            </td>
                            <td class="text-xs font-weight-bold text-center">
                                <a
                                    @click="seletedProducto = producto"
                                    role="button"
                                    data-bs-toggle="modal"
                                    data-bs-target="#updateProductoModal"
                                    ><i class="material-icons">edit</i></a
                                >
                                <a
                                    @click="
                                        confirmDelete(producto.id, 'productos')
                                    "
                                    role="button"
                                >
                                    <i class="material-icons">delete</i>
                                </a>
                                <a
                                    :href="route('productos.show', producto.id)"
                                    role="button"
                                    ><i class="material-icons">visibility</i></a
                                >
                            </td>
                        </tr>
                    </tbody>
                </MaterialTable>
            </div>
            <!-- create servicio modal -->
            <CreateProducto :servicios="servicios" />
            <!-- create servicio modal -->

            <!-- update servicio modal -->
            <UpdateProducto
                :servicios="servicios"
                :producto="seletedProducto"
            />
            <!-- update servicio modal -->
        </div>
    </AuthenticatedLayout>
</template>
