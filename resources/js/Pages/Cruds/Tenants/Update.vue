<script>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import MaterialInput from "@/Components/MaterialInput.vue";
import MaterialButton from "@/Components/MaterialButton.vue";
import Swal from "sweetalert2";
import { useForm, Head } from "@inertiajs/vue3";
import MaterialFileInput from "@/Components/MaterialFileInput.vue";

export default {
    components: {
        AuthenticatedLayout,
        MaterialInput,
        MaterialButton,
        MaterialFileInput,
        Head
    },
    props: {
        user: { type: Object, required: true },
        tenant: { type: Object, required: true },
        adminUser: { type: Object, required: true },
        configuration: { type: Object, required: true },
    },
    mounted() {
    },
    data() {
        return {
            tenant_user: {},
            form: useForm({
                slug: "",
                user: this.adminUser,
                configuration: this.configuration || {},
                main_logo: this.configuration?.main_logo
            }),
        };
    },
    methods: {
        submit() {
            this.form.post(route("tenants.update", this.tenant.id), {
                onSuccess: () => {
                    Swal.fire({
                        icon: "success",
                        title: "Éxito",
                        text: "Tenant se ha creado correctamente.",
                    });
                    this.form.reset();
                },
                onError: () => {
                    let message = "Ocurrió un error al guardar el tenant.";
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

<template>

    <AuthenticatedLayout :user="user" :title="'Editar Tenant'">
        <div class="container bg-secondary">
            <div class="card mx-3 px-3" style="overflow-x: auto;">
                <!-- Card header -->
                <div class="card-header border-bottom">
                    <div class="user d-flex align-items-center">
                        <div class="col-lg-6">
                            <a class="d-flex text-decoration-none fs-5 p-2 agregar" href="javascript:history.go(-1);"
                                role="button">
                                <i class="material-icons pe-2 pt-1">
                                    <span class="material-symbols-outlined">arrow_back</span>
                                </i>
                                Volver
                            </a>
                        </div>
                    </div>
                </div>

                <form class="px-3 py-3 row g-4" @submit.prevent="submit">
                    <!-- Slug -->
                    <h5>tenant : {{ tenant.slug }}</h5>
                    <hr>
                    <!-- User fields -->
                    <div class="col-12 col-md-6">
                        <MaterialInput variant="outline" type="text" label="Nombre de usuario" id="name"
                            v-model="form.user.name" :error="form.errors['user.name']" :is-required="true" />
                    </div>
                    <div class="col-12 col-md-6">
                        <MaterialInput variant="outline" type="email" label="Email" id="email" v-model="form.user.email"
                            :error="form.errors['user.email']" :is-required="true" />
                    </div>
                    <div class="col-12 col-md-6">
                        <MaterialInput variant="outline" label="Contraseña" type="password" id="password"
                            v-model="form.user.password" :error="form.errors['user.password']" />
                    </div>
                    <div class="col-12 col-md-6">
                        <MaterialInput variant="outline" label="Confirmar Contraseña" type="password"
                            id="confirm_password" v-model="form.user.password_confirmation"
                            :error="form.errors['user.password_confirmation']" />
                    </div>

                    <hr>

                    <!-- Configuration fields -->
                    <div class="col-12 col-md-6 d-flex">
                        <label for="style_color">Color principal</label>
                        <input id="style_color" type="color" v-model="form.configuration.style_color"
                            :error="form.errors['configuration.style_color']" required />
                    </div>
                    <div class="col-12 col-md-6 d-flex">
                        <label for="text_color">Color del texto</label>
                        <input id="text_color" type="color" v-model="form.configuration.text_color"
                            :error="form.errors['configuration.text_color']" required />
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="unique_color">Color único</label>
                        <input id="unique_color" type="color" v-model="form.configuration.unique_color"
                            :error="form.errors['configuration.unique_color']" required />
                    </div>
                    <div class="col-12 col-md-6">
                        <MaterialInput variant="outline" label="Número de serie" id="serial_num"
                            v-model="form.configuration.serial_num" :error="form.errors['configuration.serial_num']" />
                    </div>
                    <div class="col-12 col-md-6">
                        <MaterialInput variant="outline" label="URL de la aplicación" id="url_app"
                            v-model="form.configuration.url_app" :error="form.errors['configuration.url_app']" />
                    </div>
                    <div class="col-12 col-md-6">
                        <MaterialInput variant="outline" label="Razón social" id="business_name"
                            v-model="form.configuration.business_name"
                            :error="form.errors['configuration.business_name']" />
                    </div>
                    <div class="col-12 col-md-6">
                        <MaterialInput variant="outline" label="Dirección" id="address"
                            v-model="form.configuration.address" :error="form.errors['configuration.address']" />
                    </div>
                    <div class="col-12 col-md-6">
                        <MaterialInput variant="outline" label="Código Postal" id="postal_code"
                            v-model="form.configuration.postal_code"
                            :error="form.errors['configuration.postal_code']" />
                    </div>
                    <div class="col-12 col-md-6">
                        <MaterialInput variant="outline" label="Teléfono" id="phone" v-model="form.configuration.phone"
                            :error="form.errors['configuration.phone']" />
                    </div>
                    <div class="col-12 col-md-6">
                        <MaterialInput variant="outline" label="Localidad" id="town" v-model="form.configuration.town"
                            :error="form.errors['configuration.town']" />
                    </div>
                    <div class="col-12 col-md-6">
                        <MaterialInput variant="outline" label="Provincia" id="province"
                            v-model="form.configuration.province" :error="form.errors['configuration.province']" />
                    </div>
                    <div class="col-12 col-md-6">
                        <MaterialInput variant="outline" label="Email de contacto" id="conf_email"
                            v-model="form.configuration.email" :error="form.errors['configuration.email']" />
                    </div>
                    <div class="col-12 col-md-6">
                        <MaterialInput variant="outline" label="CIF/NIF" id="tax_id" v-model="form.configuration.tax_id"
                            :error="form.errors['configuration.tax_id']" />
                    </div>
                    <div class="col-12 col-md-6">
                        <MaterialInput variant="outline" label="Tipo de negocio" id="business_type"
                            v-model="form.configuration.business_type"
                            :error="form.errors['configuration.business_type']" />
                    </div>

                    <hr>

                    <div class="col-lg-6">
                        <MaterialFileInput id="main_logo" accepted-formats=".png" v-model="form.main_logo"
                            label="Logo" />
                    </div>

                    <!-- Submit -->
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
        </div>
    </AuthenticatedLayout>
</template>
