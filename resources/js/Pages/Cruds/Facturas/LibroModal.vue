<template>
    <BootstrapModal -modal-id="LibroModal" -modal-size="modal-lg" title="Libro De Facturas Recibidas">
        <form @submit.prevent="submitForm" class="p-3">
            <!-- Add CSRF token for security if using Laravel -->
            <div class="row">
                <!-- Start Date -->
                <div class="col-lg-6 mt-3">
                    <label for="startDate" class="form-label">Fecha de Inicio</label>
                    <input type="date" id="startDate" name="startDate" class="form-control border px-3"
                        v-model="form.startDate" required>
                </div>

                <!-- Finish Date -->
                <div class="col-lg-6 mt-3">
                    <label for="finishDate" class="form-label">Fecha de Fin</label>
                    <input type="date" id="finishDate" name="finishDate" class="form-control border px-3"
                        v-model="form.finishDate" required>
                </div>

                <!-- Sorting Options -->
                <div class="col-lg-6 mt-3">
                    <label class="form-label">Ordenar por:</label>
                    <div class="form-check">
                        <input type="radio" id="sortingOptionFecha" name="sortingOption" value="fecha"
                            v-model="form.sortingOption" class="form-check-input" required>
                        <label for="sortingOptionFecha" class="form-check-label">Fecha</label>
                    </div>
                    <div class="form-check">
                        <input type="radio" id="sortingOptionNumFactura" name="sortingOption" value="numFactura"
                            v-model="form.sortingOption" class="form-check-input">
                        <label for="sortingOptionNumFactura" class="form-check-label">Número de Factura</label>
                    </div>
                    <div class="form-check">
                        <input type="radio" id="sortingOptionSerie" name="sortingOption" value="serie"
                            v-model="form.sortingOption" class="form-check-input">
                        <label for="sortingOptionSerie" class="form-check-label">Serie</label>
                    </div>
                </div>

                <!-- Series Selection -->
                <div class="col-lg-6 mt-3">
                    <label class="form-label">Series:</label>
                    <div class="form-check">
                        <input type="checkbox" id="serie1" name="series[]" value="1" v-model="form.series"
                            class="form-check-input" checked>
                        <label for="serie1" class="form-check-label">Serie 1</label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" id="serie2" name="series[]" value="2" v-model="form.series"
                            class="form-check-input" checked>
                        <label for="serie2" class="form-check-label">Serie 2</label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" id="serie5" name="series[]" value="5" v-model="form.series"
                            class="form-check-input" checked>
                        <label for="serie5" class="form-check-label">Serie 5</label>
                    </div>
                </div>

                <!-- Export Options -->
                <div class="col-lg-6 mt-3">
                    <label class="form-label">Exportar como:</label>
                    <div class="form-check form-check-inline">
                        <input type="radio" id="pdf" value="pdf" v-model="form.exportType" name="exportType"
                            class="form-check-input">
                        <label for="pdf" class="form-check-label">PDF</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input type="radio" id="excel" value="excel" v-model="form.exportType" name="exportType"
                            class="form-check-input">
                        <label for="excel" class="form-check-label">Excel</label>
                    </div>
                </div>
            </div>

            <MaterialButton :disabled="form.processing" type="submit" class="btn btn-primary mt-3">
                Enviar
            </MaterialButton>
        </form>
    </BootstrapModal>
</template>

<script>
import axios from 'axios';
import Swal from 'sweetalert2';
import { useForm } from "@inertiajs/vue3";
import BootstrapModal from "@/Components/BootstrapModal.vue";
import MaterialButton from '@/Components/MaterialButton.vue';
import MaterialInput from "@/Components/MaterialInput.vue";

export default {
    components: {
        BootstrapModal,
        MaterialButton,
        MaterialInput
    },
    data() {
        return {
            form: useForm({
                startDate: "",
                finishDate: "",
                exportType: "pdf",
                series: [],
                sortingOption: '',
            })
        };
    },
    methods: {
        closeModal() {
            const modalInstance = new bootstrap.Modal(this.$refs.modal);
            modalInstance.hide();
            // Remove the "show" class from modal-backdrop
            const backdrop = document.querySelector('.modal-backdrop');
            if (backdrop) {
                backdrop.classList.remove('show');
                backdrop.classList.add('d-none');
            }
        },
        async submitForm() {
            try {
                const response = await axios.post(route('libro-de-facturas'), this.form, {
                    responseType: 'blob', // Ensures response is treated as a file
                });

                // Check if response contains an error (JSON)
                const contentType = response.headers['content-type'];
                if (contentType.includes('application/json')) {
                    const reader = new FileReader();
                    reader.onload = () => {
                        try {
                            const jsonResponse = JSON.parse(reader.result);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: jsonResponse.error || 'Hubo un problema al procesar la solicitud.',
                            });
                        } catch (err) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'No se pudo leer el mensaje de error del servidor.',
                            });
                        }
                    };
                    reader.readAsText(response.data);
                    return;
                }

                // Create a Blob from the response data
                const blob = new Blob([response.data], { type: contentType });

                // Create a link element, set the URL, and trigger the download
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);

                // Determine the filename from response headers (if available)
                const contentDisposition = response.headers['content-disposition'];
                let filename = 'exported_file.pdf';
                if (contentDisposition) {
                    const match = contentDisposition.match(/filename="?([^"]+)"?/);
                    if (match) filename = match[1];
                }

                link.download = filename;
                document.body.appendChild(link);
                link.click();

                // Cleanup
                document.body.removeChild(link);
                URL.revokeObjectURL(link.href);

                Swal.fire({
                    icon: 'success',
                    title: 'Descarga completada',
                    text: 'El archivo se ha descargado con éxito.',
                });

            } catch (error) {
                let errorMessage = 'Hubo un problema al descargar el archivo.';

                if (error.response) {
                    if (error.response.data instanceof Blob && error.response.data.type === "application/json") {
                        const reader = new FileReader();
                        reader.onload = () => {
                            try {
                                const jsonResponse = JSON.parse(reader.result);
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: jsonResponse.error || 'Error desconocido.',
                                });
                            } catch (err) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'No se pudo leer el mensaje de error del servidor.',
                                });
                            }
                        };
                        reader.readAsText(error.response.data);
                        return;
                    } else if (error.response.data.error) {
                        errorMessage = error.response.data.error;
                    }
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: errorMessage,
                });
            }
        }
    }
};
</script>
