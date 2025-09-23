<template>
    <!-- Tarjeta compacta -->
    <div v-if="onlyCard" class="compact-data-card p-3">
        <div class="d-flex justify-content-between align-items-center flex-wrap w-100">
        <!-- Ícono -->
        <div class="icon-circle shadow-sm me-2" :class="icon.background">
            <i class="material-icons opacity-10" :class="icon.color" style="font-size: 1rem;">
            {{ icon.name }}
            </i>
        </div>

        <!-- Contenido -->
        <div class="text-end flex-grow-1">
            <div class="card-title text-muted small mb-1" style="font-size: 0.8rem;">
            {{ title.text }}
            </div>

            <div class="card-text fw-bold text-dark lh-9 card-text-responsive">
            <template v-if="Array.isArray(title.value)">
                <div class="d-flex flex-wrap justify-content-end gap-2">
                <span
                    v-for="(item, index) in title.value"
                    :key="index"
                    v-html="getFormattedValue(item)"
                    class="d-flex align-items-center"
                />
                </div>
            </template>

            <template v-else-if="typeof title.value === 'object' && title.value !== null">
                {{ title.value.label }}: {{ title.value.count }}
            </template>

            <template v-else>
                {{ title.value }}
            </template>
            </div>
        </div>
        </div>
    </div>

  <!-- Tarjeta normal -->
  <div
    v-else
    class="card mb-2 fixed-card w-100"
    :class="directionReverse ? reverseDirection : ''"
    :style="cardDynamicStyle"
  >
    <!-- Encabezado -->
    <div class="card-header p-2 pt-2 d-flex align-items-center flex-nowrap position-relative">
      <!-- Ícono con o sin modal -->
      <div
        class="icon icon-md text-center me-2 d-flex align-items-center justify-content-center flex-shrink-0"
        :class="[icon.background, circular ? 'rounded-circle' : 'rounded-3']"
        :style="iconStyle"
        v-if="modal"
        :data-bs-toggle="'modal'"
        :data-bs-target="modal"
        role="button"
        style="cursor: pointer;"
      >
        <i class="material-icons opacity-10" :class="icon.color" style="font-size: 1.25rem;">
          {{ icon.name }}
        </i>
      </div>

      <div
        v-else
        class="icon icon-md text-center me-2 d-flex align-items-center justify-content-center flex-shrink-0"
        :class="[icon.background, circular ? 'rounded-circle' : 'rounded-3']"
        :style="iconStyle"
      >
        <i class="material-icons opacity-10" :class="icon.color" style="font-size: 1.25rem;">
          {{ icon.name }}
        </i>
      </div>

      <!-- Título -->
      <div class="pt-1 flex-grow-1 text-end overflow-hidden">
        <p class="text-xs mb-0 text-capitalize text-truncate" :style="textStyle" style="max-width: 100%; font-size: 0.75rem;">
          {{ title.text }} <span v-if="detail">(<span v-html="detail"></span>)</span>
        </p>

        <h5 class="mb-0 fw-bold text-nowrap" style="font-size: 0.95rem; line-height: 1.2;">
          {{ title.value }}
        </h5>
      </div>
      <!-- Enlace que cubre toda la cabecera -->
  <a
    v-if="title.url"
    :href="title.url"
    class="position-absolute top-0 start-0 w-100 h-100"
    style="z-index: 1; text-decoration: none;"
    :aria-label="title.text || 'Ir al enlace'"
  ></a>
    </div>

    <hr v-if="estadosArray.length" class="dark horizontal my-0" />

    <div v-if="estadosArray.length" class="card-footer p-1 pt-0 pb-0">
      <div class="d-flex justify-content-end align-items-end w-100 pe-1">
        <div class="mt-1 d-flex align-items-center gap-1">
          <button @click="toggleEstados" class="btn btn-link p-0 text-muted small d-flex align-items-center gap-1 mb-2">
            <i class="fa-solid fa-chart-simple"></i>
            <strong style="font-size: 0.65rem;">Ver estados</strong>
            <i class="fa" :class="showEstados ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
          </button>
        </div>
      </div>

      <transition name="fade">
        <div v-if="showEstados" class="mb-2">
          <div class="d-flex flex-wrap gap-2">
            <span
              v-for="estado in estadosArray"
              :key="estado"
              class="badge rounded-pill px-2 py-1 d-flex align-items-center gap-1"
              :class="badgeColorClass(estado)"
              style="font-size: 0.58rem; border-radius: 1rem; border-width: 1px; white-space: nowrap;"
            >
              <i :class="badgeIcon(estado)" class="fa-sm"></i>
              {{ estado }}
            </span>
          </div>
        </div>
      </transition>
    </div>
  </div>
</template>

