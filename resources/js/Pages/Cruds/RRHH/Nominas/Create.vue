<template>
    <BootstrapModal :id="'createnomina'" :-modal-size="'modal-lg'" :title="'Crear nómina'">
        <form @submit.prevent="submit">
            <div class="row">
                <MaterialSelect class="mt-3" v-model="form.user_id" :label="'Usuario'" id="userSelect"
                    :options="users.map(user => ({ value: user.id, text: user.name }))" :error="form.errors.user_id"
                    placeholder="Seleccione un usuario" :is-required="true" />

                <MaterialSelect class="mt-3" v-model="form.mes" :label="'Mes'" id="mesSelect" :options="meses"
                    :error="form.errors.mes" placeholder="Seleccione el mes" :is-required="true" />

                <MaterialInput class="mt-3" v-model="form.anio" :label="'Año'" id="anioInput" type="number" min="2000"
                    :error="form.errors.anio" placeholder="Ingrese el año" :is-required="true" />

                <MaterialFileInput class="mt-3" v-model="form.archivo" :label="'Archivo'" :error="form.errors.archivo"
                    id="fileInput" accepted-formats=".pdf" :is-required="true" />
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
            meses: [
                { value: '01', text: 'Enero' },
                { value: '02', text: 'Febrero' },
                { value: '03', text: 'Marzo' },
                { value: '04', text: 'Abril' },
                { value: '05', text: 'Mayo' },
                { value: '06', text: 'Junio' },
                { value: '07', text: 'Julio' },
                { value: '08', text: 'Agosto' },
                { value: '09', text: 'Septiembre' },
                { value: '10', text: 'Octubre' },
                { value: '11', text: 'Noviembre' },
                { value: '12', text: 'Diciembre' },
            ],
            form: useForm({
                user_id: '',
                mes: '',
                anio: new Date().getFullYear(),
                archivo: null
            })
        }
    },
    methods: {
        submit() {
            this.form.post(route('nominas.store'), {
                preserveScroll: true,
                onSuccess: () => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Nómina creada',
                        text: 'La nómina se ha creado correctamente.',
                    }).then(() => window.location.reload())
                },
                onError: () => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text:
                            this.form.errors.archivo ||
                            this.form.errors.mes ||
                            this.form.errors.anio ||
                            this.form.errors.user_id ||
                            'No se pudo guardar la nómina.',
                    });
                }
            });
        },
    }
}
</script>
