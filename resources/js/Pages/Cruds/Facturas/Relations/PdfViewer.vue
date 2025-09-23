<template>
  <div class="w-100 h-100">
    <VPdfViewer :src="getArchivoUrl(factura.pdf)" />
  </div>
</template>

<script>
import { VPdfViewer } from '@vue-pdf-viewer/viewer';

// OR THE FOLLOWING IMPORT FOR VUE 2
// import PDFViewer from 'pdf-viewer-vue/dist/vue2-pdf-viewer'

export default {
  components: {
    VPdfViewer,
  },
  props: {
    factura: {
      type: Object,
      required: true,
    },
  },
  data() {
    return {}
  },
  methods: {
    getArchivoUrl(archivo) {
      const storageBaseUrl = '/storage'; // Replace with your actual storage base URL
      if (archivo) {
        return `${storageBaseUrl}/${archivo}`;
      } else {
        return '/noFile';
      }
    },
    handleDownload() {
      // Fetch the PDF file from the storage URL
      const pdfUrl = this.getArchivoUrl(this.factura.pdf);

      // Specify a custom file name for the download
      const customFileName = this.factura.numFactura;  // Change this to your desired name

      // Create an anchor tag to simulate a download
      const link = document.createElement('a');
      link.href = pdfUrl;
      link.download = customFileName;  // Set the custom file name

      // Trigger the download
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
    },
  },
}
</script>
