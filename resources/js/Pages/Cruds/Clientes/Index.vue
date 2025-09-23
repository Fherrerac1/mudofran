<script>
import MaterialButton from "@/Components/MaterialButton.vue";
import MaterialTable from "@/Components/MaterialTableGeneral.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Modal347 from "./Relations/Modal347.vue";
import Swal from "sweetalert2";
import MaterialSelect from '@/Components/MaterialSelect.vue';
import Datepicker from "@vuepic/vue-datepicker";
import "@vuepic/vue-datepicker/dist/main.css";

export default {
    components: {
        MaterialButton,
        AuthenticatedLayout,
        MaterialTable,
        Modal347,
        MaterialSelect,
        Datepicker,
    },
    props: {
        clientes: { type: Array, required: true, },
        user: {  type: Object, required: true, },
    },
    data() {
        return {
            tipoCliente: "todos",
            dateRange: [],
            selected_correo: null,
            selected_nombre: null,
            selected_dni: null,
        };
    },
    computed: {
        /**
         * Devuelve un array con los clientes que coinciden con los filtros:
         * - categoría (si es 'todos' se muestran todos)
         * - rango de fechas (si no se selecciona se muestran todos)
         * - correo (si no se selecciona se muestran todos)
         * - nombre (si no se selecciona se muestran todos)
         * - DNI (si no se selecciona se muestran todos)
         */
        filteredClientes() {
            return this.clientes.filter((cliente) => {
                const category = cliente.category || '';

                // Filtro por categoría
                const matchCategoria = this.tipoCliente === 'todos' || category === this.tipoCliente;

                // Filtro por fecha
                const matchFecha =
                    !(
                        Array.isArray(this.dateRange) &&
                        this.dateRange.length === 2 &&
                        this.dateRange[0] &&
                        this.dateRange[1]
                    ) ||
                    (() => {
                        const createdAt = new Date(cliente.created_at).setHours(0, 0, 0, 0);
                        const [start, end] = this.dateRange.map(d =>
                            new Date(d).setHours(0, 0, 0, 0)
                        );
                        return createdAt >= start && createdAt <= end;
                    })();

                // Filtro por correo
                const matchCorreo = !this.selected_correo || cliente.email === this.selected_correo;

                // Filtro por nombre
                const matchNombre = !this.selected_nombre || cliente.nombre_completo === this.selected_nombre;

                // Filtro por DNI
                const matchDni = !this.selected_dni || cliente.dni === this.selected_dni;

                return matchCategoria && matchFecha && matchCorreo && matchNombre && matchDni;

            });
        },
        /**
         * Genera un array de correos únicos de la lista de clientes.
         * El array es de objetos con propiedades 'value' y 'text',
         * ambas con el valor del correo electrónico.
         *
         * Se utiliza para poblar el select de correos en el componente
         * de filtrado de clientes.
         *
         * @return {Array<{value: string, text: string}>}
         */
        emailOptions() {
            const correos = this.clientes
                .map(c => ({ value: c.email, text: c.email }))
                .filter(c => !!c.value); // elimina nulos/vacíos

            return Array.from(new Map(correos.map(e => [e.value, e])).values());
        },
        /**
         * Genera un array de nombres únicos de la lista de clientes.
         * El array es de objetos con propiedades 'value' y 'text',
         * ambas con el valor del nombre completo del cliente.
         *
         * Se utiliza para poblar el select de nombres en el componente
         * de filtrado de clientes.
         *
         * @return {Array<{value: string, text: string}>}
         */
        nombreOptions() {
            const nombres = this.clientes
                .map(c => ({
                    value: c.nombre_completo,
                    text: c.nombre_completo
                }))
                .filter(c => !!c.value);

            return Array.from(new Map(nombres.map(n => [n.value, n])).values());
        },
        /**
         * Genera un array de DNIs únicos de la lista de clientes.
         * El array es de objetos con propiedades 'value' y 'text',
         * ambas con el valor del DNI del cliente.
         *
         * Se utiliza para poblar el select de DNIs en el componente
         * de filtrado de clientes.
         *
         * @return {Array<{value: string, text: string}>}
         */
        dniOptions() {
            const dnis = this.clientes
                .map(c => ({ value: c.dni, text: c.dni }))
                .filter(c => !!c.value);

            return Array.from(new Map(dnis.map(d => [d.value, d])).values());
        },
        /**
         * Genera una clave única que cambia cuando lo hacen los filtros
         * para forzar el rerender de la tabla.
         */
        filteredClientesRenderKey() {
            return [
                this.tipoCliente,
                this.selected_nombre,
                this.selected_correo,
                this.selected_dni,
                this.dateRange?.[0],
                this.dateRange?.[1],
            ].join('-');
        },
    },
    methods: {
        formatDate(dateString) {
            const options = { year: "numeric", month: "long", day: "numeric" };
            return new Date(dateString).toLocaleDateString("es-ES", options);
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
                    this.deleteCliente(id);
                }
            });
        },
        deleteCliente(id) {
            axios
                .get(`/clientes/${id}/delete`)
                .then((response) => {
                    Swal.fire(
                        "Eliminado!",
                        "El Cliente ha sido eliminado.",
                        "success"
                    ).then(() => {
                        // Reload the page to reflect changes
                        window.location.reload();
                    });
                })
                .catch((error) => {
                    Swal.fire(
                        "Error!",
                        "Hubo un problema al eliminar El Cliente.",
                        "error"
                    );
                });
        },
        formatNombreCompleto(cliente) {
            if (!cliente) return '';

            const nombre = cliente.nombre || '';
            const apellido1 = cliente.apellido_1 || '';
            const apellido2 = cliente.apellido_2 || '';

            return [nombre, apellido1, apellido2]
                .filter(Boolean) // Elimina vacíos
                .join(' ')
                .trim();
        }
    },
};
</script>

