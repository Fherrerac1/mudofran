<script>
import { Head } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Breadcrumb from '@/Components/Breadcrumb.vue';
import placeholder from '@/assets/img/placeholder.jpg';
import MaterialTable from "@/Components/MaterialTableGeneral.vue";
import MaterialButton from "@/Components/MaterialButton.vue";
import MaterialSelect from '@/Components/MaterialSelect.vue';
import Swal from "sweetalert2";
import DataTable from "@/Components/DataTable.vue";
import * as XLSX from 'xlsx';

export default {
    components: {
        Head,
        AuthenticatedLayout,
        Breadcrumb,
        placeholder,
        MaterialTable,
        MaterialButton,
        DataTable,
        MaterialSelect,
    },
    props: {
        usuarios: Array,
        user: Object,
        roles: { type: Array, default: () => [], },
    },
    data() {
        return {
            breadcrumbItems: [
                { label: 'Dashboard', url: this.route('dashboard') },
                { label: 'Usuarios' },
            ],
            selected_nombre: null,
            selected_email: null,
            selected_dni: null,
            selected_rol: null,
            selected_aparicion: null,
        };
    },
    computed: {
        filteredUsuarios() {
            let result = this.usuarios;

            // 🔹 Filtro por nombre
            if (this.selected_nombre !== null) {
                result = result.filter(usuario =>
                    usuario.name === this.selected_nombre
                );
            }

            // 🔹 Filtro por email
            if (this.selected_email !== null) {
                result = result.filter(usuario =>
                    usuario.email === this.selected_email
                );
            }

            // 🔹 Filtro por DNI
            if (this.selected_dni !== null) {
                result = result.filter(usuario =>
                    usuario.dni === this.selected_dni
                );
            }

            // 🔹 Filtro por rol
            if (this.selected_rol !== null) {
                result = result.filter(usuario =>
                    usuario.rol === this.selected_rol
                );
            }

            // 🔹 Filtro por aparición
            if (this.selected_aparicion !== null) {
                result = result.filter(usuario =>
                    this.selected_aparicion === 'activo'
                        ? usuario.last_appearance === 'activo'
                        : usuario.last_appearance !== 'activo'
                );
            }

            return result;
        },
        usuarioNombreOptions() {
            const unique = Array.from(
                new Set(this.usuarios.map(u => u.name).filter(Boolean))
            );
            return unique.map(name => ({
                value: name,
                text: name,
            }));
        },
        usuarioEmailOptions() {
            const unique = Array.from(
                new Set(this.usuarios.map(u => u.email).filter(Boolean))
            );
            return unique.map(email => ({
                value: email,
                text: email,
            }));
        },
        usuarioDniOptions() {
            const unique = Array.from(
                new Set(this.usuarios.map(u => u.dni).filter(Boolean))
            );
            return unique.map(dni => ({
                value: dni,
                text: dni,
            }));
        },
        usuarioRolOptions() {
            return this.roles.map(r => ({
                value: r.value,
                text: r.text,
            }));
        },
        filteredUsuariosRenderKey() {
            return `${this.selected_nombre}-${this.selected_email}-${this.selected_dni}-${this.selected_rol}`;
        },
    },
    methods: {
        exportToExcel() {
            const worksheet = XLSX.utils.json_to_sheet(this.usuarios);
            const workbook = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(workbook, worksheet, "Usuarios");
            XLSX.writeFile(workbook, "Usuarios.xlsx");
        },
        createUser() {
            window.location = route('user.create');
        },
        formatDate(dateString) {
            const createdAt = new Date(dateString);
            return createdAt.toLocaleDateString(); // This will display date only
        },
        getArchivoUrl(archivo) {
            if (archivo != null && archivo != '') {
                if (typeof archivo === 'string') {
                    const modifiedArchivo = archivo.replace("public/", ""); // Remove 'public/' segment
                    return "/storage/" + modifiedArchivo;
                } else if (archivo instanceof File) {
                    return URL.createObjectURL(archivo);
                } else {
                    console.error("Invalid file type");
                    return placeholder; // Return placeholder in case of an invalid file type
                }
            } else {
                return placeholder; // Return placeholder when archivo is null
            }
        },
        confirmDelete(id) {
            // Initialize countdown variables
            let timerInterval;
            let disableButtonSeconds = 5; // Disable the button for 5 seconds
            let totalModalSeconds = 20; // Keep the modal open for 20 seconds (or as needed)

            // Generate a random code
            const randomCode = Math.random().toString(36).substring(2, 8).toUpperCase();

            Swal.fire({
                title: '¿Estás seguro?',
                html: `
                ¡No podrás revertir esta acción!<br>
                El usuario será eliminado junto con todos sus datos relacionados (facturas).<br>
                Para confirmar, ingresa el código <b>${randomCode}</b> en el campo abajo.<br>
                Confirmando en <b id="countdown">${disableButtonSeconds}</b> segundos...
                <input id="code-input" class="swal2-input" placeholder="Código de confirmación">`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, eliminarlo',
                cancelButtonText: 'Cancelar',
                showLoaderOnConfirm: true, // Show loader while processing
                timer: totalModalSeconds * 1000, // Keep the modal open for the specified duration
                timerProgressBar: true,
                didOpen: () => {
                    // Disable the confirm button when the modal opens
                    Swal.getConfirmButton().disabled = true;

                    // Create a timer that updates the countdown text every second
                    timerInterval = setInterval(() => {
                        disableButtonSeconds--;

                        if (disableButtonSeconds > 0) {
                            Swal.getHtmlContainer().querySelector('#countdown').textContent = disableButtonSeconds;
                        } else {
                            Swal.getHtmlContainer().querySelector('#countdown').textContent = '¡Botón habilitado!';
                        }

                        // Clear the interval when the countdown is over
                        if (disableButtonSeconds <= 0) {
                            clearInterval(timerInterval);
                        }
                    }, 1000);

                    // Watch for input changes to enable/disable the confirm button
                    const codeInput = Swal.getHtmlContainer().querySelector('#code-input');
                    codeInput.addEventListener('input', () => {
                        if (codeInput.value === randomCode) {
                            Swal.getConfirmButton().disabled = false;
                        } else {
                            Swal.getConfirmButton().disabled = true;
                        }
                    });
                },
                willClose: () => {
                    // Clear the interval when the modal closes
                    clearInterval(timerInterval);
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    // Call deleteUser function if user confirmed
                    this.deleteUser(id);
                }
            });
        },

        deleteUser(id) {
            axios.get(`/user/${id}/delete`)
                .then(response => {
                    Swal.fire(
                        '¡Eliminado!',
                        'El usuario ha sido eliminado junto con todos sus datos relacionados.',
                        'success'
                    ).then(() => {
                        // Reload the page to reflect changes
                        window.location.reload();
                    });
                })
                .catch(error => {
                    Swal.fire(
                        '¡Error!',
                        'Hubo un problema al eliminar el usuario.',
                        'error'
                    );
                });
        }
    }
}
</script>

