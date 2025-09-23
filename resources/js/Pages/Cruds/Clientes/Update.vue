<script>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import MaterialInput from "@/Components/MaterialInput.vue";
import MaterialButton from "@/Components/MaterialButton.vue";
import MaterialSwitch from "@/Components/MaterialSwitch.vue";
import MaterialSelect from "@/Components/MaterialSelect.vue";
import Swal from 'sweetalert2'; // Import SweetAlert2
import { useForm } from "@inertiajs/vue3";
import provincias from "@/assets/json/provincias.json";
import municipios from "@/assets/json/municipios.json";

export default {
    components: {
        AuthenticatedLayout,
        MaterialInput,
        MaterialButton,
        MaterialSwitch,
        MaterialSelect,
    },
    computed: {
        filteredMunicipios() {
            const selectedProvinciaPrefix = this.form.provincia;
            return this.municipios.filter(municipio => municipio.id.substring(0, 2) === selectedProvinciaPrefix);
        }
    },
    watch: {
        'form.share_data'(newVal) {
            if (newVal && !this.form.api_key) {
                this.form.api_key = this.generateApiKey();
            }
        }
    },
    data() {
        return {
            provincias: provincias,
            municipios: municipios,
            form: useForm(this.cliente),
        };
    },
    props: {
        user: {
            type: Object,
            required: true
        },
        cliente: {
            type: Object,
            required: true
        },
    },
    mounted() {
    },
    methods: {
        generateApiKey() {
            return Math.random().toString(36).substring(2, 15) +
                Math.random().toString(36).substring(2, 15);
        },
        submit() {
            this.form.post(route('clientes.update', this.cliente.id), {
                onSuccess: (response) => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Éxito',
                        text: 'Cliente se ha creado correctamente.',
                    });
                },
                onError: () => {
                    let message = 'Ocurrió un error al guardar el Cliente.';

                    if (this.form.errors && Object.keys(this.form.errors).length > 0) {
                        // Mostrar el primer error de validación (por simplicidad, solo mostramos el primero)
                        message = Object.values(this.form.errors)[0];
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: message || 'Ocurrió un error al guardar el Cliente.',
                    });
                },
            })
        },
    }
}
</script>


