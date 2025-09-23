<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import Swal from 'sweetalert2'; // Import SweetAlert
import MaterialButton from '@/Components/MaterialButton.vue';
import MaterialInputField from '@/Components/MaterialInputField.vue';

// Define the props
defineProps({
    status: {
        type: String,
    },
});

// Create form using Inertia's useForm
const form = useForm({
    email: '',
});

// Handle form submission
const submit = () => {
    form.post(route('password.email'), {
        onSuccess: () => {
            // Show a SweetAlert popup with Spanish text on successful submission
            Swal.fire({
                icon: 'success',
                title: 'Correo enviado',
                text: 'Te hemos enviado un enlace para restablecer tu contraseña.',
                confirmButtonText: 'Aceptar',
            }).then((result) => {
                if (result.isConfirmed) {
                    // Redirect after user clicks "Aceptar"
                    window.location.href = '/';
                }
            });
        },
        onError: () => {
            // Show a SweetAlert popup with Spanish text on error
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Hubo un problema enviando el correo. Por favor, inténtalo de nuevo.',
                confirmButtonText: 'Aceptar',
            });
        },
    });
};

</script>

<template>

    <Head title="Forgot Password" />
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
                                    <h3 class="font-weight-bolder text-white">Reset Password</h3>
                                    <p class="mb-0 text-sm text-white">
                                        ¿Olvidaste tu contraseña? No hay problema. Simplemente ingresa tu dirección de
                                        correo electrónico y te
                                        enviaremos un enlace para restablecer tu contraseña. </p>
                                </div>
                            </div>
                            <div class="py-4 card-body">
                                <form @submit.prevent="submit">
                                    <div class="mb-3">
                                        <material-input-field :is-required="true" id="email" v-model:value="form.email"
                                            type="email" label="Email" placeholder="john@email.com" name="email"
                                            variant="static" />
                                    </div>
                                    <div class="text-center">
                                        <material-button :disabled="form.processing" :type="'submit'" class="mt-4"
                                            variant="gradient" color="warning" full-width>Reset</material-button>
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
