<script>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Breadcrumb from '@/Components/Breadcrumb.vue';
import MaterialInput from "@/Components/MaterialInput.vue";
import MaterialButton from "@/Components/MaterialButton.vue";
import MaterialSwitch from "@/Components/MaterialSwitch.vue";
import MaterialSelect from "@/Components/MaterialSelect.vue";
import Swal from 'sweetalert2'; // Import SweetAlert2
import placeholder from '@/assets/img/placeholder.jpg'; // Import the placeholder image
import MaterialImageSelector from "@/Components/MaterialImageSelector.vue";
import { useForm } from "@inertiajs/vue3";
import provincias from "@/assets/json/provincias.json";
import municipios from "@/assets/json/municipios.json";

export default {
    components: {
        AuthenticatedLayout,
        Breadcrumb,
        MaterialInput,
        MaterialButton,
        MaterialSwitch,
        MaterialSelect,
        MaterialImageSelector,
        MaterialSwitch
    },
    computed: {
        filteredMunicipios() {
            const selectedProvinciaPrefix = this.form.provincia;
            return this.municipios.filter(municipio => municipio.id.substring(0, 2) === selectedProvinciaPrefix);
        }
    },
    data() {
        return {
            breadcrumbItems: [
                { label: 'Dashboard', url: this.route('dashboard') },
                { label: 'Usuarios', url: this.route('user.index') },
                { label: `${this.usuario.name}` },
                { label: 'Editar' },
            ],
            provincias: provincias,
            municipios: municipios,
            form: useForm(this.usuario),
            roles: [
                { text: "Gestor", value: "gestor" },
                { text: "Tecnico", value: "tecnico" },
            ],
            positions: [
                { text: "Adminstrativo", value: "Adminstrativo" },
                { text: "Trabajador", value: "Trabajador" },
            ],
        };
    },
    props: {
        usuario: {
            type: Object,
            required: true
        },
        user: {
            type: Object,
            required: true
        },
    },
    mounted() {
    },
    methods: {
        submit() {
            this.form.post(route('user.update', this.usuario.id), {
                onSuccess: (response) => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Éxito',
                        text: 'Usuario se ha actualizado correctamente.',
                    });
                    // Optionally clear errors on success
                    this.form.clearErrors();
                },
                onError: (errors) => {
                    if (errors.response?.data?.errors) {
                        this.form.errors = errors.response.data.errors;
                    }

                    // Generate a list of validation errors
                    let errorMessages = 'Ocurrió un error inesperado. Por favor, inténtalo nuevamente.'; // Use let instead of const

                    // Verificar si hay errores de validación en form.errors
                    if (this.form.errors && Object.keys(this.form.errors).length > 0) {
                        // Mostrar el primer error de validación (por simplicidad, solo mostramos el primero)
                        errorMessages = Object.values(this.form.errors)[0];
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Error al guardar el Usuario',
                        html: errorMessages,
                    });
                },
            });
        },
        getArchivoUrl(archivo) {
            if (archivo != null && archivo != '') {
                if (typeof archivo === 'string') {
                    const modifiedArchivo = archivo.replace("public/", ""); // Remove 'public/' segment
                    return "/storage/" + modifiedArchivo;
                } else if (archivo instanceof File) {
                    return URL.createObjectURL(archivo);
                } else {
                    console.error("Invalid file type");
                    return placeholder; // Return placeholder in case of an invalid file type
                }
            } else {
                return placeholder; // Return placeholder when archivo is null
            }
        },
    }
}
</script>