<template>
    <AuthenticatedLayout :user="user" :title="'Crear Cliente'">

        <div class="card mx-3 px-3" style="overflow-x: auto;">
            <!-- Card header -->
            <div class="card-header border-bottom">
                <div class="user d-flex align-items-center">
                    <div class="col-lg-6">
                        <a class="d-flex text-decoration-none fs-5 p-2 agregar" href="javascript:history.go(-1);"
                            role="button"><i class="material-icons pe-2 pt-1"><span
                                    class="material-symbols-outlined">arrow_back</span></i>Volver</a>
                    </div>

                </div>
            </div>

            <form class="px-3 py-1 row g-4" id="myForm" enctype="multipart/form-data" @submit.prevent="submit">

                <!-- Category (Personal/Empresa) -->
                <div class="form-group col-12">
                    <label class="form-label fw-bold mb-2">Tipo de Cliente</label>
                    <div class="d-flex">
                        <div class="form-check me-3">
                            <input class="form-check-input" type="radio" id="personal" name="category" value="Personal"
                                v-model="form.category" />
                            <label class="form-check-label" for="personal">
                                Residencial
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" id="empresa" name="category" value="Empresa"
                                v-model="form.category" />
                            <label class="form-check-label" for="empresa">
                                Empresa
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Nombre -->
                <div class="col-md-6-12 col-lg-4 form-group">
                    <MaterialInput variant="static" type="text" name="nombre" id="nombre" label="Nombre"
                        v-model="form.nombre" :is-required="true" />
                </div>

                <!-- Apellido 1 -->
                <div v-if="form.category === 'Personal'" class="col-md-6-12 col-lg-4 form-group">
                    <MaterialInput variant="static" type="text" name="apellido_1" id="apellido_1"
                        label="Primer Apellido" v-model="form.apellido_1" />
                </div>

                <!-- Apellido 2 -->
                <div v-if="form.category === 'Personal'" class="col-md-6-12 col-lg-4 form-group">
                    <MaterialInput variant="static" type="text" name="apellido_2" id="apellido_2"
                        label="Segundo Apellido" v-model="form.apellido_2" />
                </div>

                <!-- Email -->
                <div class="form-group col-md-6-12 col-lg-4">
                    <MaterialInput variant="static" type="email" name="email" id="email" label="Email"
                        v-model="form.email" :is-required="true"/>
                </div>

                <!-- Teléfono Móvil -->
                <div class="form-group col-md-6-12 col-lg-4">
                    <MaterialInput variant="static" type="text" name="telefono_mobile" id="telefono_mobile"
                        label="Teléfono Móvil" v-model="form.telefono_mobile" pattern="[0-9]{9}"
                        title="Please enter a 9-digit phone number" maxlength="9" minlength="9" placeholder="123456789"
                        :is-required="true" />
                </div>

                <!-- Teléfono Fijo -->
                <div class="form-group col-md-6-12 col-lg-4">
                    <MaterialInput variant="static" type="text" name="telefono_fijo" id="telefono_fijo"
                        label="Teléfono Fijo" v-model="form.telefono_fijo" pattern="[0-9]{9}"
                        title="Please enter a 9-digit phone number" maxlength="9" minlength="9"
                        placeholder="123456789" />
                </div>

                <!-- DNI -->
                <div class="form-group col-md-6-12 col-lg-4">
                    <MaterialInput variant="static" type="text" name="dni" id="dni" label="DNI/CIF" v-model="form.dni"
                        :is-required="true" />
                </div>

                <!-- Num Cuenta -->
                <div class="form-group col-md-6-12 col-lg-4">
                    <MaterialInput variant="static" type="text" name="num_cuenta" id="num_cuenta"
                        label="Cuenta Bancaria" v-model="form.num_cuenta" />
                </div>
                <!-- Descripcion -->
                <div class="form-group col-md-6-12 col-lg-4">
                    <MaterialInput variant="static" type="text" name="descripcion" id="descripcion" label="Descripcion"
                        v-model="form.descripcion" />
                </div>

                <!-- Dirección -->
                <h5 class="pt-3 ps-0 ms-3 m-0">Dirección de contacto</h5>

                <div class="row col-12 mt-3">
                    <!-- Provincia Select -->
                    <div class="form-group col-lg-6">
                        <label for="provincia" class="form-label fw-bold">Provincia</label>
                        <MaterialSelect name="provincia" id="provincia" v-model="form.provincia"
                            :options="provincias.map(provincia => ({ value: provincia.id, text: provincia.nm }))"
                            :is-required="true" placeholder="Selecciona una provincia" />
                    </div>

                    <!-- Localidad Select -->
                    <div class="form-group col-lg-6">
                        <label for="localidad" class="form-label fw-bold">Municipio</label>
                        <MaterialSelect name="localidad" id="localidad" v-model="form.localidad"
                            :options="filteredMunicipios.map(municipio => ({ value: municipio.nm, text: municipio.nm }))"
                            :is-required="true" placeholder="Selecciona un Municipio" />
                    </div>

                </div>

                <div class="form-group col-md-8">
                    <MaterialInput variant="static" type="text" name="direccion" id="direccion" label="Dirección"
                        v-model="form.direccion" :is-required="true" />
                </div>

                <!-- Código Postal -->
                <div class="form-group col-md-4">
                    <MaterialInput variant="static" type="number" name="cp" id="cp" label="Código Postal"
                        v-model="form.cp" maxlength="5" minlength="5" placeholder="12345" />
                </div>

                <div>
                    <!-- Submit Button -->
                    <MaterialButton type="submit" id="submitButton" class="mt-3" :is-disabled="form.processing">
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
