<template>
    <div :class="darkMode ? 'bg-dark text-white' : 'bg-light text-dark'" class="rounded p-4">
        <label class="form-label">
            {{ label }}
        <span v-if="isRequired && files.length === 0" class="text-danger">*</span>
        </label>

        <div class="drop-zone border rounded shadow-sm"
            :class="{ 'border-primary': isHovering, 'border-danger': isInvalidFile }" @dragover.prevent="handleDragOver"
            @dragleave.prevent="handleDragLeave" @drop.prevent="handleDrop" @click="triggerFileInput"
        >
            <input type="file" ref="fileInput" multiple :accept="acceptedFormats" @change="handleFileChange"
                style="display: none;" />

            <p v-if="files.length === 0" class="text-muted">
                <span class="material-icons">cloud_upload</span>
                Arrastra y suelta tus archivos aquí o haz clic para seleccionar
            </p>

            <p v-if="isInvalidFile" class="text-danger">
                Tipo de archivo no válido o excede el límite permitido ({{ maxFiles }}).
            </p>
        </div>

        <!-- File Limit Display -->
        <div class="mt-2 text-muted">
            Archivos seleccionados: {{ files.length }}/{{ maxFiles }}
        </div>

        <!-- File Previews -->
        <div class="mt-3">
            <div v-for="(file, index) in files" :key="index" class="preview-container mb-2">
                <div class="d-flex align-items-center">
                    <div class="img-thumbnail fixed-size me-3 d-flex justify-content-center align-items-center">
                        <!-- ✅ Si es imagen -->
                        <img
                            v-if="isImage(file)"
                            :src="getFileUrl(file)"
                            alt="Vista previa de imagen"
                            class="img-fluid rounded"
                        />
                        <!-- ✅ Si es PDF -->
                        <a
                            v-else-if="isPdf(file)"
                            :href="getPdfUrl(file)"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="text-decoration-none"
                        >
                            <i class="fas fa-file-pdf fa-3x text-danger"></i>
                        </a>
                        <!-- ✅ Si es otro tipo -->
                        <img
                            v-else
                            :src="placeholder"
                            alt="Archivo no soportado"
                            class="img-fluid rounded"
                        />
                    </div>

                    <p class="mb-0 flex-grow-1 d-none d-md-flex">{{ file.name }}</p>

                    <MaterialButton @click="removeFile(index)" class="btn btn-sm ms-2">
                        <span class="material-icons">delete</span>
                    </MaterialButton>
                </div>
            </div>
        </div>

        <!-- Add More Files Button - Only visible if there are already files -->
        <div class="mt-3" v-if="files.length > 0">
            <MaterialButton @click="triggerFileInput" class="btn btn">
                Añadir Más Archivos
            </MaterialButton>
        </div>

        <DarkModeObserver @darkModeChange="handleDarkModeChange" />
    </div>
</template>

<script>
import DarkModeObserver from './DarkModeObserver.vue';
import MaterialButton from './MaterialButton.vue';
import placeholder from '@/assets/img/placeholder.jpg'; // Import the placeholder image

