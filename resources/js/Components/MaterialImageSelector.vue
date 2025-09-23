<template>
  <div class="row mt-4 overflow-hidden">
    <div>
      <material-avatar :img="getImage" shadow="regular" class="img-fluid w-20 mt-7">
      </material-avatar>
    </div>
    <div class="mt-1 mb-2">
      <material-button v-show="!hasImage" size="sm" type="button">
        <label for="imageInput" class="mb-0 text-white small cursor-pointer">Seleccionar imagen</label>
        <input id="imageInput" type="file" style="opacity: 0; max-width: 10px;" :required="isRequired" accept="image/*"
          @change.prevent="onFileChange">
      </material-button>

      <div v-show="hasImage">
        <material-button class="mx-2" size="sm" type="button" color="danger" @click.prevent="onFileRemove">
          <label class="mb-0 text-white small cursor-pointer"> &#10005; Eliminar</label>
        </material-button>
        <material-button size="sm" type="button">
          <label for="imageInput2" class="mb-0 text-white small cursor-pointer">Cambiar</label>
          <input id="imageInput2" type="file" style="display: none;" accept="image/*" @change.prevent="onFileChange">
        </material-button>
      </div>
    </div>
  </div>
</template>

<script>
import MaterialInput from "./MaterialInput.vue";
import MaterialButton from "./MaterialButton.vue";
import MaterialAvatar from "./MaterialAvatar.vue";
import ValidationError from "./ValidationError.vue";
import placeholder from '@/assets/img/placeholder.jpg';

export default {
  name: "MaterialImageSelector",
  components: {
    MaterialInput,
    MaterialButton,
    MaterialAvatar,
    ValidationError,
  },
  props: {
    modelValue: { type: [File, String, null], default: null },
    isRequired: { type: Boolean, default: false },
  },
  data() {
    return {
      file: null,
      initialImageUrl: placeholder,
    };
  },
  emits: ['update:modelValue'],
  computed: {
    getImage() {
      return this.file ? URL.createObjectURL(this.file) : this.initialImageUrl;
    },
    hasImage() {
      return this.file || this.modelValue;
    }
  },
  watch: {
    modelValue: {
      immediate: true, // This makes the watcher run immediately when the component is created
      handler(newValue) {
        if (newValue) {
          this.file = null; // Reset local file when modelValue changes
          this.initialImageUrl = this.getArchivoUrl(newValue); // Update the image URL based on new modelValue
        } else {
          this.file = null; // Clear file if modelValue is null
          this.initialImageUrl = placeholder; // Reset to placeholder if there's no modelValue
        }
      }
    }
  },
  mounted() {
    if (this.modelValue) {
      this.initialImageUrl = this.getArchivoUrl(this.modelValue);
    }
  },
  methods: {
    onFileChange(event) {
      this.file = event.target.files[0];
      this.$emit('update:modelValue', this.file);
    },
    onFileRemove() {
      this.file = null;
      this.$emit('update:modelValue', null);
    },
    getArchivoUrl(archivo) {
      if (archivo) {
        if (typeof archivo === 'string') {
          const modifiedArchivo = archivo.replace("public/", "");
          return "/storage/" + modifiedArchivo;
        } else if (archivo instanceof File) {
          return URL.createObjectURL(archivo);
        } else {
          console.error("Tipo de archivo no válido: se esperaba una cadena o instancia de File");
          alert("Tipo de archivo no válido. Por favor, selecciona un archivo válido.");
          return placeholder;
        }
      } else {
        return placeholder;
      }
    },
  },
};
</script>
