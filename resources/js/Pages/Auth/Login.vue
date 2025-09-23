<template>
  <div class="page-header align-items-start min-vh-100 position-relative overflow-hidden"
    :style="!videoAvailable ? fallbackStyle : null">
    <!-- 🔒 LOGIN CONTENT -->

    <Head title="Inicia sesión" />
    <span class="mask bg-gradient-dark opacity-6"></span>
    <div class="container my-auto">
      <div class="row">
        <div class="col-lg-4 col-md-8 col-12 mx-auto">
          <div class="card z-index-0 fadeIn3 fadeInBottom">
            <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
              <div class="style_color shadow-success border-radius-lg py-3 pe-1">
                <div class="row mt-3">
                  <div class="d-flex justify-content-center align-items-center text-decoration-none p-0">
                    <img class="w-100" src="/images/logo_white.png" alt="Logo" />
                  </div>
                </div>
              </div>
            </div>
            <div class="card-body">
              <form @submit.prevent="submit" role="form" class="text-start mt-3">
                <div class="mb-3">
                  <material-input v-model="form.email" id="email" type="email" label="Email" name="email"
                    :isRequired="true" :error="form.errors.email" variant="static" />
                </div>
                <div class="mb-3">
                  <material-input v-model="form.password" id="password" type="password" label="Clave" name="password"
                    :isRequired="true" :error="form.errors.password" variant="static" />
                </div>

                <div class="text-center">
                  <material-button :type="'submit'" class="my-4 mb-2" variant="gradient" color="success" fullWidth>
                    <span v-if="!isSubmitting">Entrar</span>
                    <span v-else><i class="fas fa-spinner fa-spin"></i></span>
                  </material-button>
                </div>
                <p class="text-sm text-center text-gradient-unique">
                  <a href="/forgot-password" class="recover-pass font-weight-bold">
                    Recordar contraseña
                  </a>
                </p>
              </form>

              <BlackCatLogoWhite />
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import MaterialInput from '@/Components/MaterialInput.vue';
import MaterialButton from '@/Components/MaterialButton.vue';
import BlackCatLogoWhite from '@/Components/BlackCatLogoWhite.vue';
import { useForm, Head } from '@inertiajs/vue3';
import Swal from 'sweetalert2';

export default {
  components: {
    MaterialInput,
    MaterialButton,
    BlackCatLogoWhite,
    Head,
  },
  data() {
    return {
      form: useForm({
        email: '',
        password: '',
        remember: false,
      }),
      isSubmitting: false,
      videoAvailable: true,
      fallbackStyle: {
        backgroundImage:
          "url('https://images.unsplash.com/photo-1497294815431-9365093b7331?ixlib=rb-1.2.1&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1950&q=80')",
        backgroundSize: 'cover',
        backgroundPosition: 'center',
      },
    };
  },
  methods: {
    submit() {
      this.isSubmitting = true;
      this.form.post(this.route('signIn'), {
        onSuccess: () => {
          this.isSubmitting = false;
          this.form.reset('password');
          Swal.fire({
            icon: 'success',
            title: 'Éxito',
            text: '¡Inicio de sesión exitoso!',
            timer: 2000,
            showConfirmButton: false,
          });
        },
        onError: () => {
          this.isSubmitting = false;
          let message =
            'Hubo un error en tu envío. Por favor revisa tus datos e inténtalo nuevamente.';
          if (this.form.errors && Object.keys(this.form.errors).length > 0) {
            message = Object.values(this.form.errors)[0];
          }
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: message,
          });
        },
        onFinish: () => {
          this.isSubmitting = false;
        },
      });
    },
    route(name) {
      // Replace with your route helper if needed
      return window.route ? window.route(name) : `/${name}`;
    },
  },
  mounted() {
  },
};
</script>