export default {
    name: 'MaterialFileInput',
    components: { DarkModeObserver, MaterialButton },
    props: {
        label: { type: String,  default: 'Subir Archivos' },
        modelValue: { type: Array,  default: () => [] },
        isRequired: { type: Boolean, default: false },
        acceptedFormats: {  type: String,  default: 'image/*,application/pdf' },
        maxFiles: {  type: Number,  default: 4 },
    },
    data() {
        return {
            files: this.initializeFiles(), // Initialize with the modelValue or URLs
            isHovering: false,
            isInvalidFile: false,
            darkMode: false
        };
    },
    watch: {
        modelValue: {
        immediate: true,
        handler(newValue) {
            this.files = this.initializeFiles(newValue);
        }
        }
    },
    mounted() {
        // Dynamically load pdf.js CDN script
        const pdfScript = document.createElement('script');
        pdfScript.src = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.10.377/pdf.min.js';
        pdfScript.onload = () => {
        this.pdfjsLib = window.pdfjsLib;

        // ✅ Indica la ruta del worker:
        this.pdfjsLib.GlobalWorkerOptions.workerSrc =
            'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.10.377/pdf.worker.min.js';
        };
        document.body.appendChild(pdfScript);
    },
    methods: {
        initializeFiles(value = this.modelValue) {
            return value.map(file => {
                    if (typeof file === 'string') {
                    return file;
                }

                // ⚡ Si es un archivo PDF y no tiene preview, genera la portada
                if (file instanceof File && file.type === 'application/pdf' && !file.preview) {
                    this.loadPdfPreview(file);
                }

                return file;
            });
        },
        triggerFileInput() {
            this.$refs.fileInput.click();
        },
        handleFileChange(event) {
            const selectedFiles = Array.from(event.target.files);
            const totalFiles = this.files.length + selectedFiles.length;

            if (totalFiles > this.maxFiles) {
                this.isInvalidFile = true;
                return;
            }

            if (this.validateFiles(selectedFiles)) {
                selectedFiles.forEach(file => {
                if (file.type === 'application/pdf') {
                    this.loadPdfPreview(file);
                }
                });
                this.files.push(...selectedFiles);
                this.$emit('update:model-value', this.files); // Emit the updated files array
                this.isInvalidFile = false; // Reset invalid flag
            }
            else {
                this.isInvalidFile = true;
            }
        },
        handleDragOver() {
            this.isHovering = true;
        },
        handleDragLeave() {
            this.isHovering = false;
        },
        handleDrop(event) {
            this.isHovering = false;
            const droppedFiles = Array.from(event.dataTransfer.files);
            const totalFiles = this.files.length + droppedFiles.length;

            if (totalFiles > this.maxFiles) {
                this.isInvalidFile = true;
                return;
            }

            if (this.validateFiles(droppedFiles)) {
                droppedFiles.forEach(file => {
                if (file.type === 'application/pdf') {
                    this.loadPdfPreview(file);
                }
                });
                this.files.push(...droppedFiles);
                this.$emit('update:model-value', this.files); // Emit the updated files array
                this.isInvalidFile = false; // Reset invalid flag
            }
            else {
                this.isInvalidFile = true;
            }
        },
        validateFiles(fileArray) {
            const acceptedTypes = this.acceptedFormats.split(',').map(format => format.trim());

            const isValid = fileArray.every(file => {
                const isAccepted = acceptedTypes.some(type => {
                // Match with MIME type directly
                return file.type === type || file.type.match(type);
                });
                return isAccepted;
            });

            return isValid;
        },
        loadPdfPreview(file) {
            const reader = new FileReader();
            reader.onload = async (e) => {
                const arrayBuffer = e.target.result;
                const pdfDoc = await this.pdfjsLib.getDocument(arrayBuffer).promise;
                const page = await pdfDoc.getPage(1);
                const scale = 1.5;
                const viewport = page.getViewport({ scale });

                const canvas = document.createElement('canvas');
                const context = canvas.getContext('2d');
                canvas.width = viewport.width;
                canvas.height = viewport.height;

                await page.render({
                canvasContext: context,
                viewport
                }).promise;

                // Guarda la imagen de portada SOLO como preview
                const imageUrl = canvas.toDataURL();
                file.preview = imageUrl;

                this.$forceUpdate();
            };
            reader.readAsArrayBuffer(file);
        },

        removeFile(index) {
            this.files.splice(index, 1);
            this.$emit('update:model-value', this.files); // Emit the updated files array
        },
            handleDarkModeChange(isDark) {
            this.darkMode = isDark;
        },
        getFileUrl(file) {
            if (file != null && file != '') {
                if (file instanceof File) {
                if (file.type.startsWith('image/')) {
                    return URL.createObjectURL(file);
                }
                return null; // PDF u otros, no necesita URL
                }
                if (typeof file === 'string') {
                if (/\.(jpe?g|png|gif|webp|bmp)$/i.test(file)) {
                    return "/storage/" + file.replace("public/", "");
                }
                return null; // PDF u otros string
                }
            }

            return this.placeholder;
        },
        isImage(file) {
            if (file instanceof File) {
                return file.type.startsWith('image/');
            }
            if (typeof file === 'string') {
                return /\.(jpe?g|png|gif|webp|bmp)$/i.test(file);
            }
            return false;
        },
        isPdf(file) {
            if (file instanceof File) {
                return file.type === 'application/pdf';
            }
            if (typeof file === 'string') {
                return file.toLowerCase().endsWith('.pdf');
            }
            return false;
        },
        getPdfUrl(file) {
            if (file instanceof File) {
                return URL.createObjectURL(file);
            }
            if (typeof file === 'string') {
                return "/storage/" + file.replace(/^public\//, '');
            }
            return '#';
        },
    }
};
</script>

<style scoped>
.drop-zone {
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 20px;
    cursor: pointer;
    transition: border 0.2s;
}

.preview-container img {
    max-height: 100px;
    max-width: 100px;
}

.fixed-size {
    width: 100px;
    height: 100px;
    overflow: hidden;
}
</style>
