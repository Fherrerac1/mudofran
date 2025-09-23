<script>
import MaterialInput from '@/Components/MaterialInput.vue';
import MaterialTextarea from '@/Components/MaterialTextarea.vue';
import { Head } from '@inertiajs/vue3';


export default {
    components: {
        MaterialInput,
        MaterialTextarea,
        Head,
    },
    data() {
        return {
            configuration: {
                footer_text: '',
                business_name: '',
                phone: '',
                address: '',
                postal_code: '',
                town: '',
                province: '',
                email: '',
                tax_id: '',
                business_type: '',
                text_color: '#212529',
                style_color: '#fff',
                unique_color: '#00DBDB',
                fecha_fin: '',
                logo_white: '',
                App_Logo: '',
                sidebar_image: '',
                video: '',
                favicon: '',
                serial_num: '',
                url_app: '',
                series_types: '',
                series: 0,
                tecnicos: 0,
                factura_mode: 1,
            },
            videoSource: null,
            isSubmitting: false,
            alertMessage: {
                type: '',
                text: '',
            }
        };
    },
    props: ['old_configuration'],
    mounted() {
        if (this.old_configuration.id != null) {
            this.configuration = this.old_configuration;
        }
    },
    methods: {
        submitCreate() {
            if (this.isSubmitting) {
                return;
            }
            this.isSubmitting = true;
            axios.post('/configuracion', this.configuration, {
                headers: {
                    "Content-Type": "multipart/form-data",
                },
            })
                .then(response => {
                    window.location = '/home';
                })
                .catch(error => {
                    if (error.response && error.response.data && error.response.data.message) {
                        const message = error.response.data.message;
                        this.alertMessage = {
                            type: 'danger',
                            text: message,
                        };
                    } else {
                        // Handle other errors if needed
                        this.alertMessage = {
                            type: 'danger',
                            text: 'An error occurred while processing your request.',
                        };
                    }
                    this.isSubmitting = false;
                });
        },
        previewImage(event, previewId) {
            const fileInput = event.target.files[0];
            const previewImage = document.getElementById(previewId);

            if (fileInput) {
                const reader = new FileReader();

                reader.onload = function (e) {
                    if (previewId === 'previewVideo') {
                        this.videoSource = e.target.result;
                        this.configuration.video = fileInput;
                    } else if (previewId === 'previewLogo_white') {
                        previewImage.src = e.target.result;
                        this.configuration.logo_white = fileInput;
                    } else if (previewId === 'previewFavicon') {
                        previewImage.src = e.target.result;
                        this.configuration.favicon = fileInput;
                    } else if (previewId === 'previewApp_Logo') {
                        previewImage.src = e.target.result;
                        this.configuration.App_Logo = fileInput;
                    } else if (previewId === 'previewSidebar_image') {
                        previewImage.src = e.target.result;
                        this.configuration.sidebar_image = fileInput;
                    }
                }.bind(this);

                reader.readAsDataURL(fileInput);
                console.log(this.configuration);
            }
        }, getArchivoUrl(adjuntos) {
            if (adjuntos != null) {
                const modifiedAdjuntos = adjuntos.replace("public/", ""); // Remove 'public/' segment
                return "/storage/" + modifiedAdjuntos;
            } else {
                'noFile';
            }
        },
    },
}
</script>

