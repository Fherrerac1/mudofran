<template>
    <div>

        <!-- Modal Component -->
        <div class="modal fade" id="LibroModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true"
            ref="modal">
            <div class="modal-dialog modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header style_color">
                        <h5 class="modal-title" id="exampleModalLabel">
                            Libro De Facturas Recibidas
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="m-auto container mt-3 rounded-2">
                            <form @submit.prevent="submitForm" class="p-3">
                                <!-- Add CSRF token for security if using Laravel -->
                                <div class="mb-3">
                                    <label for="startDate" class="form-label">Fecha de Inicio:</label>
                                    <input type="date" v-model="form.startDate" name="startDate" class="form-control"
                                        required />
                                </div>

                                <div class="mb-3">
                                    <label for="finishDate" class="form-label">Fecha de Fin:</label>
                                    <input type="date" v-model="form.finishDate" name="finishDate" class="form-control"
                                        required />
                                </div>

                                <div class="mb-3">
                                    <label for="exportType" class="form-label">Exportar como:</label>
                                    <select v-model="form.exportType" name="exportType" class="form-control">
                                        <option value="pdf">PDF</option>
                                        <option value="excel">Excel</option>
                                    </select>
                                </div>

                                <button type="submit" class="btn btn-primary mt-3">
                                    Enviar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { ref } from "vue";
import { useForm } from "@inertiajs/vue3";
import Swal from "sweetalert2";

export default {
    setup() {
        // Modal reference
        const modal = ref(null);

        // useForm for handling form state and submission
        const form = useForm({
            startDate: "",
            finishDate: "",
            exportType: "pdf", // default value
        });

        // Function to close the modal
        const closeModal = () => {
            const modalInstance = new bootstrap.Modal(modal.value);
            modalInstance.hide();
        };

        // Form submission
        const submitForm = () => {
            // Perform form validation or any other checks here

            // If valid, proceed with the form submission
            form.post(route('libro-de-facturas'), {
                onSuccess: () => {
                    // Show success alert using SweetAlert2
                    Swal.fire({
                        icon: "success",
                        title: "Formulario enviado",
                        text: "El formulario ha sido enviado con éxito.",
                    });

                    // Close the modal
                    closeModal();
                },
                onError: () => {
                    // Show error alert using SweetAlert2
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "Ha ocurrido un error al enviar el formulario.",
                    });
                },
            });
        };

        return {
            modal,
            form,
            closeModal,
            submitForm,
        };
    },
};
</script>
