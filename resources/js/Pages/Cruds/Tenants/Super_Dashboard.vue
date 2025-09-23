<script>
import MaterialButton from "@/Components/MaterialButton.vue";
import MaterialSelect from "@/Components/MaterialSelect.vue";
import MaterialTable from "@/Components/MaterialTableGeneral.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Swal from "sweetalert2";

export default {
    components: {
        MaterialButton,
        AuthenticatedLayout,
        MaterialTable,
        MaterialSelect
    },
    props: {
        tenants: { type: Array, required: true },
        user: { type: Object, required: true },
    },
    data() {
        return {
            selectedTenant: null, // <-- store tenant selection
        };
    },
    computed: {
        filteredTenants() {
            if (!this.selectedTenant) {
                return this.tenants; // show all if nothing selected
            }
            return this.tenants.filter(t => t.id === this.selectedTenant);
        }
    },
    methods: {
        formatDate(dateString) {
            const options = { year: "numeric", month: "long", day: "numeric" };
            return new Date(dateString).toLocaleDateString("es-ES", options);
        },
    },
};
</script>

<template>
    <AuthenticatedLayout :user="user" title="Dashboard">
        <div class="container-fluid overflow-visible">
            <div class="row" id="nav">
                <a class="col d-flex text-decoration-none fs-5" href="javascript:history.go(-1);" role="button">
                    <i class="material-icons pe-2 pt-1">
                        <span class="material-symbols-outlined">arrow_back</span>
                    </i>Volver
                </a>
            </div>

            <!-- tenants content -->
            <h5 class="text-center">Dashboard</h5>

            <!-- tenant filter -->
            <div class="row mb-4">
                <div class="col-lg-4">
                    <MaterialSelect name="tenant" id="tenant" v-model="selectedTenant" :is-required="false"
                        :options="tenants.map(t => ({ text: t.slug, value: t.id }))" placeholder="Filtrar por Tenant" />
                </div>
            </div>

            <!-- tenant stats -->
            <div class="row">
                <div v-for="tenant in filteredTenants" :key="tenant.id" class="col-md-4 mb-3">
                    <div class="card shadow-sm p-3">
                        <h6 class="fw-bold">{{ tenant.slug }}</h6>
                        <p><strong>Presupuestos:</strong> {{ tenant.presupuestos?.length }}</p>
                        <p><strong>Facturas:</strong> {{ tenant.facturas?.length }}</p>
                        <p><strong>Activities:</strong> {{ tenant.activities?.length }}</p>
                        <p><strong>Usuarios:</strong> {{ tenant.users?.length }}</p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
