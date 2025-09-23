<template>
  <div :class="darkMode ? 'bg-dark text-white' : 'bg-light text-dark'" class="rounded p-4">
    <label :for="id" class="form-label">
      {{ label }}
      <span v-if="isRequired && modelValue === null" class="text-danger">*</span>
    </label>

    <div class="drop-zone border rounded shadow-sm"
      :class="{ 'border-primary': isHovering, 'border-danger': isInvalidFile }" @dragover.prevent="handleDragOver"
      @dragleave.prevent="handleDragLeave" @drop.prevent="handleDrop" @click="triggerFileInput">
      <!-- File input with conditional required attribute -->
      <input :required="isRequired && modelValue === null" type="file" :id="id" ref="fileInput"
        @change="handleFileChange" :accept="acceptedFormats" style="opacity: 0;" />

      <p v-if="!selectedFile && !modelValue" class="text-muted">
        <span class="material-icons">cloud_upload</span>
        Arrastra y suelta tu archivo aquí o haz clic para seleccionar
      </p>
      <p v-if="isInvalidFile" class="text-danger">
        Tipo de archivo no válido. Por favor, sube un archivo válido.
      </p>
      <p v-if="selectedFile" class="text-success">
        <span class="material-icons">check_circle</span>
        Archivo seleccionado: {{ selectedFile.name }}
      </p>
    </div>

    <!-- Preview and Action Buttons (Shown only if fileUrl is set and not placeholder) -->
    <div class="mt-3" v-if="fileUrl">
      <p class="text-center">Vista previa:</p>

      <div class="preview-container d-flex flex-column align-items-center text-center">
        <div class="img-thumbnail fixed-size">
          <template v-if="isPdf">
            <iframe :src="fileUrl" title="Vista previa del archivo" width="100%" height="400px"
              style="border: 1px solid #ccc; border-radius: 6px;" type="application/pdf"></iframe>
          </template>
          <template v-else>
            <img :src="fileUrl" alt="Vista previa del archivo" class="img-fluid rounded" />
          </template>
        </div>

        <!-- Show "Select New File" button only if placeholder image is shown -->
        <MaterialButton v-if="isPlaceholder" @click="triggerFileInput"
          class="btn btn-primary btn-sm mt-2 d-flex align-items-center justify-content-center">
          <span class="material-icons me-2">add</span>
          Seleccionar nuevo archivo
        </MaterialButton>

        <!-- Show Change and Delete buttons only if the file is not the placeholder -->
        <div v-else class="d-flex justify-content-center mt-2">
          <MaterialButton @click="triggerFileInput"
            class="btn btn-primary btn-sm me-2 d-flex align-items-center justify-content-center">
            <span class="material-icons me-2">edit</span>
            Cambiar archivo
          </MaterialButton>
          <MaterialButton @click="deleteFile"
            class="btn btn-danger btn-sm d-flex align-items-center justify-content-center">
            <span class="material-icons me-2">delete</span>
            Eliminar archivo
          </MaterialButton>
        </div>
      </div>
    </div>

    <DarkModeObserver @darkModeChange="handleDarkModeChange" />
  </div>
</template>

<script>
import DarkModeObserver from './DarkModeObserver.vue';
import placeholderImage from '@/assets/img/placeholder.jpg';
import MaterialButton from './MaterialButton.vue';

export default {
  name: "FileSelector",
  components: {
    DarkModeObserver,
    MaterialButton
  },
  props: {
    label: { type: String, required: true },
    id: { type: String, required: true },
    disabled: { type: Boolean, default: false },
    modelValue: { type: [File, String, null], default: null },
    isEditMode: { type: Boolean, default: false },
    isRequired: { type: Boolean, default: false },
    acceptedFormats: {
      type: String,
      default: "image/*, application/pdf, .doc, .docx"
    },
    maxSize: {
      type: Number,
      default: 2 // MB
    }
  },
  emits: ['update:modelValue'],
  data() {
    return {
      selectedFile: null,
      darkMode: false,
      isHovering: false,
      isInvalidFile: false,
    };
  },
  computed: {
    fileUrl() {
      if (this.selectedFile instanceof File) {
        return URL.createObjectURL(this.selectedFile);
      }
      if (typeof this.modelValue === "string" && this.modelValue.trim() !== "") {
        return `/storage/${this.modelValue.replace("public/", "")}`;
      }
      return placeholderImage;
    },
    isPlaceholder() {
      return this.fileUrl === placeholderImage;
    },
    isPdf() {
      return this.selectedFile instanceof File
        ? this.selectedFile.type === 'application/pdf'
        : typeof this.modelValue === 'string' && this.modelValue.toLowerCase().endsWith('.pdf');
    },
  },
  watch: {
    modelValue: {
      immediate: true,
      handler(newValue) {
        if (newValue !== this.selectedFile) {
          this.selectedFile = newValue;
        }
      },
    },
  },
  methods: {
    triggerFileInput() {
      this.$refs.fileInput.click();
    },
    handleFileChange(event) {
      const file = event.target.files[0];
      this.processFile(file);
    },
    handleDrop(event) {
      const file = event.dataTransfer.files[0];
      this.processFile(file);
      this.isHovering = false;
    },
    handleDragOver() {
      this.isHovering = true;
    },
    handleDragLeave() {
      this.isHovering = false;
    },
    processFile(file) {
      const maxSizeInBytes = this.maxSize * 1024 * 1024;
      if (file && file.size > maxSizeInBytes) {
        alert(`El tamaño del archivo supera los ${this.maxSize} MB. Por favor, elige un archivo más pequeño.`);
        this.selectedFile = null;
        return;
      }
      if (!this.isImage(file) && !['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'].includes(file.type)) {
        this.isInvalidFile = true;
      } else {
        this.isInvalidFile = false;
        this.selectedFile = file;
        this.$emit('update:modelValue', file);
      }
    },
    isImage(file) {
      return file && (file instanceof File ? file.type.startsWith('image/') : /\.(png|jpg|jpeg)$/.test(file));
    },
    handleDarkModeChange(isDark) {
      this.darkMode = isDark;
    },
    deleteFile() {
      this.selectedFile = null;
      this.$emit('update:modelValue', null);
    },
  },
};
</script>

<style scoped>
.bg-dark {
  background-color: #15172b;
}

.bg-light {
  background-color: #ffffff;
}

.text-dark {
  color: #343a40;
}

.text-white {
  color: white;
}

.drop-zone {
  border: 2px dashed #ccc;
  text-align: center;
  cursor: pointer;
  transition: border-color 0.3s, background-color 0.3s;
  padding: 20px;
  border-radius: 8px;
}

.border-primary {
  border-color: #007bff;
}

.border-danger {
  border-color: #dc3545;
}

.shadow-sm {
  box-shadow: 0 .125rem .25rem rgba(0, 0, 0, .075);
}

.text-muted {
  color: #6c757d;
}

.text-success {
  color: #28a745;
}

.fixed-size {
  width: 250px;
  height: 250px;
  overflow: hidden;
  display: flex;
  justify-content: center;
  align-items: center;
}

.preview-container {
  position: relative;
}
</style>
