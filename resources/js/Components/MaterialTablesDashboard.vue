<template>
    <div class="card shadow-sm border-0 flex-fill d-flex flex-column overflow-visible pb-3">
        <div
            class="w-100 d-flex flex-column flex-fill overflow-visible "
            :class="classProp"
            v-bind="$attrs"
            :key="tableKey"
        >
            <!-- TÍTULO PARA <= 1400px -->
            <div v-if="isSmallScreen" class="small-title-wrapper my-2 d-flex justify-content-center">
                <button class="btn btn-icon toggle-btn my-2 px-0" @click="$emit('toggle-prev')">
                    <i class="fa fa-chevron-left arrow-icon me-3"></i>
                </button>

                <div class="small-title-container style_color shadow-sm d-inline-flex align-items-center px-3 py-2">
                    <h6 class="m-0 text-white text-uppercase text-center">{{ title }}</h6>
                </div>

                <button class="btn btn-icon toggle-btn my-2 px-0" @click="$emit('toggle-next')">
                    <i class="fa fa-chevron-right arrow-icon ms-3"></i>
                </button>
            </div>

            <div class="table-body-wrapper flex-fill overflow-auto">
                <!-- TÍTULO DENTRO (solo si > 1400) -->
                <div v-if="!isSmallScreen" class="text-center my-0 pt-0">
                    <div class="text-center my-0 mb-custom-responsive pt-3">
                        <button class="btn btn-icon toggle-btn my-2 px-0" @click="$emit('toggle-prev')">
                            <i class="fa fa-chevron-left arrow-icon me-3"></i>
                        </button>

                        <div class="large-title-container style_color shadow-sm d-inline-flex align-items-center px-4 py-2">
                            <h6 class="m-0 text-white text-capitalize text-center">{{ title }}</h6>
                        </div>

                        <button class="btn btn-icon toggle-btn my-2 px-0" @click="$emit('toggle-next')">
                            <i class="fa fa-chevron-right arrow-icon ms-3"></i>
                        </button>
                    </div>
                </div>

                <div class="card-body px-3 pt-1 pb-0">
                    <div class="table-responsive">
                        <table
                            :id="tableId"
                            class="table align-items-center table-sm mb-0 material-table table-nowrap"
                        >
                            <slot />
                        </table>
                    </div>
                </div>
            </div>

            <!-- Footer con paginación -->
            <div>
                <DataTable
                    :pageLength="pageLength"
                    :defaultOrder="defaultOrder"
                    :tableId="tableId"
                    :tableKey="tableKey"
                    :columnDefs="columnDefs"
                />
            </div>
        </div>
    </div>
</template>

<script>
import DataTable from "@/Components/DataTable.vue";
import { ref, onMounted, onBeforeUnmount } from "vue";

export default {
    name: "MaterialTableDashboard",
    components: { DataTable },
    props: {
        title: { type: String, default: "Table" },
        classProp: { type: [String, Object, Array], default: "" },
        pageLength: { type: Number, default: 10 },
        compactTitle: { type: Boolean, default: false },
        defaultOrder: { type: Array, default: () => null },
        tableKey: { type: [String, Number], default: null },
        tableId: { type: String, default: "material-table" },
        columnDefs: { type: Array, default: () => [] },
    },
    setup() {
        const isSmallScreen = ref(window.innerWidth <= 1400);

        const handleResize = () => {
            isSmallScreen.value = window.innerWidth <= 1400;
        };

        onMounted(() => {
            window.addEventListener("resize", handleResize);
        });

        onBeforeUnmount(() => {
            window.removeEventListener("resize", handleResize);
        });

        return { isSmallScreen };
    }
};
</script>

<style scoped>
.card {
    position: relative;
}

.mb-custom-responsive {
    margin-bottom: -50px !important;
}

.table-body-wrapper {
    overflow: visible !important;
    position: relative !important;
}

.small-title-container {
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
}

.large-title-container {
    border-radius: 12px;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
}

.arrow-icon {
    color: #cccccc;
    font-size: 1rem;
    cursor: pointer;
    transition: color 0.2s ease;
}

.arrow-icon:hover {
    color: #999999;
}

.dt-button-collection {
    position: absolute !important;
    z-index: 9999 !important;
    transform-origin: top right !important;
    box-shadow: 0px 0px 3px rgba(0, 0, 0, 0.3);
}

/* El contenedor del botón ColVis debe ser relativo */
.dt-buttons {
    position: relative !important;
}
</style>
