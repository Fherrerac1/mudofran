<template>
    <BootstrapModal -modal-id="GenerateOt_modal" -modal-size="modal-lg" title="Aceptar Presupuesto">
        <form @submit.prevent="submitForm" class="">

            <div class="modal-body">
                <div class="alert alert-warning text-white">¿Quieres Aceptar este presupuesto?</div>
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
import MaterialInput from '@/Components/MaterialInput.vue';
import MaterialSelect from '@/Components/MaterialSelect.vue';
import { useForm } from '@inertiajs/vue3';
import Swal from 'sweetalert2';

export default {
    components: {
        BootstrapModal,
        MaterialInput,
        MaterialButton,
        MaterialSelect,
    },
    props: {
        presupuesto: {
            type: Object,
            required: true
        },
        clientes: {
            type: Array,
            required: true,
        },
    },
    data() {
        return {
            form: useForm({
                ObraMode: false,
                estado: 2,
            }),
        };
    },
    mounted() {
    },
    methods: {
        submitForm() {
            Swal.fire({
                title: 'Cargando...',
                text: 'Estamos procesando tu solicitud, por favor espera.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            // Post form data to the backend
            this.form.put(route('presupuesto.estado', this.presupuesto.id), {
                onSuccess: () => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Éxito',
                        text: '¡El formulario se envió con éxito!',
                        confirmButtonText: 'Aceptar',
                    }).then(() => {
                        // Reload the page only after user confirms
                        location.reload();
                    });
                },
                onError: (error) => {
                    // Assign errors to display them in the form if needed
                    this.errors = error;

                    // Display detailed error messages if available
                    if (error) {
                        const errorMessages = Object.values(error).flat().join('<br>');
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            html: errorMessages,
                            confirmButtonText: 'Aceptar',
                        });
                    }
                },
                onFinish: () => {
                    // Stop loading animation without reloading immediately
                    Swal.close();
                }
            });
        }
    }

};
</script>
