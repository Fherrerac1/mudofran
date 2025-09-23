<template>
    <BootstrapModal :id="'passwordModal'" :ModalSize="'modal-lg modal-dialog-centered'" :title="'CAMBIAR LA CONTRASEÑA'">
        <!-- Advertencia -->
        <div class="text-center">
            <div
                class="d-inline-block py-2 px-4 rounded-pill shadow-sm mb-4"
                style="
                background: var(--bs-warning-pastel, #fff3cd);
                border: 1px solid var(--bs-warning-pastel-border, #ffeeba);
                color: var(--bs-warning-pastel-text, #856404);
                font-weight: 600;
                letter-spacing: 0.5px;
                "
            >
                <i class="fas fa-exclamation-triangle me-2"></i>
                Esta acción <strong>no es reversible</strong>. Verifica tu contraseña antes de continuar.
            </div>
        </div>

        <form enctype="multipart/form-data" @submit.prevent="submitpassword" class="reset-form container-sm px-2 py-3">
        <div class="form-group mb-3">
            <label for="current_password" class="form-label fw-semibold">Contraseña actual <span class="text-danger">*</span></label>
            <MaterialInputPassword
                v-model="form.current_password"
                type="password"
                id="current_password"
                name="current_password"
                :isRequired="true"
                :error="form.errors?.current_password"
            />
        </div>

        <div class="form-group mb-3">
            <label for="password" class="form-label fw-semibold">Nueva Contraseña <span class="text-danger">*</span></label>
            <MaterialInputPassword
                v-model="form.password"
                type="password"
                id="password"
                name="password"
                :isRequired="true"
                :error="form.errors?.password"
            />
        </div>

        <div class="form-group mb-4">
            <label for="password_confirmation" class="form-label fw-semibold">Confirmar Contraseña <span class="text-danger">*</span></label>
            <MaterialInputPassword
                v-model="form.password_confirmation"
                type="password"
                id="password_confirmation"
                name="password_confirmation"
                :isRequired="true"
                :error="form.errors?.password_confirmation"
            />
        </div>

        <div class="d-flex justify-content-center">
            <button
                type="submit"
                class="btn btn-sm btn-gradient-unique"
                :class="{ 'opacity-50': form.processing }"
                :disabled="form.processing"
            >
            RESTABLECER CONTRASEÑA
            </button>
        </div>
        </form>
    </BootstrapModal>
</template>

<script>
import Swal from 'sweetalert2';
import axios from 'axios';
import MaterialInputPassword from '@/Components/MaterialInputPassword.vue';
import BootstrapModal from '@/Components/BootstrapModal.vue';

export default {
    components: {
        MaterialInputPassword,
        BootstrapModal,
    },
    props: ['user'],
    data() {
        return {
        form: {
            current_password: "",
            password: "",
            password_confirmation: "",
            processing: false,
            errors: {},
        },
        };
    },
    methods: {
        submitpassword() {
        this.form.processing = true;

        axios.put('/password', this.form)
            .then(() => {
            Swal.fire({
                icon: 'success',
                title: 'Contraseña actualizada',
                text: 'Contraseña actualizada correctamente.',
            }).then(() => {
                location.reload();
            });
            })
            .catch((error) => {
            let message = "Error al actualizar la contraseña.";
            if (error.response?.data?.message) {
                message = error.response.data.message;
            }
            this.form.errors = error.response?.data?.errors || {};
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: message,
            });
            })
            .finally(() => {
            this.form.processing = false;
            });
        },
    },
};
</script>

<style scoped>
.reset-form {
    max-width: 500px;
    margin: 0 auto;
    padding: 1rem;
}

.form-label {
    font-size: 0.875rem;
    font-weight: 600;
    margin-bottom: 0.25rem;
    color: #4a4a4a;
}
</style>
