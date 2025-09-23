<script>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Swal from 'sweetalert2';
import axios from "axios";
import MaterialTable from "@/Components/MaterialTableGeneral.vue";
import MaterialButton from "@/Components/MaterialButton.vue";

export default {
    props: ["activities", "user"],

    components: {
        MaterialTable,
        AuthenticatedLayout,
        MaterialButton
    },
    data() {
        return {};
    },
    methods: {
        formatDate(dateString) {
            const options = { year: 'numeric', month: 'long', day: 'numeric' };
            return new Date(dateString).toLocaleDateString('es-ES', options);
        },
        // Function to generate a random alphanumeric string
        generateRandomWord(length = 6) {
            const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
            let result = '';
            for (let i = 0; i < length; i++) {
                result += chars.charAt(Math.floor(Math.random() * chars.length));
            }
            return result;
        },
        async crearHistory() {
            // Generate a random word
            const randomWord = this.generateRandomWord();

            // Show the SweetAlert2 confirmation dialog
            const { value: inputWord } = await Swal.fire({
                title: 'Confirmar eliminación',
                html: `Escribe la siguiente palabra para confirmar: <strong>${randomWord}</strong>`,
                input: 'text',
                inputPlaceholder: 'Escribe la palabra aquí',
                showCancelButton: true,
                confirmButtonText: 'Confirmar',
                cancelButtonText: 'Cancelar',
                preConfirm: (inputValue) => {
                    if (!inputValue) {
                        Swal.showValidationMessage('Por favor, escribe la palabra para confirmar');
                        return false;
                    }
                    if (inputValue !== randomWord) {
                        Swal.showValidationMessage('La palabra de confirmación es incorrecta');
                        return false;
                    }
                    return inputValue;
                }
            });

            // If the inputWord is valid, proceed with the deletion
            if (inputWord) {
                try {
                    // Call the backend to delete the history
                    const response = await axios.get('/delete-history');
                    // Handle the success response
                    Swal.fire({
                        title: 'Eliminado',
                        text: 'El historial ha sido eliminado correctamente',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        // Reload the page after the user clicks 'OK'
                        location.reload();
                    });
                } catch (error) {
                    // Handle the error response
                    Swal.fire('Error', 'No se pudo eliminar el historial', 'error');
                }
            }
        }
    }
};
</script>


<template>
    <AuthenticatedLayout :user="user" :title="'Lista de Actividades'">

        <div class="container-fluid overflow-hidden">
            <div class="row mb-4" id="nav">
                <a class="col d-flex text-decoration-none fs-5" href="javascript:history.go(-1);" role="button">
                    <i class="material-icons pe-2 pt-1">
                        <span class="material-symbols-outlined">arrow_back</span>
                    </i>Volver
                </a>

                <div class="col d-flex justify-content-end"> <!-- "Crear History" button -->
                    <MaterialButton v-if="user.rol === 'admin'" @click="crearHistory" class="btn unique_bg">Eliminar
                        Todo</MaterialButton>
                </div>
            </div>

            <MaterialTable title="ACTIVIDADES">
                <thead>
                    <tr>
                        <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-start ps-1">Fecha</th>
                        <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-start ps-1">ID Usuario</th>
                        <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-start ps-1">Acción</th>
                        <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-start ps-1">Email</th>
                        <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-start ps-1">IP</th>
                        <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-start ps-1">User Agent</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="activity in activities" :key="activity.id">
                        <td class="text-xs font-weight-bold text-start" data-type="date">{{ formatDate(activity.updated_at) }}</td>
                        <td class="text-xs font-weight-bold text-start">{{ activity.user_id }}</td>
                        <td class="text-xs font-weight-bold text-start">{{ activity.action }}</td>
                        <td class="text-xs font-weight-bold text-start">{{ activity.email }}</td>
                        <td class="text-xs font-weight-bold text-start">{{ activity.ip_address }}</td>
                        <td class="text-xs font-weight-bold text-truncate text-start">{{ activity.user_agent }}</td>
                    </tr>
                </tbody>
            </MaterialTable>
        </div>
    </AuthenticatedLayout>
</template>