<template>
    <AuthenticatedLayout :user="user" :title="'LISTA DE USUARIOS'">
        <div class="container-fluid overflow-visible">
            <div class="row">
                <Breadcrumb :items="breadcrumbItems" />
            </div>

            <div class="user d-flex align-items-center mb-4">
                <div class="col-12 d-flex justify-content-end gap-3">
                    <material-button @click="exportToExcel" class="btn btn-secondary">
                        Exportar a Excel
                    </material-button>

                    <material-button v-if="user.rol == 'admin' || user.rol == 'decano' || user.rol == 'gestor'"
                        class="float-right btn btm-sm" @click="createUser()">
                        <i class="fas fa-user-plus me-2"></i>
                        Crear Usuario
                    </material-button>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-lg-3 mb-2">
                    <MaterialSelect id="usuario-nombre" v-model="selected_nombre" name="usuario"
                        placeholder="Filtrar por Nombre" :options="usuarioNombreOptions" />
                </div>

                <div class="col-lg-3 mb-2">
                    <MaterialSelect id="usuario-email" v-model="selected_email" name="email"
                        placeholder="Filtrar por Email" :options="usuarioEmailOptions" />
                </div>

                <div class="col-lg-3 mb-2">
                    <MaterialSelect id="usuario-dni" v-model="selected_dni" name="dni" placeholder="Filtrar por DNI"
                        :options="usuarioDniOptions" />
                </div>

                <div class="col-lg-3 mb-2">
                    <MaterialSelect id="usuario-aparicion" v-model="selected_aparicion" name="aparicion"
                        placeholder="Filtrar por Última Aparición" :options="[
                            { text: 'Activos', value: 'activo' },
                            { text: 'No Activos', value: 'no_activo' }
                        ]" />
                </div>

                <div class="col-lg-3 mb-2" v-if="user.rol === 'admin' || user.rol === 'gestor'">
                    <MaterialSelect id="usuario-rol" v-model="selected_rol" name="rol" placeholder="Filtrar por Rol"
                        :options="usuarioRolOptions" />
                </div>

            </div>

            <MaterialTable :key="filteredUsuariosRenderKey" title="USUARIOS">
                <thead>
                    <tr>
                        <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-start ps-1">Fecha de
                            creación</th>
                        <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-start ps-1">Foto</th>
                        <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-start ps-1">Nombre</th>
                        <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-start ps-1">Email</th>
                        <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-start ps-1">DNI</th>
                        <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-start ps-1">Última
                            aparición</th>
                        <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-start ps-1">Rol</th>
                        <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="usuario in filteredUsuarios" :key="usuario.id" role="button">
                        <td class="text-xs font-weight-bold text-start" data-type="date">{{
                            formatDate(usuario.created_at) }}</td>
                        <td class="text-start align-middle">
                            <img style="width: 35px; height: 35px; object-fit: cover;" class="rounded-circle"
                                :src="getArchivoUrl(usuario.img)" alt="avatar">
                        </td>
                        <td class="text-xs font-weight-bold text-start">{{ usuario.name }}</td>
                        <td class="text-xs font-weight-bold text-start">{{ usuario.email }}</td>
                        <td class="text-xs font-weight-bold text-start">{{ usuario.dni || '-' }}</td>
                        <td class="text-xs font-weight-bold text-start">
                            <span class="text-lg" :class="{
                                'blinking-green text-success': usuario.last_appearance === 'activo',
                                'text-warning': usuario.last_appearance !== 'activo'
                            }"> ● </span> {{ usuario.last_appearance || '-' }}
                        </td>
                        <td class="text-xs font-weight-bold text-start">{{ usuario.rol }}</td>
                        <td class="text-xs font-weight-bold text-center">
                            <a v-if="user.rol === 'admin' ||
                                (user.rol === 'gestor' && usuario.rol !== 'admin') ||
                                (user.rol === 'decano' && usuario.rol !== 'admin' && usuario.rol !== 'gestor')"
                                :href="route('user.show', usuario.id)">
                                <i class="material-icons">visibility</i>
                            </a>
                            <a v-if="user.rol === 'admin' ||
                                (user.rol === 'gestor' && usuario.rol !== 'admin') ||
                                (user.rol === 'decano' && usuario.rol !== 'admin' && usuario.rol !== 'gestor')"
                                :href="'/user/' + usuario.id + '/edit'">
                                <i class="material-icons">edit</i>
                            </a>
                            <a role="button" @click="confirmDelete(usuario.id)" class="text-danger"
                                v-if="user.rol === 'admin' && usuario.rol !== 'admin'">
                                <i class="material-icons">delete</i>
                            </a>
                        </td>
                    </tr>
                </tbody>
            </MaterialTable>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.mb-custom-responsive {
    margin-bottom: -40px !important;
}

/* Solo en pantallas menores a 576px (móvil) */
@media (max-width: 800px) {
    .mb-custom-responsive {
        margin-bottom: 0 !important;
        padding-bottom: 10px !important;
    }
}


.loading-spinner {
    position: absolute;
    top: 50%;
    left: 50%;
}

.fa-spinner {
    font-size: 50px;
    /* Adjust size as needed */


}

.blinking-green {
    animation: blink-green 1.5s steps(1, start) infinite;
}

@keyframes blink-green {
    50% {
        opacity: 0;
    }
}
</style>
