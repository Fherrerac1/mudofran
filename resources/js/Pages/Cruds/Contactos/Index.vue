<script>
import MaterialButton from "@/Components/MaterialButton.vue";
import MaterialTable from "@/Components/MaterialTableGeneral.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Swal from "sweetalert2";
import MaterialSelect from '@/Components/MaterialSelect.vue';
import Datepicker from "@vuepic/vue-datepicker";
import "@vuepic/vue-datepicker/dist/main.css";

export default {
    components: {
        MaterialButton,
        AuthenticatedLayout,
        MaterialTable,
        MaterialSelect,
        Datepicker,
    },
    props: {
        contactos: { type: Array, required: true, },
        user: { type: Object, required: true, },
    },
    data() {
        return {
            dateRange: [],
            selected_correo: null,
            selected_nombre: null,
            selected_dni: null,
        };
    },
    computed: {
        filteredcontactos() {
            return this.contactos.filter((contacto) => {

                // Filtro por fecha
                const matchFecha =
                    !(
                        Array.isArray(this.dateRange) &&
                        this.dateRange.length === 2 &&
                        this.dateRange[0] &&
                        this.dateRange[1]
                    ) ||
                    (() => {
                        const createdAt = new Date(contacto.created_at).setHours(0, 0, 0, 0);
                        const [start, end] = this.dateRange.map(d =>
                            new Date(d).setHours(0, 0, 0, 0)
                        );
                        return createdAt >= start && createdAt <= end;
                    })();

                // Filtro por correo
                const matchCorreo = !this.selected_correo || contacto.email === this.selected_correo;

                // Filtro por nombre
                const matchNombre = !this.selected_nombre || contacto.nombre === this.selected_nombre;

                // Filtro por DNI
                const matchDni = !this.selected_dni || contacto.dni === this.selected_dni;

                return matchFecha && matchCorreo && matchNombre && matchDni;

            });
        },
        /**
         * @return {Array<{value: string, text: string}>}
         */
        emailOptions() {
            const correos = this.contactos
                .map(c => ({ value: c.email, text: c.email }))
                .filter(c => !!c.value); // elimina nulos/vacíos

            return Array.from(new Map(correos.map(e => [e.value, e])).values());
        },
        /**
         * @return {Array<{value: string, text: string}>}
         */
        nombreOptions() {
            const nombres = this.contactos
                .map(c => ({
                    value: c.nombre,
                    text: c.nombre
                }))
                .filter(c => !!c.value);

            return Array.from(new Map(nombres.map(n => [n.value, n])).values());
        },
        /**
         * @return {Array<{value: string, text: string}>}
         */
        dniOptions() {
            const dnis = this.contactos
                .map(c => ({ value: c.dni, text: c.dni }))
                .filter(c => !!c.value);

            return Array.from(new Map(dnis.map(d => [d.value, d])).values());
        },
        filteredcontactosRenderKey() {
            return [
                this.selected_nombre,
                this.selected_correo,
                this.selected_dni,
                this.dateRange?.[0],
                this.dateRange?.[1],
            ].join('-');
        },
    },
    methods: {
        ConvertirACliente(contacto) {
            location.href = route('clientes.create', [contacto]);
        },
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
                    this.deletecontacto(id);
                }
            });
        },
        deletecontacto(id) {
            axios
                .get(`/contactos/${id}/delete`)
                .then((response) => {
                    Swal.fire(
                        "Eliminado!",
                        "El contacto ha sido eliminado.",
                        "success"
                    ).then(() => {
                        // Reload the page to reflect changes
                        window.location.reload();
                    });
                })
                .catch((error) => {
                    Swal.fire(
                        "Error!",
                        "Hubo un problema al eliminar El contacto.",
                        "error"
                    );
                });
        },
    },
};
</script>

<template>
    <AuthenticatedLayout :user="user" :title="'Lista de contactos'">
        <div class="container-fluid overflow-visible">
            <div class="row" id="nav">
                <a class="col d-flex text-decoration-none fs-5" href="javascript:history.go(-1);" role="button"><i
                        class="material-icons pe-2 pt-1"><span
                            class="material-symbols-outlined">arrow_back</span></i>Volver</a>
                <div class="d-flex flex-column flex-md-row gap-3 justify-content-end">
                    <div class="col-12 col-md-auto d-flex justify-content-center justify-content-md-start">
                        <a :href="route('contactos.create')">
                            <MaterialButton class="d-flex align-items-center">
                                <i class="material-icons pe-2"><span
                                        class="material-symbols-outlined">add</span></i>Agregar contacto
                            </MaterialButton>
                        </a>
                    </div>
                </div>
            </div>

            <div class="row my-2">
                <div class="col-lg-3 mb-2">
                    <MaterialSelect id="select_name" v-model="selected_nombre" name="nombre"
                        placeholder="Filtrar por Nombre" :options="nombreOptions" />
                </div>

                <div class="col-lg-3 mb-2">
                    <MaterialSelect id="select_email" v-model="selected_correo" name="email"
                        placeholder="Filtrar por Correo" :options="emailOptions" />
                </div>

                <div class="col-lg-3 mb-2">
                    <MaterialSelect id="sekect_id" v-model="selected_dni" name="dni" placeholder="Filtrar por DNI"
                        :options="dniOptions" />
                </div>

                <div class="col-lg-3 mb-2">
                    <Datepicker v-model="dateRange" range placeholder="Rango de Fechas" :enable-time-picker="false"
                        :format="'dd/MM/yyyy'" locale="es" />
                </div>
            </div>

            <MaterialTable title="contactos" :key="filteredcontactosRenderKey" :classProp="'fade-in'">
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
                            Teléfono
                        </th>
                        <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-start ps-1">
                            Convertir a Cliente
                        </th>
                        <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-center">
                            Acciones
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="contacto in filteredcontactos" :key="contacto.id">
                        <td class="text-xs font-weight-bold text-start">
                            {{ contacto.nombre }}
                        </td>
                        <td class="text-xs font-weight-bold text-start">
                            {{ contacto.email }}
                        </td>
                        <td class="text-xs font-weight-bold text-start">
                            {{ contacto.dni }}
                        </td>
                        <td class="text-xs font-weight-bold text-start">
                            <a :href="'tel:' +
                                (contacto.telefono_mobile)">
                                {{
                                    contacto.telefono_mobile
                                }}
                            </a>
                        </td>
                        <td class="text-xs font-weight-bold text-start">
                            <button @click="ConvertirACliente(contacto)" class="btn style_color">Convertir a
                                Cliente</button>
                        </td>
                        <td class="text-xs font-weight-bold text-center">
                            <a :href="route('contactos.edit', contacto.id)"><i class="material-icons">edit</i></a>
                            <a v-if="user.rol == 'admin'" @click="confirmDelete(contacto.id)" role="button"><i
                                    class="material-icons">delete</i></a>
                        </td>
                    </tr>
                </tbody>
            </MaterialTable>
        </div>
    </AuthenticatedLayout>
</template>
