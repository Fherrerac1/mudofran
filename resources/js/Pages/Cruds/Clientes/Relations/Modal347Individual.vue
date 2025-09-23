<template>
  <BootstrapModal -modal-id="Modal347">
    <form @submit.prevent="submitForm">
      <!-- Year Input -->
      <div class="form-group mb-3">
        <label for="year" class="form-label">A&ntilde;o</label>
        <input type="number" class="form-control border px-3" id="year" v-model="formData.year" placeholder="yyyy"
          min="2020" max="2100" required />
      </div>
      <!-- Format Selection -->
      <div class="form-group mb-3">
        <label for="format" class="form-label">Formato</label>
        <select class="form-select border px-3" id="format" v-model="formData.format" required>
          <option value="pdf">PDF</option>
          <option value="excel">Excel</option>
        </select>
      </div>
      <!-- Submit Button -->
      <MaterialButton :disabled="isSubmitting" type="submit" class="btn btn-primary mt-3">
        Enviar
      </MaterialButton>
    </form>
  </BootstrapModal>
</template>

<script>
import BootstrapModal from "@/Components/BootstrapModal.vue";
import MaterialButton from "@/Components/MaterialButton.vue";
import Swal from 'sweetalert2';

export default {
  components: {
    BootstrapModal,
    MaterialButton
  },
  props: {
    cliente: {
      type: Object,
      required: true
    },
  },
  data() {
    return {
      formData: {
        year: "",
        format: "pdf",
      },
      isSubmitting: false,
    };
  },
  methods: {
    submitForm() {
      this.isSubmitting = true;

      axios.get(route('clientes.modelo347PDF', this.cliente.id), {
        params: this.formData,
        responseType: 'blob',  // Expecting blob response (PDF or Excel)
      })
        .then(response => {
          const contentType = response.headers['content-type'];

          if (contentType.includes('application/json')) {
            this.handleJsonError(response.data);  // Handle JSON error if response is JSON
          } else {
            this.downloadFile(response.data, response.headers);
            Swal.fire({
              icon: 'success',
              title: 'Descarga completada',
              text: 'El archivo se ha descargado con éxito.',
            });
          }
        })
        .catch(error => {
          let errorMessage = 'Hubo un problema al descargar el archivo.';

          if (error.response) {
            const contentType = error.response.headers['content-type'];

            if (contentType && contentType.includes("application/json")) {
              this.handleJsonError(error.response.data);
            } else {
              this.downloadFile(error.response.data, error.response.headers, true);
              errorMessage = 'El archivo podría estar corrupto. Se ha descargado para su revisión.';
            }
          } else {
            // If no response from server, handle network errors
            errorMessage = 'Error de conexión. Por favor, intente de nuevo más tarde.';
          }

          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: errorMessage,
          });
        })
        .finally(() => {
          this.isSubmitting = false;
        });
    },

    handleJsonError(blobData) {
      const reader = new FileReader();
      reader.onload = () => {
        try {
          const jsonResponse = JSON.parse(reader.result);
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: jsonResponse?.message || 'Hubo un problema al procesar la solicitud.',
          });
        } catch {
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'No se pudo leer el mensaje de error del servidor.',
          });
        }
      };
      reader.readAsText(blobData);  // Read the response as text to parse JSON
    },

    downloadFile(blobData, headers, isError = false) {
      const blob = new Blob([blobData], { type: headers['content-type'] });
      const link = document.createElement('a');
      link.href = URL.createObjectURL(blob);

      // Determine filename
      const contentDisposition = headers['content-disposition'];
      let filename = isError ? 'error_file' : 'exported_file';
      if (contentDisposition) {
        const match = contentDisposition.match(/filename="?([^"]+)"?/);
        if (match) filename = match[1];
      }

      filename += isError ? '_corrupt' : '';
      filename += blob.type.includes('pdf') ? '.pdf' : '.xlsx';

      link.download = filename;
      document.body.appendChild(link);
      link.click();

      // Cleanup
      document.body.removeChild(link);
      URL.revokeObjectURL(link.href);
    },
  }
};
</script>