<template>
    <div class="container">

        <Head title="configuracion" />
        <div style="transition: 1s;">

            <div class="shadow">
                <!-- Alert Message Display -->
                <div v-if="alertMessage" :class="['alert', 'alert-' + alertMessage.type]">
                    {{ alertMessage.text }}
                </div>
                <form class="px-3 py-1 row g-4" id="form_oferta" enctype="multipart/form-data"
                    @submit.prevent="submitCreate">
                    <!-- numero_serie -->
                    <div class="form-group col-6">
                        <label class="fw-bold" for="serial_num">Nuero de Serie:</label>
                        <input type="text" name="serial_num" id="serial_num" class="form-control border"
                            v-model="configuration.serial_num" required />
                    </div>
                    <!-- app url -->
                    <div class="form-group col-6">
                        <label class="fw-bold" for="url_app">Url:</label>
                        <input type="text" name="url_app" id="url_app" class="form-control border"
                            v-model="configuration.url_app" required />
                    </div>
                    <!-- exp date -->
                    <div class="form-group col-6">
                        <label class="fw-bold" for="fecha_fin">Fech Fin:</label>
                        <input type="date" name="fecha_fin" id="fecha_fin" class="form-control border"
                            v-model="configuration.fecha_fin" required />
                    </div>
                    <!-- Texto del footer -->
                    <div class="form-group">
                        <label class="fw-bold" for="footer_text">Texto del Footer:</label>
                        <textarea name="footer_text" id="footer_text" class="form-control border"
                            v-model="configuration.footer_text" required></textarea>
                    </div>

                    <div class="form-group">
                        <label class="fw-bold" for="logo_white">Logo _white (toda la web) en PNG:</label>
                        <div class="input-group">
                            <input type="file" name="logo_white" id="logo_white" class="form-control border-file"
                                accept=".png" @change="previewImage($event, 'previewLogo_white')" />
                        </div>
                        <div class="mt-2">
                            <img class="bg-dark" :id="'previewLogo_white'" src="images/logo_white.png" alt="Preview"
                                style="max-width: 200px;" />
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="fw-bold" for="App_Logo">Logo _800 (saldrá en facturas y login, ojo color de
                            fondo
                            de la factura) en PNG:</label>
                        <div class="input-group">
                            <input type="file" name="App_Logo" id="App_Logo" class="form-control border-file"
                                accept=".png" @change="previewImage($event, 'previewApp_Logo')" />
                        </div>
                        <div class="mt-2">
                            <img :id="'previewApp_Logo'" src="images/App_Logo.png" alt="Preview"
                                style="max-width: 200px;" />
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="fw-bold" for="favicon">Favicon en ico:</label>
                        <div class="input-group">
                            <input type="file" name="favicon" id="favicon" class="form-control border-file"
                                accept=".ico" @change="previewImage($event, 'previewFavicon')" />
                        </div>
                        <div class="mt-2">
                            <img :id="'previewFavicon'" src="images/favicon.ico" alt="Preview"
                                style="max-width: 200px;" />
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="fw-bold" for="sidebar_image">Sidebar image en PNG:</label>
                        <div class="input-group">
                            <input type="file" name="sidebar_image" id="sidebar_image" class="form-control border-file"
                                accept=".png" @change="previewImage($event, 'previewSidebar_image')" />
                        </div>
                        <div class="mt-2">
                            <img :id="'previewSidebar_image'" src="images/sidebar_image.png" alt="Preview"
                                style="max-width: 200px;" />
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="fw-bold" for="video">video en mp4:</label>
                        <div class="input-group">
                            <input type="file" name="video" id="video" class="form-control border-file"
                                accept="video/mp4" @change="previewImage($event, 'previewVideo')" />
                        </div>
                        <div class="mt-2">
                            <video v-if="videoSource" :id="'previewVideo'" controls style="max-width: 200px;">
                                <source :src="videoSource" type="video/mp4">
                                Your browser does not support the video tag.
                            </video>
                            <video v-else :id="'previewVideo'" controls style="max-width: 200px;">
                                <source src="video/video.mp4" type="video/mp4">
                                Your browser does not support the video tag.
                            </video>
                        </div>
                    </div>

                    <div class="form-group" style="max-width: 150px;">
                        <label class="fw-bold" for="style_color">Stilo Color</label>
                        <input type="color" name="style_color" id="style_color" class="form-control border"
                            v-model="configuration.style_color" required />
                    </div>
                    <div class="form-group" style="max-width: 150px;">
                        <label class="fw-bold" for="texto_color">Texto Color</label>
                        <input type="color" name="texto_color" id="texto_color" class="form-control border"
                            v-model="configuration.text_color" required />
                    </div>

                    <div class="form-group" style="max-width: 150px;">
                        <label class="fw-bold" for="unique_color">Unique Color</label>
                        <input type="color" name="unique_color" id="unique_color" class="form-control border"
                            v-model="configuration.unique_color" required />
                    </div>

                    <!-- Certificado PFX y contraseña  -->
                    <!-- <div class="form-group">
                <label class="fw-bold" for="pfx_certificate">Certificado PFX:</label>
                <div class="input-group">
                    <input type="file" name="pfx_certificate" id="pfx_certificate" class="form-control border-file"
                        accept=".pfx" onchange="previewPFX(event)">
                </div>
            </div>
            <div class="form-group">
                <label class="fw-bold" for="pfx_password">Contraseña del Certificado PFX:</label>
                <input type="password" name="pfx_password" id="pfx_password" class="form-control border">
            </div> -->

                    <!-- Información del negocio  -->
                    <div class="form-group">
                        <label class="fw-bold" for="business_name">Nombre del Negocio:</label>
                        <input type="text" name="business_name" id="business_name" class="form-control border"
                            v-model="configuration.business_name" required />
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="fw-bold" for="phone">Teléfono:</label>
                                <input type="text" name="phone" id="phone" class="form-control border"
                                    v-model="configuration.phone" required />
                            </div>

                            <div class="form-group">
                                <label class="fw-bold" for="address">Dirección:</label>
                                <input type="text" name="address" id="address" class="form-control border"
                                    v-model="configuration.address" required />
                            </div>

                            <div class="form-group">
                                <label class="fw-bold" for="postal_code">Código postal:</label>
                                <input type="text" name="postal_code" id="postal_code" class="form-control border"
                                    v-model="configuration.postal_code" required />
                            </div>


                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="fw-bold" for="town">Ciudad:</label>
                                <input type="text" name="town" id="town" class="form-control border"
                                    v-model="configuration.town" required />
                            </div>

                            <div class="form-group">
                                <label class="fw-bold" for="province">Provincia:</label>
                                <input type="text" name="province" id="province" class="form-control border"
                                    v-model="configuration.province" required />
                            </div>

                            <div class="form-group">
                                <label class="fw-bold" for="email">Email:</label>
                                <input type="email" name="email" id="email" class="form-control border"
                                    v-model="configuration.email" required />
                            </div>

                            <div class="form-group">
                                <label class="fw-bold" for="tax_id">NIE/NIF:</label>
                                <input type="text" name="tax_id" id="tax_id" class="form-control border"
                                    v-model="configuration.tax_id" required />
                            </div>
                        </div>
                    </div>

                    <hr>
                    <div class="row">
                        <!-- Tipo de Negocio  -->

                        <div class="form-group col-md-6">
                            <label class="fw-bold">Tipo de Negocio:</label>
                            <div>
                                <div class="form-check">
                                    <input type="radio" class="form-check-input" name="business_type" id="individual"
                                        value="individual" v-model="configuration.business_type" />
                                    <label class="form-check-label fw-bold" for="individual">Persona Física</label>
                                </div>
                                <div class="form-check">
                                    <input type="radio" class="form-check-input" name="business_type" id="corporate"
                                        value="corporate" v-model="configuration.business_type" />
                                    <label class="form-check-label fw-bold" for="corporate">Persona Jurídica</label>
                                </div>
                            </div>
                        </div>

                        <div class="form-group col-md-6">
                            <label class="fw-bold">Tecnicios:</label>
                            <div>
                                <div class="form-check">
                                    <input type="radio" class="form-check-input" name="tecnicos" id="tecnicos" value="1"
                                        v-model="configuration.tecnicos" />
                                    <label class="form-check-label fw-bold" for="tecnicos">Si</label>
                                </div>
                                <div class="form-check">
                                    <input type="radio" class="form-check-input" name="tecnicos" id="no_tecnicos"
                                        value="0" v-model="configuration.tecnicos" />
                                    <label class="form-check-label fw-bold" for="no_series">No</label>
                                </div>
                            </div>
                        </div>

                        <div class="form-group col-md-6">
                            <label class="fw-bold">Series:</label>
                            <div>
                                <div class="form-check">
                                    <input type="radio" class="form-check-input" name="series" id="series" value="1"
                                        v-model="configuration.series" />
                                    <label class="form-check-label fw-bold" for="series">Si</label>
                                </div>
                                <div class="form-check">
                                    <input type="radio" class="form-check-input" name="series" id="no_series" value="0"
                                        v-model="configuration.series" />
                                    <label class="form-check-label fw-bold" for="no_series">No</label>
                                </div>
                            </div>
                        </div>

                        <div v-if="configuration.series == 1" class="form-group col-md-6">
                            <label for="selector">Select:</label>
                            <select id="selector" v-model="configuration.series_types" class="form-select border px-3" multiple>
                                <option v-for="value in [1, 2, 5, 7, 9, 11]" :key="value" :value="value">{{ value }}
                                </option>
                            </select>
                            <p>Selected values: {{ configuration.series_types }}</p>
                        </div>

                        <hr>
                        <div class="form-group col-md-6">
                            <label class="fw-bold">Factura Mode:</label>
                            <div>
                                <div class="form-check">
                                    <input type="radio" class="form-check-input" name="factura_mode" id="mode_1"
                                        value="1" v-model="configuration.factura_mode" />
                                    <label title="Servicios: Nombre y Descripcion" class="form-check-label fw-bold"
                                        for="mode_1">Modo 1</label>
                                </div>
                                <div class="form-check">
                                    <input type="radio" class="form-check-input" name="factura_mode" id="mode_2"
                                        value="2" v-model="configuration.factura_mode" />
                                    <label title="Servicios: Nombre, Cantidad, Descuento ,Descripcion y Precio"
                                        class="form-check-label fw-bold" for="mode_2">Modo 2</label>
                                </div>
                            </div>
                        </div>

                    </div>

                    <button type="submit" class="btn unique_bg mt-4 col-4 ms-3" :disabled="isSubmitting">
                        Guardar
                    </button>
                </form>
            </div>
        </div>
    </div>
</template>