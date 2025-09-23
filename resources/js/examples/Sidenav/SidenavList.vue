<template>
    <div class="w-100 h-auto collapse navbar-collapse overflow-y-auto overflow-x-hidden d-flex flex-column"
        id="sidenav-collapse-main">
        <ul class="navbar-nav">
            <li v-for="(link, index) in filteredSidebarLinks" :key="index" class="nav-item my-0"
                :class="[{ dropdown: link.subLinks }]">
                <a :href="link.url" class="nav-link" style="font-size: 0.8em;"
                    :class="[link.subLinks ? 'dropdown-toggle' : '', getRoute() === link.url ? `active unique_bg` : '']"
                    :data-bs-toggle="link.subLinks ? 'dropdown' : ''" :aria-expanded="link.subLinks ? 'false' : ''">
                    <i :class="link.icon + ' opacity-10 fs-5 me-1'"></i>
                    {{ link.title }}
                </a>

                <div v-if="link.subLinks" class="dropdown-menu p-0" style="font-size: 0.9em;">
                    <a v-for="(subLink, subIndex) in link.subLinks" :key="subIndex" :href="subLink.url"
                        class="dropdown-item">
                        {{ subLink.title }}
                    </a>
                </div>
            </li>

            <li class="nav-item" v-if="user.rol !== 'admin' && user.rol !== 'super_admin'">
                <hr class="horizontal light">
                <div>
                    <TimeRecord />
                </div>
            </li>
        </ul>
    </div>
</template>

<script>
import SidenavCollapse from "./SidenavCollapse.vue";
import TimeRecord from "@/Pages/Profile/sections/TimeRecord.vue";
import { mapState } from "vuex";

export default {
    name: "SidenavList",
    props: {
        user: Object
    },
    data() {
        return {
            sidebarLinks: [
                {
                    title: "Dashboard",
                    url: "/dashboard",
                    icon: "fas fa-chart-line",
                    rol: ['admin', 'gestor']
                },
                {
                    title: "Dashboard",
                    url: "/tenants_dashboard",
                    icon: "fas fa-chart-line",
                    rol: ['super_admin']
                },
                {
                    title: "ListaTenants",
                    url: "/tenants_list",
                    icon: "fas fa-user-friends",
                    rol: ['super_admin']
                },
                {
                    title: "Clientes",
                    url: "#Clientes",
                    icon: "fas fa-user-tie",
                    rol: ['admin', 'gestor'],
                    subLinks: [
                        { title: "Clientes", url: "/list_clientes" },
                        { title: "Contactos", url: "/list_contactos" },
                    ]
                },
                {
                    title: "Presupuestos",
                    url: "/list_presupuestos",
                    icon: "fas fa-hand-holding-usd",
                    rol: ['admin', 'gestor'],
                },
                {
                    title: "Facturas",
                    url: "#Facturas",
                    icon: "fa-solid fa-file-invoice",
                    rol: ['admin', 'gestor'],
                    subLinks: [
                        { title: "Facturas", url: "/list_facturas" },
                        { title: "Facturas Recurrentes", url: "/lista_recurrentes" },
                        { title: "Borradores", url: "/facturas_borradores" },
                    ]
                },
                {
                    title: "Servicios & Productos",
                    url: "/lista_servicios",
                    icon: "fa-solid fa-shop",
                    rol: ['admin', 'gestor'],
                },
                {
                    title: "RRHH",
                    url: "#rr_hh",
                    icon: "fa-solid fa-person-chalkboard",
                    rol: ['admin', 'gestor'],
                    subLinks: [
                        { title: "Nominas", url: "/nominas" },
                        // { title: "Documentos y Certificados", url: "/documentos" },
                        { title: "Vacaciones", url: "/time-off" },
                        // { title: "Ausencias y Ahoras Extra", url: "/" },
                        // { title: "Bajas", url: "/" },
                    ]
                },
                {
                    title: "RRHH",
                    url: "#rr_hh",
                    icon: "fa-solid fa-person-chalkboard",
                    rol: ['tecnico'],
                    subLinks: [
                        { title: "Nominas", url: "/nominas" },
                    ]
                },
                {
                    title: "Control Horario",
                    url: "/list_horarios",
                    icon: "fa-solid fa-calendar-day",
                    rol: ['admin', 'gestor'],
                },
                {
                    title: "Usuarios",
                    url: "/list_users",
                    icon: "fa-solid fa-users",
                    rol: ['admin', 'gestor'],
                },
            ]
        };
    },
    components: {
        SidenavCollapse,
        TimeRecord
    },
    mounted() {
    },
    methods: {
        getRoute() {
            // Get the current route from Inertia's page context
            const currentPath = this.$page.url;
            return currentPath.length > 1 ? currentPath : '';
        }
    },
    computed: {
        ...mapState(["color"]),
        filteredSidebarLinks() {
            return this.sidebarLinks.filter(link => {
                // Show link if no role restriction or exact match with user role
                return !link.rol || link.rol.includes(this.user.rol);
            });
        }

    }
};
</script>

<style scoped>
#sidenav-collapse-main {
    scrollbar-width: thin;
    scrollbar-color: #dadada #253249;
}

#sidenav-collapse-main::-webkit-scrollbar {
    width: 8px;
}

#sidenav-collapse-main::-webkit-scrollbar-track {
    background-color: #253249 !important;
}

#sidenav-collapse-main::-webkit-scrollbar-thumb {
    background-color: #dadada;
    border-radius: 4px;
    transition: background-color 0.3s ease;
}

#sidenav-collapse-main::-webkit-scrollbar-thumb:hover {
    background-color: #dadada;
}

.dropdown-item:hover {
    border-radius: 10px !important;
}
</style>
