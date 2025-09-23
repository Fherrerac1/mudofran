<template>
    <!-- Add Cobro Modal -->
    <BootstrapModal -modal-id="facturae_modal" -modal-size="modal-lg" title="Facturae">
        <form @submit.prevent="submit">
            <div class="mb-3">
                <label class="form-label">¿Desea firmar la factura?</label>
                <div class="form-check">
                    <input class="form-check-input" type="radio" id="firmar" value="firmar" v-model="opcion">
                    <label class="form-check-label" for="firmar">Firmar</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" id="sin_firmar" value="sin_firmar" v-model="opcion">
                    <label class="form-check-label" for="sin_firmar">Sin firmar</label>
                </div>
            </div>

            <div v-if="opcion === 'firmar'" class="mb-3">
                <label for="certificado_pfx" class="form-label">Cargar Certificado (.pfx)</label>
                <input type="file" id="certificado_pfx" class="form-control border px-3" required
                    @change="handleFileUpload" accept=".pfx">
            </div>

            <div v-if="opcion === 'firmar'" class="mb-3">
                <label for="passphrase" class="form-label">Passphrase</label>
                <input type="text" id="passphrase" class="form-control border px-3" v-model="form.passphrase"
                    required />
            </div>

            <div v-if="opcion === 'firmar'" class="mb-3 form-switch">
                <input class="form-check-input" type="checkbox" id="faceB2B" v-model="form.faceB2B">
                <label class="form-check-label" for="faceB2B">Usar FaceB2B</label>
            </div>

            <MaterialButton type="submit" id="submitButton" class="mt-3" :is-disabled="form.processing">
                <span v-if="form.processing">
                    <i class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></i>
                </span>
                Guardar
            </MaterialButton>
        </form>
    </BootstrapModal>
</template>

<script>
import BootstrapModal from '@/Components/BootstrapModal.vue';
import MaterialButton from '@/Components/MaterialButton.vue';
import { useForm } from '@inertiajs/vue3';
import Swal from 'sweetalert2';

export default {
    components: {
        BootstrapModal,
        MaterialButton
    },
    props: {
        factura: Object,
    },
    data() {
        return {
            form: useForm({
                certificado_pfx: null,
                passphrase: '',
                faceB2B: false
            }),
            opcion: 'sin_firmar',
        };
    },
    methods: {
        handleFileUpload(event) {
            this.form.certificado_pfx = event.target.files[0];
        },
        submit() {
            Swal.fire({
                title: 'Cargando...',
                text: 'Estamos procesando tu solicitud, por favor espera.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            const formData = new FormData();
            formData.append('_method', 'POST');
            if (this.opcion === 'firmar' && this.form.certificado_pfx) {
                formData.append('certificado_pfx', this.form.certificado_pfx);
                formData.append('passphrase', this.form.passphrase);
                formData.append('faceB2B', this.form.faceB2B);
            }

            axios.post(route('facturae.download', [this.factura.hash, this.factura.id]), formData, {
                responseType: 'blob' // Important for file downloads
            })
                .then((response) => {
                    if (!response.data || response.data.size === 0) {
                        throw new Error('El archivo descargado está vacío.');
                    }

                    // Extract filename from headers (if available)
                    const contentDisposition = response.headers['content-disposition'];
                    let filename = 'facturae.xsig';
                    if (contentDisposition) {
                        const matches = contentDisposition.match(/filename="?([^"]+)"?/);
                        if (matches && matches.length > 1) {
                            filename = matches[1];
                        }
                    }

                    // Create and trigger the download
                    const blob = new Blob([response.data], { type: response.headers['content-type'] });
                    const url = URL.createObjectURL(blob);
                    const link = document.createElement('a');
                    link.href = url;
                    link.setAttribute('download', filename);
                    document.body.appendChild(link);
                    link.click();

                    // Cleanup
                    document.body.removeChild(link);
                    URL.revokeObjectURL(url);

                    Swal.fire({
                        icon: 'success',
                        title: 'Éxito',
                        text: '¡El archivo se descargó con éxito!'
                    });
                })
                .catch(async (error) => {
                    let errorMessage = 'Hubo un problema con la descarga. Inténtalo nuevamente.';

                    if (error.response) {
                        const contentType = error.response.headers['content-type'];
                        if (contentType && contentType.includes('application/json')) {
                            try {
                                const errorData = await error.response.data.text();
                                const parsedError = JSON.parse(errorData);
                                errorMessage = parsedError.error || parsedError.message || errorMessage;
                            } catch (e) {
                                console.error('Error parsing JSON response:', e);
                            }
                        } else {
                            errorMessage = `Error ${error.response.status}: ${error.response.statusText}`;
                        }
                    } else if (error.message) {
                        errorMessage = error.message;
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: errorMessage
                    });
                    console.error(error);
                });
        }
    }
};
</script>