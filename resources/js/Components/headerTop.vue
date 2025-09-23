<script>
import notificaciones from './notificaciones.vue';
import FirebaseToken from './Firebase.vue';
export default {
    components: {
        notificaciones,
        FirebaseToken
    },
    data() {
        return {
            showModal: false,
            showDropdown: false,
            isSubmitting: false,
            thumbnailUrl: null,
            formularioReclamacion: {
                title: '',
                messages: [
                    {
                        user_id: this.user.id,
                        message: '',
                    }
                ],
                archivo: null
            },
            alertMessage: null,
        };
    },
    mounted() {
        document.addEventListener('click', (e) => {
            const header_menu = document.querySelector('.dropdown-countainer');
            if (header_menu && !header_menu.contains(e.target)) {
                this.showDropdown = false;
            }
        });
    },
    methods: {
        toggleDropdown() {
            this.showDropdown = !this.showDropdown;
        },
        closeDropdown() {
            this.showDropdown = false;
        },
        submitCreate() {
            if (this.isSubmitting) {
                return;
            }

            this.isSubmitting = true;

            axios
                .post("/reclamaciones/save", this.formularioReclamacion, {
                    headers: {
                        "Content-Type": "multipart/form-data",
                    },
                })
                .then((response) => {
                    location.reload();
                })
                .catch((error) => {
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
        handleFileChange(event) {
            const selectedFile = event.target.files[0];
            if (selectedFile) {
                this.generateThumbnail(selectedFile);
                this.formularioReclamacion.archivo = selectedFile;
            }
        },
        generateThumbnail(file) {
            const reader = new FileReader();

            reader.onload = (e) => {
                // Assuming you have a property to hold the thumbnail URL
                this.thumbnailUrl = e.target.result;
            };

            // Read the file as a data URL for preview
            reader.readAsDataURL(file);
        }
        ,
    },
    props: {
        user: {
            type: Object,
            required: true
        },
        title: {
            type: String,
            required: true
        }
    },

}
</script>
<template>
    <FirebaseToken />
    <!-- Admin Header Section -->
    <header class="sticky-top border-bottom style_color" style="height: 70px;">
        <nav class="navbar navbar-expand-lg ">
            <div class="d-flex justify-content-between align-items-center w-100 px-3">
                <h3 class="m-0">{{ title }}</h3>
                <!-- MENÚ -->
                <div class="navbar navbar-dark">
                    <div class="d-flex align-items-center justify-content-lg-end">
                        <!-- {{-- @if ('true' == false) --}} -->
                        <notificaciones :user="user"></notificaciones>
                        <div class="dropdown-countainer">
                            <button class="btn dropdown-toggle fw-bold text-white" @click="toggleDropdown">
                                {{ user.nombre }}
                            </button>

                            <ul v-if="showDropdown" @click.self="closeDropdown"
                                class="dropdown-menu dropdown-menu-dark d-block" id="header-dropmenu"
                                style="margin-left: -150px">


                                <li
                                    v-if="user.rol === 'decano' || user.rol === 'gestor' || user.rol === 'admin' || user.rol === 'tesorero' || user.rol === 'vocal'">
                                    <a href="/admin/admin_zona" class="dropdown-item text-uppercase">MI ZONA ({{
                                        user.rol
                                        }})</a>
                                </li>

                                <li v-else>
                                    <a href="/colegiado_zona" class="dropdown-item text-uppercase">MI ZONA ({{ user.rol
                                        }})</a>
                                </li>
                                <li v-if="user.rol === 'decano' || user.rol === 'gestor' || user.rol === 'admin'"><a
                                        href="/lista_notificaciones" class="dropdown-item">NOTIFICACIONES</a>
                                </li>
                                <li v-if="user.rol === 'decano' || user.rol === 'gestor' || user.rol === 'admin'"><a
                                        href="/list/activities" class="dropdown-item">ACTIVIDADES</a>
                                </li>

                                <li v-if="user.rol === 'colegiado'">
                                    <a href="#" data-bs-toggle="modal" data-bs-target="#nuevo_reclamacion"
                                        class="dropdown-item">
                                        <i class="fas fa-exclamation-circle"></i> RECLAMACION
                                    </a>
                                </li>

                                <li><a href="/users/logout" class="dropdown-item">SALIR</a></li>
                            </ul>
                        </div>
                    </div>
                </div>

            </div>
        </nav>
    </header>

    <!--add reclamacion modal-->
    <div class="modal fade" id="nuevo_reclamacion" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true"
        >
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title">RECLAMACION</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Alert Message Display -->
                    <div v-if="alertMessage" :class="['alert', 'alert-' + alertMessage.type]">
                        {{ alertMessage.text }}
                    </div>
                    <form class="px-3 py-1 row g-4" id="form_documento" enctype="multipart/form-data"
                        @submit.prevent="submitCreate">
                        <div class="col-12">
                            <label for="title" class="form-label">Título:</label>
                            <input type="text" v-model="formularioReclamacion.title" class="form-control" id="title"
                                name="title" required>
                            <div class="invalid-feedback">Por favor ingresa un título.</div>
                        </div>
                        <div v-for="(message, index) in formularioReclamacion.messages" :key="index" class="col-12">
                            <label for="description" class="form-label">Descripción:</label>
                            <textarea class="form-control" v-model="message.message" id="description" name="description"
                                rows="4" required></textarea>
                            <div class="invalid-feedback">Por favor ingresa una descripción.</div>
                        </div>
                        <div class="col-12">
                            <label for="archivo" class="form-label fw-bold">Subir Imagen:</label>
                            <input type="file" name="archivo" @change="handleFileChange" id="archivo"
                                class="form-control" accept="image/*" max="6144000" />
                            <div class="invalid-feedback">Por favor selecciona una imagen (tamaño máximo: 6MB).</div>
                        </div>

                        <!-- Display thumbnail -->
                        <div class="col-3 mt-3" v-if="thumbnailUrl">
                            <label class="form-label">Thumbnail:</label>
                            <img :src="thumbnailUrl" alt="Thumbnail" class="img-thumbnail" />
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">Enviar</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</template>
