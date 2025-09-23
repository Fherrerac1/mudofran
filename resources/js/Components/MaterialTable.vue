<template>
    <div class="row mt-3" :class="classProp" v-bind="$attrs">
        <div class="col-12">
            <div class="card shadow-sm border-0 mb-4">
                <!-- Cuerpo de la tarjeta -->
                <div class="card-body pt-2 px-0 pb-2">
                    <!-- Título centrado ajustado -->
                    <div class="text-center my-0 mb-custom-responsive">
                        <div class="style_color border-radius-lg py-2 px-3 d-inline-block shadow-sm">
                            <h6 class="text-white m-0 text-capitalize">{{ title }}</h6>
                        </div>
                    </div>

                    <!-- Tabla -->
                    <div class="table-responsive px-3">
                        <table class="table align-items-center mb-0 material-table">
                            <slot />
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Inicialización de DataTable -->
    <DataTable
        :pageLength="pageLength"
        :defaultOrder="defaultOrder"
        :tableId="tableId"
        :tableKey="tableKey"
        :columnDefs="columnDefs"
    />
</template>

<script>
    import DataTable from "@/Components/DataTable.vue";

    export default {
        name: "MaterialTableWrapper",
        components: {
            DataTable,
        },
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
    };
</script>

<style scoped>
.card.flex-fill {
    min-height: 100%;
    flex-grow: 1;
    display: flex;
    flex-direction: column;
}

.mb-custom-responsive {
    margin-bottom: -40px !important;
}

/* Solo en pantallas menores a 576px (móvil) */
@media (max-width: 800px) {
.mb-custom-responsive {
    margin-bottom: 0 !important;
}
}

.border-radius-lg {
    border-radius: 0.5rem;
}
</style>
