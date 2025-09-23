<template>
    <BootstrapModal -modal-id="email_modal" -modal-size="modal-lg" title="Enviar al Cliente">
        <form @submit.prevent="sendEmail">

            <!-- Dynamic Email Inputs -->
            <div v-for="(email, index) in form.emails" :key="index" class="mb-2 d-flex align-items-center">
                <MaterialInput v-model="form.emails[index]" label="Email" type="email" placeholder="Ingrese email"
                    :id="'email' + index" class="me-2" :is-required="true" />
                <button type="button" class="btn btn-danger btn-sm m-0" @click="removeEmail(index)">
                    <i class="material-icons">delete</i>
                </button>
            </div>

            <!-- Add New Email Button -->
            <button type="button" class="btn btn-primary btn-sm mt-2" @click="addEmail">
                <i class="material-icons">add</i> Agregar Email
            </button>
            <hr>
            <!-- Submit Button -->
            <MaterialButton type="submit" id="submitButton" class="mt-3" :is-disabled="form.processing">
                <span v-if="form.processing">
                    <i class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></i>
                </span>
                Enviar Correos
            </MaterialButton>

        </form>
    </BootstrapModal>
</template>

<script>
import BootstrapModal from '@/Components/BootstrapModal.vue';
import MaterialButton from '@/Components/MaterialButton.vue';
import MaterialInput from '@/Components/MaterialInput.vue';
import Swal from 'sweetalert2';
import { useForm } from '@inertiajs/vue3';

export default {
    components: {
        BootstrapModal,
        MaterialInput,
        MaterialButton,
    },
    props: {
        presupuesto: {
            type: Object,
            required: true
        },
    },
    data() {
        return {
            form: useForm({
                emails: this.presupuesto.cliente?.email ? [this.presupuesto.cliente.email] : [''] // Ensure at least one input
            }),
        };
    },
    methods: {
        addEmail() {
            this.form.emails.push('');
        },
        removeEmail(index) {
            this.form.emails.splice(index, 1);
        },
        async sendEmail() {
            if (this.form.emails.some(email => email.trim() === '')) {
                Swal.fire("Error", "Por favor, complete todos los campos de correo.", "error");
                return;
            }

            const result = await Swal.fire({
                title: "¿Está seguro?",
                text: "Esto enviará un correo a los clientes ingresados.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Enviar",
                cancelButtonText: "Cancelar",
            });

            if (!result.isConfirmed) return;

            Swal.fire({
                title: 'Enviando...',
                text: 'Procesando su solicitud, por favor espere.',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading(),
            });

            this.form.post(`/presupuestos/${this.presupuesto.id}/send_email`, {
                preserveScroll: true,
                onSuccess: () => {
                    Swal.fire("Éxito", "Correos enviados exitosamente.", "success")
                        .then(() => {
                            location.reload(); // Recargar la página después de cerrar la alerta
                        });
                },
                onError: () => {
                    let message = 'No se pudo enviar el correo. Inténtelo nuevamente.';

                    if (this.form.errors && Object.keys(this.form.errors).length > 0) {
                        // Mostrar el primer error de validación (por simplicidad, solo mostramos el primero)
                        message = Object.values(this.form.errors)[0];
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: message || 'No se pudo enviar el correo. Inténtelo nuevamente.',
                    });
                },
            });
        }
    }
};
</script>
