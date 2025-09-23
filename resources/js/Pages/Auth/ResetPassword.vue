<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import MaterialInputField from '@/Components/MaterialInputField.vue';
import MaterialButton from '@/Components/MaterialButton.vue';

const props = defineProps({
    email: {
        type: String,
        required: true,
    },
    token: {
        type: String,
        required: true,
    },
});

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    // Basic validation
    if (!form.password || form.password.length < 8) {
        Swal.fire({
            icon: 'error',
            title: 'Contraseña inválida',
            text: 'La contraseña debe tener al menos 8 caracteres.',
            confirmButtonText: 'Aceptar',
        });
        return;
    }
    if (form.password !== form.password_confirmation) {
        Swal.fire({
            icon: 'error',
            title: 'Contraseñas no coinciden',
            text: 'La confirmación de la contraseña no coincide.',
            confirmButtonText: 'Aceptar',
        });
        return;
    }

    // Submit form if validation passes
    form.post(route('password.store'), {
        onSuccess: () => {
            Swal.fire({
                icon: 'success',
                title: 'Contraseña restablecida',
                text: 'Tu contraseña se ha restablecido correctamente.',
                confirmButtonText: 'Aceptar',
            });
        },
        onError: () => {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Hubo un problema restableciendo tu contraseña. Por favor, revisa la información proporcionada e inténtalo de nuevo.',
                confirmButtonText: 'Aceptar',
            });
        },
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>

    <Head title="Restablecer Contraseña" />

    <main class="mt-0 main-content">
        <div class="page-header align-items-start min-vh-50 m-3 border-radius-lg" style="
        background-image: url('https://images.unsplash.com/photo-1497996541515-6549b145cd90?ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&ixlib=rb-1.2.1&auto=format&fit=crop&w=1650&q=80');
      ">
            <span class="mask bg-gradient-dark opacity-6"></span>
        </div>
        <section>
            <div class="container mb-4 p-0">
                <div class="row mt-lg-n12 mt-md-n12 mt-n12 justify-content-center">
                    <div class="col-xl-4 col-lg-5 col-md-7 mx-auto">
                        <div class="card mt-8">
                            <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                                <div
                                    class="style_color forms shadow-warning border-radius-lg py-3 pe-1 text-center py-4">
                                    <h3 class="font-weight-bolder text-white">Restablecer Contraseña</h3>
                                </div>
                            </div>
                            <div class="py-4 card-body">
                                <form @submit.prevent="submit">
                                    <div class="mb-3">
                                        <material-input-field id="newPassword" v-model:value="form.password"
                                            type="password" label="Nueva Contraseña" name="newPassword"
                                            variant="static" />
                                    </div>
                                    <div class="mb-3">
                                        <material-input-field id="confirmPassword"
                                            v-model:value="form.password_confirmation" type="password"
                                            label="Confirmar Contraseña" name="confirmPassword" variant="static" />
                                    </div>
                                    <div class="text-center">
                                        <material-button type="submit" :class="{ 'opacity-25': form.processing }"
                                            :disabled="form.processing" class="mt-4" variant="gradient" color="warning"
                                            full-width>Restablecer</material-button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
</template>
