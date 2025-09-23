<template>
    <div class="card h-100 d-flex flex-column chart-card-hover"  :style="{ height: computedHeight }" >
        <!-- Título centrado + botones flechas -->
        <div class="card-body flex-shrink-0 d-flex align-items-start px-3 pt-3 position-relative">
            <div class="flex-grow-1 text-center">
                <h6 class="card-title mb-1 fw-bold">{{ title }}</h6>
                <p class="text-sm text-muted mb-0" v-html="subtitle"></p>
            </div>

            <!-- Botones flechas (siempre visibles pero sutiles) -->
            <div v-if="showToggle" class="toggle-buttons">
                <button class="btn btn-icon toggle-btn" @click="$emit('toggle-prev')" title="Anterior">
                    <i class="fas fa-chevron-left"></i>
                </button>

                <button class="btn btn-icon toggle-btn" @click="$emit('toggle-next')" title="Siguiente">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>

        <!-- Slot del gráfico -->
        <div class="card-header flex-grow-1 d-flex align-items-center justify-content-center p-0 position-relative mt-n4 mx-3 bg-transparent">
            <div class="w-100 border-radius-lg py-2 pe-1" :class="[`bg-gradient-${color}`, `shadow-${color}`, textColorClass]" >
                <slot />
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-auto px-3 pb-3 text-center" v-if="footer || $slots.footer">
            <slot name="footer">
                <small class="text-muted">{{ footer }}</small>
            </slot>
        </div>
    </div>
</template>

<script>
    export default {
        name: "ChartHolderCard",
        props: {
            title: String,
            subtitle: String,
            update: String,
            color: { type: String, default: "primary" },
            footer: String,
            textColor: { type: String, default: "text-dark" },
            height: { type: String, default: "100%" },
            showToggle: { type: Boolean, default: false },
        },
        computed: {
            textColorClass() {
                return this.textColor;
            },
            computedHeight() {
                return this.height || "auto";
            },
        },
    };
</script>

<style scoped>
/* Contenedor principal con transición ligera */
.chart-card-hover {
    position: relative;
    transition: box-shadow 0.3s ease;
}

/* Botones flecha siempre visibles */
.toggle-buttons {
    position: absolute;
    top: 10px;
    right: 10px;
    display: flex;
    gap: 4px;
}

/* Botón individual */
.toggle-btn {
    background: transparent;
    border: none;
    padding: 4px 6px;
    border-radius: 4px;
    color: #ccc; /* tono claro inicial */
    font-size: 0.75rem;
    transition: transform 0.2s ease, color 0.2s ease, background-color 0.2s ease;
}

.toggle-btn:hover {
    background-color: rgba(0, 0, 0, 0.05);
    transform: scale(1.1);
    color: #333; /* más oscuro al pasar el ratón */
}

.toggle-btn i {
    pointer-events: none;
}
</style>
