<template>
    <BootstrapModal id="createTimeOff" -modal-size="modal-lg" title="Nueva Solicitud de Permiso">
        <form @submit.prevent="submit">
            <div class="row">
                <!-- Fecha Desde -->
                <MaterialInput
                    class="mt-3"
                    v-model="form.from"
                    :label="'Desde'"
                    id="fromDate"
                    type="date"
                    :min="today"
                    :error="form.errors.from"
                    placeholder="Fecha desde"
                    :is-required="true"
                />

                <!-- Fecha Hasta -->
                <MaterialInput
                    class="mt-3"
                    v-model="form.to"
                    :label="'Hasta'"
                    id="toDate"
                    type="date"
                    :min="form.from || today"
                    :error="form.errors.to"
                    placeholder="Fecha hasta"
                    :is-required="true"
                />
            </div>

            <!-- Vista previa del rango -->
            <div v-if="form.from && form.to" class="alert alert-info mt-3">
                <i class="material-icons align-middle me-1">event</i>
                Solicitas un permiso del <strong>{{ formatDate(form.from) }}</strong> al
                <strong>{{ formatDate(form.to) }}</strong>.
            </div>

            <div class="mt-3 text-end">
                <MaterialButton type="submit" color="primary" :disabled="form.processing">
                    <span class="material-icons align-middle me-1">save</span>
                    Guardar
                </MaterialButton>
            </div>
        </form>
    </BootstrapModal>
</template>

<script>
import Swal from 'sweetalert2';
import MaterialInput from '@/Components/MaterialInput.vue';
import BootstrapModal from '@/Components/BootstrapModal.vue';
import MaterialButton from '@/Components/MaterialButton.vue';
import { useForm } from '@inertiajs/vue3';

export default {
    components: {
        MaterialInput,
        BootstrapModal,
        MaterialButton,
    },
    props: {
        user: {
            type: Object,
            required: true
        },
    },
    data() {
        const today = new Date().toISOString().split('T')[0];
        return {
            today,
            form: useForm({
                from: '',
                to: '',
            })
        };
    },
    methods: {
        submit() {
            this.form.post(route('time_off.store'), {
                preserveScroll: true,
                onSuccess: () => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Solicitud creada',
                        text: 'La solicitud se ha creado correctamente.',
                    }).then(() => window.location.reload());
                },
                onError: () => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: this.form.errors.from || this.form.errors.to || 'No se pudo guardar la solicitud.',
                    });
                }
            });
        },
        formatDate(dateString) {
            const options = { year: 'numeric', month: 'long', day: 'numeric' };
            return new Date(dateString).toLocaleDateString('es-ES', options);
        },
    }
};
</script>
