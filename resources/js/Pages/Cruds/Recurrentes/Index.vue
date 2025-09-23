    <script>
    import MaterialButton from '@/Components/MaterialButton.vue';
    import MaterialTable from "@/Components/MaterialTableGeneral.vue";
    import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
    import MaterialSelect from '@/Components/MaterialSelect.vue';
    import Swal from 'sweetalert2';
    import Datepicker from "@vuepic/vue-datepicker";
    import "@vuepic/vue-datepicker/dist/main.css";
    import FacturaRecurrenteModalUpdate from './FacturaRecurrenteModalUpdate.vue';
    import CreateRecurrentes from './CreateRecurrentes.vue';

    export default {
        components: {
            MaterialTable,
            MaterialButton,
            AuthenticatedLayout,
            MaterialSelect,
            Datepicker,
            FacturaRecurrenteModalUpdate,
            CreateRecurrentes
        },

        data() {
            return {
                dateRange: [],
                selected_numFactura: null,
                selected_cliente: null,
                selected_frecuencia: null,
                selected_factura: null,
                selected_factura_recurrente: null,
            };
        },
        props: {
            facturas: { type: Array, required: true },
            facturasRecurrentes: { type: Array, required: true },
            user: { type: Object, required: true },
            nombresFacturas: { type: Object, default: () => ({}) },
            nombresClientes: { type: Object, default: () => ({}) },
            frecuencias: { type: Object, default: () => [] }, // Esperaba un objecto, no un array
        },
        computed: {
            /**
             * Filtra las facturas recurrentes según los selectores de fecha,
             *
             * Primero se filtran las facturas que coinciden con los selectores
             * y luego se ordenan por fecha descendente y, en caso de empate,
             * por número de factura descendente.
             *
             * @return {Array}
             */
            filteredFacturasRecurrentes() {
                let result = this.facturasRecurrentes;

                // 🔹 Filtro por fecha
                if (
                    Array.isArray(this.dateRange) &&
                    this.dateRange.length === 2 &&
                    this.dateRange[0] &&
                    this.dateRange[1]
                ) {
                    const [startDate, endDate] = this.dateRange.map(d =>
                        new Date(d).setHours(0, 0, 0, 0)
                    );

                    result = result.filter((factura) => {
                        const fecha = new Date(factura.fechaInicio).setHours(0, 0, 0, 0);
                        return fecha >= startDate && fecha <= endDate;
                    });
                }

                // 🔹 Filtro por número de factura
                if (this.selected_numFactura !== null) {
                    result = result.filter(f => f.id === this.selected_numFactura);
                }

                // 🔹 Filtro por cliente
                if (this.selected_cliente !== null) {
                    result = result.filter(f => f.factura?.cliente?.id === this.selected_cliente);
                }

                // 🔹 Filtro por frecuencia
                if (this.selected_frecuencia !== null) {
                    result = result.filter(f => f.frecuencia === this.selected_frecuencia);
                }

                return result;
            },
            /**
             * Genera una clave única que cambia cuando lo hacen los filtros
             * para forzar el rerender de la tabla.
             * @returns {String} Clave para re renderizar la tabla de facturas recurrentes.
             */
            filteredFacturasRecurrentesRenderKey() {
                return `${this.selected_numFactura}-${this.selected_cliente}-${this.selected_frecuencia}-${this.dateRange}`;
            }
        },
        methods: {
            formatDate(dateString) {
                const options = { year: 'numeric', month: 'long', day: 'numeric' };
                return new Date(dateString).toLocaleDateString('es-ES', options);
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
                        this.deletefactura(id);
                    }
                });
            },
            deletefactura(id) {
                axios.get(`/recurrentes/${id}/delete`)
                    .then(response => {
                        Swal.fire(
                            'Eliminado!',
                            'el factura ha sido eliminado.',
                            'success'
                        ).then(() => {
                            // Reload the page to reflect changes
                            window.location.reload();
                        });
                    })
                    .catch(error => {
                        Swal.fire(
                            'Error!',
                            'Hubo un problema al eliminar el factura.',
                            'error'
                        );
                    });
            },
            formatCurrency(amount) {
                return new Intl.NumberFormat('es-ES', {
                    style: 'currency',
                    currency: 'EUR',
                    minimumFractionDigits: 2
                }).format(amount);
            },
        },

    }
