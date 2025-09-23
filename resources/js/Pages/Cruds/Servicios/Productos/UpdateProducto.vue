<template>
    <!-- update producto modal -->
    <BootstrapModal :id="'updateProductoModal'" :ModalSize="'modal-md'" :title="'Editar Producto'">
        <form @submit.prevent="submit">
            <div class="row">
                <div class="form-group col-md-6">
                    <MaterialInput v-model="form.nombre" type="text" label="Nombre del Producto" id="nombre"
                        name="nombre" :isRequired="true" :error="form.errors?.nombre" variant="static" />
                </div>

                <div class="form-group col-md-6">
                    <MaterialInput v-model="form.precio" type="text" label="Precio del Producto" id="number" step="any"
                        name="precio" :isRequired="true" :error="form.errors?.precio" variant="static" />
                </div>

                <!-- servicio -->
                <div class="form-group mb-3 col-md-12 col-12">
                    <label for="servicio_id" class="fw-bold">Servicio</label>
                    <MaterialSelect id="servicio_id" v-model="form.servicio_id" :is-required="true"
                        placeholder="Selecciona un Servicio" :options="servicios.map(servicio => ({
                            value: servicio.id,
                            text: servicio.nombre
                        }))" />
                </div>

                <div class="form-group col-12 mt-3">
                    <label for="observaciones" class="fw-bold">Observaciones</label>
                    <MaterialTextarea v-model="form.observaciones" id="observaciones" name="observaciones"
                        :isRequired="true" :error="form.errors?.observaciones"
                        :placeholder="'Añade observaciones del producto'" />
                </div>

                <div class="form-group mt-3">
                    <MultiFileInputs v-model:model-value="form.documents" id="documents" ref="documentInput" multiple
                        label="Adjunte cualquier documento relevante para su inscripción." />
                </div>

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
import MultiFileInputs from "@/Components/MultiFileInputs.vue";
import MaterialSelect from "@/Components/MaterialSelect.vue";

export default {
    components: {
        MaterialInput,
        BootstrapModal,
        MaterialButton,
        MaterialTextarea,
        MultiFileInputs,
        MaterialSelect
    },
    props: {
        servicios: {
            type: Array,
            required: true,
        },
        producto: {
            type: Object,
            required: true,
        },
    },
    data() {
        return {
            form: useForm({}),
        };
    },
    watch: {
        producto: {
            immediate: true, // Ensures the watcher runs immediately when the component is mounted
            handler(newProducto) {
                // Update the form data when the Producto prop changes
                this.form = useForm({ ...newProducto });
            },
        },
    },
    methods: {
        submit() {
            const documents = Array.from(this.$refs.documentInput.files);
            this.form.documents = documents;

            this.form.post(route("productos.update", this.producto.id), {
                onSuccess: () => {
                    Swal.fire({
                        icon: "success",
                        title: "Éxito",
                        text: "¡El producto se ha creado correctamente!",
                        timer: 2000,
                        showConfirmButton: true,
                        confirmButtonText: 'Aceptar',
                    }).then(() => {
                        // Reload the page after the user confirms
                        window.location.reload();
                    });
                },
                onError: () => {
                    // Default error message
                    let message = "Hubo un error en tu envío. Por favor revisa tus datos e inténtalo nuevamente.";

                    // Use the first validation error if available
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