<template>
    <AuthenticatedLayout :user="user" :title="'Lista de Clientes'">
        <div class="container-fluid overflow-visible">
            <div class="row" id="nav">
                <a class="col d-flex text-decoration-none fs-5" href="javascript:history.go(-1);" role="button"><i
                        class="material-icons pe-2 pt-1"><span
                            class="material-symbols-outlined">arrow_back</span></i>Volver</a>
                <div class="d-flex flex-column flex-md-row gap-3 justify-content-end">
                    <div class="col-12 col-md-auto d-flex justify-content-center justify-content-md-end gap-3">
                        <MaterialButton data-bs-toggle="modal" data-bs-target="#Modal347-clientes"
                            class="d-flex justify-content-start align-items-center">
                            <i class="material-icons pe-2">save</i>m.347
                        </MaterialButton>
                    </div>
                    <div class="col-12 col-md-auto d-flex justify-content-center justify-content-md-start">
                        <a :href="route('clientes.create')">
                            <MaterialButton class="d-flex align-items-center">
                                <i class="material-icons pe-2"><span
                                        class="material-symbols-outlined">add</span></i>Agregar Cliente
                            </MaterialButton>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Tipo de Cliente (Personal/Empresa) -->
            <div class="form-group col-12 col-md-4 mb-4">
                <label class="form-label fw-bold mb-2 w-100">Tipo de Cliente</label>
                <div class="btn-group w-100" role="group">
                    <input type="radio" class="btn-check" id="todos" name="category" value="todos"
                        v-model="tipoCliente" />
                    <MaterialButton :active="tipoCliente === 'todos'" @click="tipoCliente = 'todos'">Todos
                    </MaterialButton>

                    <input type="radio" class="btn-check" id="personal" name="category" value="Personal"
                        v-model="tipoCliente" />
                    <MaterialButton :active="tipoCliente === 'Personal'" @click="tipoCliente = 'Personal'">Persona
                    </MaterialButton>

                    <input type="radio" class="btn-check" id="empresa" name="category" value="Empresa"
                        v-model="tipoCliente" />
                    <MaterialButton :active="tipoCliente === 'Empresa'" @click="tipoCliente = 'Empresa'">Empresa
                    </MaterialButton>
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-lg-3 mb-2">
                    <MaterialSelect id="nombre"
                        v-model="selected_nombre"
                        name="nombre"
                        placeholder="Filtrar por Nombre"
                        :options="nombreOptions"
                    />
                </div>

                <div class="col-lg-3 mb-2">
                    <MaterialSelect id="correo"
                        v-model="selected_correo"
                        name="email"
                        placeholder="Filtrar por Correo"
                        :options="emailOptions"
                    />
                </div>

                <div class="col-lg-3 mb-2">
                    <MaterialSelect id="dni"
                        v-model="selected_dni"
                        name="dni"
                        placeholder="Filtrar por DNI"
                        :options="dniOptions"
                    />
                </div>

                <div class="col-lg-3 mb-2">
                    <Datepicker
                        v-model="dateRange"
                        range
                        placeholder="Rango de Fechas"
                        :enable-time-picker="false"
                        :format="'dd/MM/yyyy'"
                        locale="es"
                    />
                </div>
            </div>

            <MaterialTable title="CLIENTES" :key="filteredClientesRenderKey" :classProp="'fade-in'">
                <thead>
                    <tr>
                        <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-start ps-1">
                            Nombre
                        </th>
                        <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-start ps-1">
                            Email
                        </th>
                        <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-start ps-1">
                            DNI/NIF
                        </th>
                        <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-start ps-1">
                            Tipo de Cliente
                        </th>
                        <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-start ps-1">
                            Teléfono
                        </th>
                        <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-start ps-1">
                            Municipio
                        </th>
                        <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-center">
                            Acciones
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="cliente in filteredClientes" :key="cliente.id">
                        <td class="text-xs font-weight-bold text-start">
                            {{ formatNombreCompleto(cliente) }}
                        </td>
                        <td class="text-xs font-weight-bold text-start">
                            {{ cliente.email }}
                        </td>
                        <td class="text-xs font-weight-bold text-start">
                            {{ cliente.dni }}
                        </td>
                        <td class="text-xs font-weight-bold text-start">
                            {{ cliente.category }}
                        </td>
                        <td class="text-xs font-weight-bold text-start">
                            <a :href="'tel:' +
                                (cliente.telefono_mobile ||
                                    cliente.telefono_fijo)
                                ">
                                {{
                                    cliente.telefono_mobile ||
                                    cliente.telefono_fijo
                                }}
                            </a>
                        </td>
                        <td class="text-xs font-weight-bold text-start">
                            {{ cliente.localidad }}
                        </td>
                        <td class="text-xs font-weight-bold text-center">
                            <a :href="route('clientes.edit', cliente.id)"><i class="material-icons">edit</i></a>
                            <a v-if="user.rol == 'admin'" @click="confirmDelete(cliente.id)" role="button"><i
                                    class="material-icons">delete</i></a>
                            <a :href="'/clientes/' + cliente.id + '/mostrar'" role="button"><i
                                    class="material-icons">visibility</i></a>
                        </td>
                    </tr>
                </tbody>
            </MaterialTable>
        </div>
        <Modal347 />
    </AuthenticatedLayout>
</template>