</script>

    <template>
        <AuthenticatedLayout :user="user" :title="'Lista de facturas'">
            <div class="container-fluid overflow-visible">
                <div class="row" id="nav">
                    <a class="col d-flex text-decoration-none fs-5" href="javascript:history.go(-1);" role="button">
                        <i class="material-icons pe-2 pt-1">
                            <span class="material-symbols-outlined">arrow_back</span>
                        </i>Volver
                    </a>

                    <div class="col-12 col-md-auto d-flex justify-content-center justify-content-md-end gap-3"> <a
                            href="#" class="btn unique_bg" data-bs-toggle="modal"
                            data-bs-target="#create-recurrentes-Modal">Agregar</a>
                    </div>
                </div>

                <!-- Filtro por fecha -->
                <div class="row mb-3">
                    <div class="col-lg-3 mt-2">
                        <MaterialSelect id="select-num-factura" v-model="selected_numFactura"
                            placeholder="Filtrar por Factura" :options="Object.entries(nombresFacturas).map(([id, numero]) => ({
                                value: parseInt(id),
                                text: numero
                            }))" />
                    </div>

                    <div class="col-lg-3 mt-2">
                        <MaterialSelect id="select-cliente" v-model="selected_cliente" placeholder="Filtrar por Cliente"
                            :options="Object.entries(nombresClientes).map(([id, nombre]) => ({
                                value: parseInt(id),
                                text: nombre
                            }))" />
                    </div>

                    <div class="col-lg-3 mt-2">
                        <MaterialSelect id="select-frecuencia" v-model="selected_frecuencia"
                            placeholder="Filtrar por Frecuencia" :options="frecuencias" />
                    </div>

                    <div class="col-lg-3 mt-2">
                        <Datepicker v-model="dateRange" range placeholder="Filtrar por Fecha"
                            :enable-time-picker="false" :format="'dd/MM/yyyy'" locale="es" />
                    </div>
                </div>

                <MaterialTable :key="filteredFacturasRecurrentesRenderKey" title="FACTURAS RECURRENTES">
                    <thead>
                        <tr>
                            <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-start ps-1">Fecha
                                Inicio</th>
                            <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-start ps-1">Fecha Fin
                            </th>
                            <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-start ps-1">Nº Factura
                                Nativa</th>
                            <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-start ps-1">Cliente
                            </th>
                            <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-start ps-1">
                                Repeticiones Actuales</th>
                            <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-start ps-1">Próxima
                                Factura</th>
                            <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="factura in filteredFacturasRecurrentes" :key="factura.id" role="button">
                            <td class="text-xs font-weight-bold text-start" data-type="date">
                                {{ formatDate(factura.fechaInicio) }}
                            </td>
                            <td class="text-xs font-weight-bold text-start" data-type="date">
                                {{ factura.fechaFin ? formatDate(factura.fechaFin) : '-' }}
                            </td>

                            <td class="text-xs font-weight-bold text-start">
                                {{ factura.factura?.numFactura || 'blanco/vacio' }}
                            </td>

                            <td class="text-xs font-weight-bold text-start">
                                {{ factura.factura?.cliente?.nombre }} {{ factura.factura?.cliente?.apellido_1 }} {{
                                    factura.factura?.cliente?.apellido_2 }}
                            </td>

                            <td class="text-xs font-weight-bold text-start">
                                {{ factura.repeticiones_actuales }}
                            </td>
                            <td class="text-xs font-weight-bold text-start" data-type="date">
                                {{ factura.proxima_fecha ? formatDate(factura.proxima_fecha) : "Pendiente" }}
                            </td>
                            <td class="text-xs font-weight-bold text-center">
                                <a href="#" data-bs-toggle="modal" data-bs-target="#facturas-recurrentes-update-modal"
                                    @click="selected_factura_recurrente = factura;">
                                    <i class="material-icons">edit</i>
                                </a>


                                <a @click="confirmDelete(factura.id)">
                                    <i class="material-icons">delete</i>
                                </a>
                            </td>
                        </tr>
                    </tbody>
                </MaterialTable>
            </div>


            <FacturaRecurrenteModalUpdate :facturas_recurrente="selected_factura_recurrente || {}" :user="user" />
            <CreateRecurrentes :facturas="facturas" />

        </AuthenticatedLayout>

    </template>
