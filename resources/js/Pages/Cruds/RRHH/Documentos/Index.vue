<script>
import MaterialButton from '@/Components/MaterialButton.vue';
import MaterialTable from "@/Components/MaterialTableGeneral.vue";
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Swal from 'sweetalert2';
import axios from 'axios';
import Create from './Create.vue';

export default {
    components: {
        MaterialTable,
        MaterialButton,
        AuthenticatedLayout,
        Create
    },
    props: {
        documentos: Array,
        users: Array,
        user: Object,
    },
    methods: {
        formatDate(dateString) {
            const options = { year: 'numeric', month: 'long', day: 'numeric' };
            return new Date(dateString).toLocaleDateString('es-ES', options);
        },
        confirmDelete(id) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: 'Esta acción no se puede deshacer.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
            }).then((result) => {
                if (result.isConfirmed) {
                    this.deleteDocumento(id);
                }
            });
        },
        deleteDocumento(id) {
            axios.get(`/documentos/${id}`)
                .then(() => {
                    Swal.fire('Eliminado', 'El documento fue eliminado.', 'success').then(() => {
                        window.location.reload();
                    });
                })
                .catch(() => {
                    Swal.fire('Error', 'No se pudo eliminar el documento.', 'error');
                });
        },
    },
};
</script>

<template>
    <AuthenticatedLayout :user="user" title="Lista de Documentos">
        <div class="container-fluid overflow-hidden">
            <div class="row mb-3">
                <div class="col">
                    <a href="javascript:history.back()" class="text-decoration-none fs-5">
                        <span class="material-icons align-middle me-1">arrow_back</span>
                        Volver
                    </a>
                </div>
                <div class="col d-flex justify-content-end">
                    <a data-bs-toggle="modal" data-bs-target="#createDocumento">
                        <MaterialButton>
                            <span class="material-icons me-2">add</span>
                            Crear Documento
                        </MaterialButton>
                    </a>
                </div>
            </div>

            <MaterialTable title="DOCUMENTOS">
                <thead>
                    <tr>
                        <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-start ps-1">Fecha</th>
                        <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-start ps-1">Título</th>
                        <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-center ps-1">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="documento in documentos" :key="documento.id">
                        <td class="text-xs font-weight-bold text-start">{{ formatDate(documento.created_at) }}</td>
                        <td class="text-xs font-weight-bold text-start">{{ documento.titulo }}</td>
                        <td class="text-xs font-weight-bold text-center">
                            <a :href="`/storage/${documento.archivo}`" target="_blank" class="me-2">
                                <span class="material-icons" title="Ver documento">visibility</span>
                            </a>
                            <!-- <a :href="`/documentos/${documento.id}/editar`" class="me-2">
                                <span class="material-icons" title="Editar">edit</span>
                            </a> -->
                            <a href="#" @click.prevent="confirmDelete(documento.id)">
                                <span class="material-icons text-danger" title="Eliminar">delete</span>
                            </a>
                        </td>
                    </tr>
                </tbody>
            </MaterialTable>
        </div>
        <Create :users="users" />
    </AuthenticatedLayout>
</template>
