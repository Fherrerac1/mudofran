<template>
    <BootstrapModal :id="'createDocumento'" :-modal-size="'modal-lg'" :title="'Crear Documento'">
        <form @submit.prevent="submit">
            <div class="row">
                <MaterialSelect class="mt-3" v-model="form.user_id" :label="'Usuario'" id="userSelect"
                    :options="users.map(user => ({ value: user.id, text: user.name }))" :error="form.errors.user_id"
                    placeholder="Seleccione un usuario" :is-required="true" />

                <MaterialInput class="mt-3" v-model="form.titulo" :label="'Título'" :error="form.errors.titulo"
                    id="titulo" placeholder="Ingrese el título del documento" :is-required="true" />

                <MaterialFileInput class="mt-3" v-model="form.archivo" :label="'Archivo'" :error="form.errors.archivo"
                    id="fileInput" accept=".pdf,.doc,.docx,.png,.jpg,.jpeg" :is-required="true" />
            </div>

            <div class="mt-3">
                <MaterialButton type="submit" color="primary" :disabled="form.processing">
                    Guardar
                </MaterialButton>
            </div>
        </form>
    </BootstrapModal>
</template>

<script>
import Swal from 'sweetalert2'
import MaterialInput from '@/Components/MaterialInput.vue'
import BootstrapModal from '@/Components/BootstrapModal.vue'
import MaterialButton from '@/Components/MaterialButton.vue'
import { useForm } from '@inertiajs/vue3'
import MaterialFileInput from '@/Components/MaterialFileInput.vue'
import MaterialSelect from '@/Components/MaterialSelect.vue';

export default {
    components: {
        MaterialInput,
        BootstrapModal,
        MaterialButton,
        MaterialFileInput,
        MaterialSelect
    },
    props: {
        users: {
            type: Array,
            required: true,
            default: () => []
        },
    },
    data() {
        return {
            form: useForm({
                user_id: '',
                titulo: '',
                archivo: null
            })
        }
    },
    methods: {
        submit() {
            this.form.post(route('documentos.store'), {
                preserveScroll: true,
                onSuccess: () => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Documento creado',
                        text: 'El documento se ha creado correctamente.',
                    }).then(() => window.location.reload())
                },
                onError: (error) => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: this.form.errors.archivo || this.form.errors.titulo || this.form.errors.user_id || 'No se pudo guardar el documento.',
                    });
                }
            });
        },
    }
}
</script>
