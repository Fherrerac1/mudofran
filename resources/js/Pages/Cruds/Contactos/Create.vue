<script>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import MaterialInput from "@/Components/MaterialInput.vue";
import MaterialButton from "@/Components/MaterialButton.vue";
import Swal from 'sweetalert2';
import { useForm } from "@inertiajs/vue3";

export default {
    components: {
        AuthenticatedLayout,
        MaterialInput,
        MaterialButton,
    },
    props: {
        user: {
            type: Object,
            required: true,
        },
    },
    data() {
        return {
            form: useForm({
                nombre: '',
                email: '',
                trabajador_id: '',
                telefono_mobile: '',
                dni: '',
            }),
        };
    },
    methods: {
        submit() {
            // Check that at least email or phone is provided
            if (!this.form.email && !this.form.telefono_mobile) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Campo requerido',
                    text: 'Debe ingresar al menos un correo electrónico o un número de teléfono.',
                });
                return;
            }

            this.form.post(route('contactos.store'), {
                onSuccess: () => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Éxito',
                        text: 'El contacto se ha creado correctamente.',
                    });
                },
                onError: () => {
                    const message = this.form.errors && Object.keys(this.form.errors).length > 0
                        ? Object.values(this.form.errors)[0]
                        : 'Ocurrió un error al guardar el contacto.';
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: message,
                    });
                },
            });
        },
    },
};
</script>

<template>
    <AuthenticatedLayout :user="user" title="Crear contacto">
        <div class="card mx-3 px-3" style="overflow-x: auto;">
            <!-- Header -->
            <div class="card-header border-bottom">
                <div class="user d-flex align-items-center">
                    <div class="col-lg-6">
                        <a class="d-flex text-decoration-none fs-5 p-2" href="javascript:history.go(-1);" role="button">
                            <i class="material-icons pe-2 pt-1">
                                <span class="material-symbols-outlined">arrow_back</span>
                            </i>
                            Volver
                        </a>
                    </div>
                </div>
            </div>

            <!-- Form -->
            <form class="px-3 py-1 row g-4" @submit.prevent="submit" enctype="multipart/form-data">
                <h2 class="style_color text-center rounded">Crear Contacto</h2>
                <!-- Nombre -->
                <div class="col-md-6 form-group">
                    <MaterialInput variant="static" type="text" name="nombre" id="nombre" label="Nombre"
                        v-model="form.nombre" :is-required="true" />
                </div>

                <!-- Email -->
                <div class="col-md-6 form-group">
                    <MaterialInput variant="static" type="email" name="email" id="email" label="Email"
                        v-model="form.email" />
                </div>

                <!-- Teléfono -->
                <div class="col-md-6 form-group">
                    <MaterialInput variant="static" type="text" name="telefono_mobile" id="telefono_mobile" label="Teléfono Móvil"
                        v-model="form.telefono_mobile" pattern="[0-9]{9}" title="Introduce un número de 9 dígitos"
                        maxlength="9" minlength="9" placeholder="123456789" />
                </div>

                <!-- DNI -->
                <div class="col-md-6 form-group">
                    <MaterialInput variant="static" type="text" name="dni" id="dni" label="DNI/CIF"
                        v-model="form.dni" />
                </div>

                <!-- Botón Guardar -->
                <div class="col-12">
                    <MaterialButton type="submit" class="mt-3" :is-disabled="form.processing">
                        <span v-if="form.processing">
                            <i class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></i>
                        </span>
                        Guardar
                    </MaterialButton>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
