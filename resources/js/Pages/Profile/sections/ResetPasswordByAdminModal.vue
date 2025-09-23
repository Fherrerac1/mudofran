<template>
    <BootstrapModal :id="'resetPasswordAdminModal'" :ModalSize="'modal-lg modal-dialog-centered'" :title="'RESTABLECER CONTRASEÑA'">
        <!-- Mensaje de advertencia -->
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
                Esta acción <strong>no es reversible</strong>. Verifica la contraseña antes de continuar.
            </div>
        </div>

        <form @submit.prevent="submitPassword" class="reset-form container-sm px-2 py-3">
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
                ACTUALIZAR NUEVA CONTRASEÑA
            </button>
        </div>
        </form>
    </BootstrapModal>
</template>

<script>
import Swal from 'sweetalert2';
import axios from 'axios';
import MaterialInput from '@/Components/MaterialInput.vue';
import BootstrapModal from '@/Components/BootstrapModal.vue';
import MaterialInputPassword from '@/Components/MaterialInputPassword.vue';

export default {
  components: {
    MaterialInput,
    BootstrapModal,
    MaterialInputPassword
  },
  props: {
    usuarioId: { type: Number, required: true }
  },
  data() {
    return {
      form: {
        password: "",
        password_confirmation: "",
        processing: false,
        errors: {},
      }
    };
  },
  methods: {
        submitPassword() {
        this.form.processing = true;

        axios.post(`/users/${this.usuarioId}/update-password`, {
            password: this.form.password,
            password_confirmation: this.form.password_confirmation
        })
            .then(() => {
            Swal.fire({
                icon: 'success',
                title: 'Contraseña actualizada',
                text: 'La nueva contraseña se actualizo correctamente.',
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
        }
    }
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

.btn-primary {
  background-color: #007bff;
  border: none;
  color: white;
  border-radius: 8px;
  transition: all 0.3s ease;
}

.btn-primary:hover {
  background-color: #0056b3;
}
</style>