<script>
export default {
  name: 'MiniStatisticsCard',
  props: {
    onlyCard: Boolean,
    title: Object,
    detail: String,
    icon: Object,
    directionReverse: Boolean,
    circular: Boolean,
    estados: String,
    modal: {
      type: String,
      default: null
    },
    customHeightClosed: {
      type: String,
      default: '85px'
    },
    customHeightOpen: {
      type: String,
      default: '170px'
    }
  },
  data() {
    return {
      reverseDirection: 'flex-row-reverse justify-content-between',
      showEstados: false
    };
  },
  computed: {
    estadosArray() {
      return this.estados ? this.estados.split(',').map(s => s.trim()) : [];
    },
    cardDynamicStyle() {
      return {
        minHeight: '85px',
        height: this.showEstados ? 'auto' : '85px',
        transition: 'all 0.3s ease-in-out',
        overflow: 'hidden',
        borderRadius: '0.75rem'
      };
    },
    iconStyle() {
      return {
        width: this.circular ? '60px' : '40px',
        height: this.circular ? '60px' : '40px',
        boxShadow: 'none'
      };
    },
    textStyle() {
      return {
        fontSize: '0.85rem',
        opacity: 0.8
      };
    }
  },
  watch: {
    estados(newVal, oldVal) {
      if (newVal !== oldVal) {
        this.showEstados = false;
      }
    }
  },
  methods: {
    toggleEstados() {
      this.showEstados = !this.showEstados;
    },
    badgeColorClass(estado) {
      const lower = estado.toLowerCase();
      if (lower.includes('aceptado')) return 'estado-aceptado';
      if (lower.includes('pagado parcialmente')) return 'estado-pagado-parcialmente';
      if (lower.includes('pagado')) return 'estado-pagado';
      if (lower.includes('recepcionado')) return 'estado-recepcionado';
      if (lower.includes('facturado parcialmente')) return 'estado-facturado-parcial';
      if (lower.includes('pendiente')) return 'estado-pendiente';
      if (lower.includes('rechazado')) return 'estado-rechazado';
      if (lower.includes('facturado')) return 'estado-facturado';
      if (lower.includes('rectificada')) return 'estado-rectificada';
      if (lower.includes('enviado')) return 'estado-enviado';
      if (lower.includes('gasto')) return 'estado-gasto';
      return 'estado-desconocido';
    },
    badgeIcon(estado) {
      const lower = estado.toLowerCase();
      if (lower.includes('aceptado')) return 'fa-solid fa-check';
      if (lower.includes('pagado parcialmente')) return 'fa-solid fa-file-invoice-dollar';
      if (lower.includes('pagado')) return 'fa-solid fa-file-invoice-dollar';
      if (lower.includes('recepcionado')) return 'fa-solid fa-box-open';
      if (lower.includes('pendiente')) return 'fa-regular fa-clock';
      if (lower.includes('rechazado')) return 'fa-solid fa-xmark';
      if (lower.includes('facturado parcialmente')) return 'fa-solid fa-file-invoice-dollar';
      if (lower.includes('facturado')) return 'fa-solid fa-file-invoice';
      if (lower.includes('rectificada')) return 'fa-solid fa-file-invoice';
      if (lower.includes('enviado')) return 'fa-solid fa-truck';
      if (lower.includes('gasto')) return 'fa-solid fa-money-bill';
      return 'fa-solid fa-circle-question';
    },
    getFormattedValue(item) {
      const label = item.label.toLowerCase();

      if (label.includes("empresa")) {
        return `<i class="fas fa-building me-1" style="color: #747b8a ;"></i> ${item.count}`;
      }

      if (label.includes("personal")) {
        return `<i class="fas fa-user me-1" style="color: #747b8a ;"></i> ${item.count}`;
      }

      return `<i class="fas fa-circle me-1" style="color: #747b8a ;"></i> ${item.count}`;
    }
  }
};
</script>

<style scoped>
.card-header {
  position: relative;
}

.card-text-responsive {
  font-size: 0.82rem;
}

.compact-data-card {
  height: 50px;
  padding-bottom: 13px !important;
  border-radius: 14px;
  background-color: #ffffff;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.05);
  transition: transform 0.2s ease, box-shadow 0.2s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  isolation: isolate;
  color: inherit;
}

.compact-data-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
}

.icon-circle {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  color: #fff;
}

/* Estados */
.estado-pendiente, .estado-asignado-a-obra {
  background-color: #fff8e6;
  color: #b38f00;
  border: 1px solid #ffe299;
}

.estado-rechazado, .estado-gasto, .estado-incompleto {
  background-color: #ffe8e8;
  color: #d9534f;
  border: 1px solid #f8c6c6;
}

.estado-aceptado {
  background-color: #e8f9e8;
  color: #5cb85c;
  border: 1px solid #c5e6c5;
}

.estado-facturado-parcial {
  background-color: #e8f4f9;
  color: #5bc0de;
  border: 1px solid #cce8f0;
}

.estado-facturado {
  background-color: #eae7ff;
  color: #6f42c1;
  border: 1px solid #ccc0ff;
}

.estado-pagado, .estado-recepcionado, .estado-enviado {
  background-color: #e8f9e8;
  color: #5cb85c;
  border: 1px solid #c5e6c5;
}

.estado-rectificada {
  background-color: #ffdbbb21;
  color: #e99a2c;
  border: 1px solid #ffc067;
}

.estado-pagado-parcialmente {
  background-color: #e8f4f9;
  color: #66f1c2;
  border: 1px solid #cce8f0;
}

.estado-desconocido {
    background-color: #f2f2f2;
    color: #777;
    border: 1px solid #ddd;
}

.fade-enter-active, .fade-leave-active {
    transition: all 0.4s ease;
}

.fade-enter-from, .fade-leave-to {
    opacity: 0;
    transform: translateY(-10px);
}
</style>
