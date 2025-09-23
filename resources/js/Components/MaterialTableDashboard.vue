<template>
    <div class="card shadow-sm border-0 flex-fill d-flex flex-column overflow-hidden mb-4">
        <div
            class="w-100 d-flex flex-column flex-fill overflow-hidden"
            :class="classProp"
            v-bind="$attrs"
            :key="tableKey"
        >
            <div class="table-body-wrapper flex-fill overflow-auto">
                <div class="text-center my-0 mb-custom-responsive pt-3">
                    <div class="style_color border-radius-lg py-2 px-3 d-inline-block shadow-sm">
                        <h6 class="text-white m-0 text-capitalize">{{ title }}</h6>
                    </div>
                </div>

                <div class="card-body px-3 py-2">
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
                    :title="title"
                />
            </div>
        </div>
    </div>
</template>

<script>
    import DataTable from "@/Components/DataTable.vue";

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
        },
    };
</script>

<style scoped>
.mb-custom-responsive {
    margin-bottom: -40px !important;
}

/* Solo en pantallas menores a 576px (móvil) */
@media (max-width: 800px) {
.mb-custom-responsive {
    margin-bottom: 0 !important;
    }
}

.table-body-wrapper {
    min-height: 0;
    max-height: calc(100vh - 300px);
    overflow-y: auto;
    padding-bottom: 1rem;
}
</style>
