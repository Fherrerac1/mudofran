<script>
import BootstrapModal from './BootstrapModal.vue';
import MaterialButton from './MaterialButton.vue';
import MaterialImageSelector from './MaterialImageSelector.vue';
import MaterialInput from './MaterialInput.vue';
import MaterialTextarea from './MaterialTextarea.vue';

export default {
    props: {
        user: {
            type: Object,
            required: true
        },
    },
    components: {
        BootstrapModal,
        MaterialInput,
        MaterialTextarea,
        MaterialButton,
        MaterialImageSelector
    },
    data() {
        return {
            formularioReclamacion: {
                title: '',
                messages: [
                    {
                        user_id: this.user.id,
                        message: '',
                        user_foto: this.user.foto
                    }
                ],
                archivo: null
            },
            alertMessage: null,
            isSubmitting: false
        };
    },
    mounted() {
    },
    methods: {
        submitCreate() {
            if (this.isSubmitting) {
                return;
            }
            this.isSubmitting = true;

            axios.post("/reclamaciones/save", this.formularioReclamacion, {
                headers: {
                    "Content-Type": "multipart/form-data",
                },
            })
                .then((response) => {
                    location.reload();
                })
                .catch((error) => {
                    if (error.response && error.response.data && error.response.data.message) {
                        const message = error.response.data.message;
                        this.alertMessage = {
                            type: 'danger',
                            text: message,
                        };
                    } else {
                        // Handle other errors if needed
                        this.alertMessage = {
                            type: 'danger',
                            text: 'An error occurred while processing your request.',
                        };
                    }
                    this.isSubmitting = false;
                });
        },
    },

}
</script>

<template>
    <!--add reclamacion modal-->
    <BootstrapModal -modal-id="nuevo_reclamacion" -modal-size="modal-lg" title="RECLAMACION">
        <form class="px-3 py-1 row g-4" id="form_documento" enctype="multipart/form-data"
            @submit.prevent="submitCreate">
            <div class="col-12">
                <MaterialInput label="Título" type="text" v-model="formularioReclamacion.title" id="title" name="title"
                    :is-required="true" />
            </div>
            <div v-for="(message, index) in formularioReclamacion.messages" :key="index" class="col-12">
                <label for="description" class="form-label">Descripción:</label>
                <MaterialTextarea v-model="message.message" id="description" name="description" :rows="3"
                    :is-required="true" />
            </div>
            <div class="col-12">
                <label for="archivo" class="form-label fw-bold">Subir Imagen:</label>
                <MaterialImageSelector name="archivo" v-model:model-value="formularioReclamacion.archivo" id="archivo"
                    accept="image/*" max="6144000" />
            </div>

            <div class="col-12">
                <MaterialButton type="submit" :disabled="isSubmitting">
                    <span v-if="isSubmitting" class="spinner-border spinner-border-sm" role="status"
                        aria-hidden="true"></span>
                    <span v-if="!isSubmitting">Guardar</span>
                    <span v-else>Guardando...</span>
                </MaterialButton>
            </div>
        </form>
    </BootstrapModal>

</template>