<template>
    <AuthenticatedLayout :user="user" :title="'Editar Usuarios'">
        <div class="row mb-3">
            <Breadcrumb :items="breadcrumbItems" />
        </div>

        <div class="card mx-3 px-3" style="overflow-x: auto;">
            <form class="px-3 py-1 row g-4" id="myForm" enctype="multipart/form-data" @submit.prevent="submit">
                <div class="form-group col-lg-4 col-md-6">
                    <MaterialInput variant="static" :id="'name'" :label="'Nombre'" :type="'text'" v-model="form.name"
                        :is-required="true" />
                </div>

                <div class="form-group col-lg-4 col-md-6">
                    <MaterialInput variant="static" :id="'email'" :label="'Email'" :type="'email'" v-model="form.email"
                        :is-required="true" />
                </div>

                <div class="form-group col-lg-4 col-md-6">
                    <MaterialInput variant="static" :id="'phone'" :label="'Teléfono'" :type="'text'"
                        v-model="form.telefono" :pattern="'[0-9]{9}'" :title="'Please enter a 9-digit phone number'"
                        :maxlength="9" :minlength="9" />
                </div>

                <div class="form-group col-lg-4 col-md-6">
                    <MaterialInput variant="static" :id="'dni'" :label="'DNI'" :type="'text'" v-model="form.dni"
                        :pattern="'([a-z]|[A-Z]|[0-9])[0-9]{7}([a-z]|[A-Z]|[0-9])'" :maxlength="9" />
                </div>

                <div class="form-group col-lg-4 col-md-6">
                    <MaterialInput variant="static" :id="'num_seguridad'" :label="'Número de la Seguridad Social'"
                        type="text" v-model="form.num_seguridad" :is-required="form.rol !== 'admin'" />
                </div>

                <div class="form-group col-lg-4 col-md-6">
                    <MaterialInput variant="static" :id="'fecha_alta'" :label="'Fecha de Alta'" type="date"
                        v-model="form.fecha_alta" :is-required="form.rol !== 'admin'" />
                </div>

                <div class="form-group col-lg-4 col-md-6">
                    <MaterialInput variant="static" :id="'fecha_nacimiento'" :label="'Fecha de Nacimiento'" type="date"
                        v-model="form.fecha_nacimiento" :is-required="form.rol !== 'admin'" />
                </div>

                <!-- Horas semanales -->
                <div class="form-group col-lg-4 col-md-6">
                    <MaterialInput variant="static" :id="'horario_semanal'" :label="'Horas semanales'" type="number"
                        v-model="form.horario_semanal" :is-required="true" />
                </div>

                <!-- Días laborables -->
                <div class="form-group col-lg-4 col-md-6">
                    <MaterialInput variant="static" :id="'dias_laborables'" :label="'Días laborables'" type="number"
                        v-model="form.dias_laborables" :is-required="true" />
                </div>

                <div class="row col-12 mt-3">
                    <!-- Provincia Select -->
                    <div class="form-group col-lg-4 col-md-6">
                        <label for="provincia" class="form-label fw-bold">Provincia</label>
                        <MaterialSelect name="provincia" id="provincia" v-model="form.provincia"
                            :options="provincias.map(provincia => ({ value: provincia.id, text: provincia.nm }))"
                            :is-required="form.rol !== 'admin'" placeholder="Selecciona una provincia" />
                    </div>

                    <!-- Localidad Select -->
                    <div class="form-group col-lg-4 col-md-6">
                        <label for="localidad" class="form-label fw-bold">Municipio</label>
                        <MaterialSelect name="localidad" id="localidad" v-model="form.localidad"
                            :options="filteredMunicipios.map(municipio => ({ value: municipio.nm, text: municipio.nm }))"
                            :is-required="form.rol !== 'admin'" placeholder="Selecciona un Municipio" />
                    </div>

                </div>

                <!-- Direccion -->
                <div class="col-lg-6 form-group">
                    <MaterialInput variant="static" type="text" name="direccion" id="direccion" label="Dirección"
                        v-model="form.direccion" :is-required="form.rol !== 'admin'" />
                </div>

                <!-- postal code -->
                <div class="col-lg-6 form-group">
                    <MaterialInput variant="static" type="number" name="cp" id="cp" label="Codigo Postal"
                        v-model="form.cp" :is-required="form.rol !== 'admin'" />
                </div>
                <hr>

                <div class="form-group col-lg-4 col-md-6">
                    <label for="position" class="fw-bold">Puesto:</label>
                    <MaterialSelect id="position" :options="positions" v-model="form.position"
                        placeholder="Seleccione un puesto" :is-required="true" />
                </div>

                <div v-if="form.rol !== 'admin'" class="form-group col-lg-4 col-md-6">
                    <label for="puesto1" class="fw-bold">Rol:</label>
                    <MaterialSelect :searchable="false" name="rol" id="rol" v-model="form.rol" :options="roles"
                        placeholder="Selecciona rol del usuario" :is-required="true" />
                </div>

                <div class="col-12 form-group">
                    <MaterialImageSelector id="fileInput" label="Foto" v-model="form.img" />
                </div>

                <div v-if="form.rol === 'tecnico'" class="form-group col-lg-4 col-md-6">
                    <MaterialInput variant="static" :id="'coste_hora'" label="Coste por Hora" type="number"
                        v-model="form.coste_hora" :is-required="true" />
                </div>

                <div v-if="user.rol === 'admin'" class="form-group col-lg-6 d-flex align-items-center gap-3">
                    <label class="form-label" for="block">Bloqueado</label>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="block" name="blocked" v-model="form.blocked"
                            :true-value="1" :false-value="0" />
                    </div>
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
