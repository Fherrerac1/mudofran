<template>
    <!-- update servicio modal -->
    <BootstrapModal v-if="servicio" :id="'updateServicioModal'" :ModalSize="'modal-md'" :title="'Crear Servicio'">
        <form @submit.prevent="submit">
            <div class="form-group">
                <MaterialInput v-model="form.nombre" type="text" label="Nombre del servicio" id="nombre" name="nombre"
                    :isRequired="true" :error="form.errors?.nombre" />
            </div>

            <div class="form-group mt-3">
                <label for="observaciones" class="fw-bold">Observaciones</label>
                <MaterialTextarea v-model="form.observaciones" id="observaciones" name="observaciones"
                    :isRequired="true" :error="form.errors?.observaciones"
                    :placeholder="'Añade observaciones del servicio'" />
            </div>

            <div class="flex items-center justify-end mt-4">
                <MaterialButton type="submit" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    Guardar
                </MaterialButton>
            </div>
        </form>
    </BootstrapModal>
</template>

<script>
import Swal from "sweetalert2";
import MaterialInput from "@/Components/MaterialInput.vue";
import BootstrapModal from "@/Components/BootstrapModal.vue";
import MaterialButton from "@/Components/MaterialButton.vue";
import { useForm } from "@inertiajs/vue3";
import MaterialTextarea from "@/Components/MaterialTextarea.vue";

export default {
    components: {
        MaterialInput,
        BootstrapModal,
        MaterialButton,
        MaterialTextarea,
    },
    props: ['servicio'],
    data() {
        return {
            form: useForm({}),
        };
    },
    watch: {
        servicio: {
            immediate: true, // Ensures the watcher runs immediately when the component is mounted
            handler(newServicio) {
                // Update the form data when the servicio prop changes
                this.form = useForm({ ...newServicio });
            },
        },
    },
    methods: {
        submit() {
            this.form.post(route("servicios.update", this.servicio.id), {
                onSuccess: () => {
                    Swal.fire({
                        icon: "success",
                        title: "Éxito",
                        text: "¡El Servicio se ha actualizado correctamente!",
                        timer: 2000,
                        showConfirmButton: true,
                        confirmButtonText: 'Aceptar',
                    }).then(() => {
                        // Reload the page after the user confirms
                        window.location.reload();
                    });
                },
                onError: () => {
                    let message = "Hubo un error en tu envío. Por favor revisa tus datos e inténtalo nuevamente.";

                    if (this.form.errors && Object.keys(this.form.errors).length > 0) {
                        message = Object.values(this.form.errors)[0];
                    }

                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: message,
                    });
                },
            });
        },
    },
};
</script>
