<script>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Swal from 'sweetalert2';
import axios from 'axios';
import MaterialTable from '@/Components/MaterialTable.vue';

export default {
    props: ["notificaciones", "ultimos_notificaciones", "user"],

    components: {
        AuthenticatedLayout,
        MaterialTable
    },
    data() {
        return {
        };
    },
    methods: {
        formatDate(dateString) {
            const options = { year: 'numeric', month: 'long', day: 'numeric' };
            return new Date(dateString).toLocaleDateString('es-ES', options); // This will display date only
        },
        async confirmDelete(notificacionId) {
            try {
                const result = await Swal.fire({
                    title: '¿Estás seguro?',
                    text: "¡No podrás revertir esto!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, elimínalo!',
                    cancelButtonText: 'Cancelar'
                });

                if (result.isConfirmed) {
                    // Perform the delete operation
                    await axios.get(`/notificaciones/${notificacionId}/delete`);

                    Swal.fire(
                        '¡Eliminado!',
                        'Tu archivo ha sido eliminado.',
                        'success'
                    ).then(() => {
                        // Reload the page to reflect changes
                        window.location.reload();
                    });;
                }
            } catch (error) {
                Swal.fire(
                    'Error!',
                    'Ocurrió un error al eliminar el ítem.',
                    'error'
                );
                console.error('Delete failed:', error);
            }
        }
    }
};
</script>

<template>
    <AuthenticatedLayout :user="user" :title="'Lista de Notificaciones'">

        <div class="container-fluid overflow-hidden">
            <div class="row" id="nav">
                <a class="d-flex text-decoration-none fs-5" href="javascript:history.go(-1);" role="button"><i
                        class="material-icons pe-2 pt-1"><span
                            class="material-symbols-outlined">arrow_back</span></i>Volver</a>
            </div>

            <MaterialTable title="notificaciones">
                <thead>
                    <tr>
                        <th class="text-uppercase text-xxs font-weight-bolder opacity-7">Fecha</th>
                        <th class="text-uppercase text-xxs font-weight-bolder opacity-7">Título</th>
                        <th class="text-uppercase text-xxs font-weight-bolder opacity-7">Contenido</th>
                        <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="notificacion in notificaciones" :key="notificacion.id" role="button">
                        <td class="text-xs font-weight-bold" data-type="date">{{ formatDate(notificacion.updated_at) }}</td>
                        <td class="text-xs font-weight-bold">{{ notificacion.title }}</td>
                        <td class="text-xs font-weight-bold">{{ notificacion.content }}</td>

                        <td class="text-xs font-weight-bold">
                            <button @click="confirmDelete(notificacion.id)"
                                class="btn btn-link text-secondary delete-button">
                                <i class="material-icons">delete</i>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </MaterialTable>
        </div>
    </AuthenticatedLayout>
</template>
