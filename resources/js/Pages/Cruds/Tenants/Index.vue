<script>
import MaterialButton from "@/Components/MaterialButton.vue";
import MaterialTable from "@/Components/MaterialTableGeneral.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Swal from "sweetalert2";
import { Head } from "@inertiajs/vue3";

export default {
    components: {
        Head,
        MaterialButton,
        AuthenticatedLayout,
        MaterialTable,
    },
    props: {
        tenants: { type: Array, required: true },
        user: { type: Object, required: true },
    },
    methods: {
        formatDate(dateString) {
            const options = { year: "numeric", month: "long", day: "numeric" };
            return new Date(dateString).toLocaleDateString("es-ES", options);
        },
        confirmDelete(id) {
            // Generate a random confirmation code
            const code = "DELETE-" + Math.floor(1000 + Math.random() * 9000);

            Swal.fire({
                title: "⚠️ Confirmación requerida",
                html: `
            <p>Esta acción eliminará <strong>permanentemente</strong> la BASE DE DATOS del tenant seleccionado.</p>
            <p>No podrás revertirlo.</p>
            <p>Por favor, escribe el siguiente código para confirmar:</p>
            <strong>${code}</strong>
        `,
                input: "text",
                inputPlaceholder: "Escribe el código aquí",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#6c757d",
                confirmButtonText: "Sí, eliminar la base de datos",
                cancelButtonText: "Cancelar",
                reverseButtons: true,
                focusCancel: true,
                preConfirm: (inputValue) => {
                    if (inputValue !== code) {
                        Swal.showValidationMessage("El código no coincide. Inténtalo de nuevo.");
                        return false;
                    }
                    return true;
                },
            }).then((result) => {
                if (result.isConfirmed) {
                    this.deleteTenant(id);
                }
            });
        },
        deleteTenant(id) {
            axios
                .delete(route("tenants.destroy", id)) // Use DELETE HTTP verb
                .then((response) => {
                    Swal.fire(
                        "Eliminado!",
                        "El tenant ha sido eliminado.",
                        "success"
                    ).then(() => {
                        // Refresh page or remove tenant from list
                        window.location.reload();
                    });
                })
                .catch((error) => {
                    Swal.fire(
                        "Error!",
                        "Hubo un problema al eliminar el tenant.",
                        "error"
                    );
                });
        },
    },
};
</script>

<template>

    <AuthenticatedLayout :user="user" title="Aminstracion tenants">

        <div class="container-fluid overflow-visible">
            <div class="row" id="nav">
                <a class="col d-flex text-decoration-none fs-5" href="javascript:history.go(-1);" role="button">
                    <i class="material-icons pe-2 pt-1">
                        <span class="material-symbols-outlined">arrow_back</span>
                    </i>Volver
                </a>
                <div class="d-flex flex-column flex-md-row gap-3 justify-content-end">
                    <div class="col-12 col-md-auto d-flex justify-content-center justify-content-md-start">
                        <a :href="route('tenants.create')">
                            <MaterialButton class="d-flex align-items-center">
                                <i class="material-icons pe-2">
                                    <span class="material-symbols-outlined">add</span>
                                </i>Agregar tenant
                            </MaterialButton>
                        </a>
                    </div>
                </div>
            </div>

            <MaterialTable title="Tenants" :key="tenants.length" classProp="fade-in">
                <thead>
                    <tr>
                        <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-start ps-1">
                            Nombre (Slug)
                        </th>
                        <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-start ps-1">
                            API Token
                        </th>
                        <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-center ps-1">
                            Creado
                        </th>
                        <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-center">
                            Acciones
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="tenant in tenants" :key="tenant.id">
                        <td class="text-start ps-1">{{ tenant.slug }}</td>
                        <td class="text-start ps-1">{{ tenant.api_token }}</td>
                        <td class="text-center">{{ formatDate(tenant.created_at) }}</td>
                        <td class="text-center">
                            <a :href="`/tenants/${tenant.id}/edit`" class="btn btn-sm btn-secondary me-1">Editar</a>
                            <button @click="confirmDelete(tenant.id)" class="btn btn-sm btn-danger">Eliminar</button>
                        </td>
                    </tr>
                </tbody>
            </MaterialTable>
        </div>
    </AuthenticatedLayout>
</template>
