<template>
    <nav class="shadow-none navbar navbar-main navbar-expand-lg border-radius-xl" id="navbarBlur" data-scroll="true">
      <div class="px-3 py-1 container-fluid">
        <breadcrumbs :currentPage="currentRouteName" :color="color" />
        <div id="navbar">
          <ul class="navbar-nav">

            <li class="nav-item dropdown">
    <a
      class="nav-link dropdown-toggle d-flex align-items-center py-0"
      id="dropdownMenuAccount"
      href="#"
      role="button"
      data-bs-toggle="dropdown"
      aria-expanded="false"
    >
      <i class="material-icons">account_circle</i>
    </a>

    <ul
      class="dropdown-menu dropdown-menu-end"
      aria-labelledby="dropdownMenuAccount"
    >
      <!--Profile-->
      <li class="nav-item d-flex align-items-center">
        <Link
          :href="route('my.zone')"
          class="px-0 nav-item font-weight-bold lh-1 d-flex align-items-center"
        >
          <i class="material-icons ms-2">account_circle</i>
          Mi perfil
        </Link>
      </li>

      <hr
        v-if="user.rol === 'secretario' || user.rol === 'gestor' || user.rol === 'admin'"
        class="m-1"
      />

      <li
        v-if="user.rol === 'secretario' || user.rol === 'gestor' || user.rol === 'admin'"
      >
        <Link
          href="/lista_notificaciones"
          class="px-0 nav-item font-weight-bold lh-1 d-flex align-items-center"
        >
          <i class="material-icons ms-2">notifications</i>
          NOTIFICACIONES
        </Link>
      </li>

      <hr
        v-if="user.rol === 'secretario' || user.rol === 'gestor' || user.rol === 'admin'"
        class="m-1"
      />

      <li
        v-if="user.rol === 'secretario' || user.rol === 'gestor' || user.rol === 'admin'"
      >
        <Link
          href="/list/activities"
          class="px-0 nav-item font-weight-bold lh-1 d-flex align-items-center"
        >
          <i class="material-icons ms-2">summarize</i>
          ACTIVIDADES
        </Link>
      </li>
      <hr class="m-1" />

      <!--Logout-->
      <li class="nav-item d-flex align-items-center">
        <Link
          :href="route('logout')"
          class="px-0 nav-item font-weight-bold lh-1 d-flex align-items-center"
        >
          <i class="material-icons ms-2">logout</i>
          Cerrar sesión
        </Link>
      </li>
    </ul>
  </li>


            <li class="nav-item d-xl-none ps-3 d-flex align-items-center">
              <a href="#" @click="toggleSidebar" class="p-0 nav-link text-body lh-1" id="iconNavbarSidenav">
                <div class="sidenav-toggler-inner">
                  <i class="sidenav-toggler-line"></i>
                  <i class="sidenav-toggler-line"></i>
                  <i class="sidenav-toggler-line"></i>
                </div>
              </a>
            </li>

            <li class="px-3 nav-item d-flex align-items-center">
              <Notificaciones :user="user" :color="color"></Notificaciones>
            </li>

            <!-- <li class="px-3 nav-item d-flex align-items-center invisible">
              <a class="p-0 nav-link lh-1" @click="toggleConfigurator" :class="color ? color : 'text-body'">
                <i class="material-icons fixed-plugin-button-nav cursor-pointer">
                  settings
                </i>
              </a>
            </li> -->

          </ul>
        </div>
      </div>
    </nav>
    <hr class="mt-0 mb-2">

  </template>
  <script>
  import Breadcrumbs from "../Breadcrumbs.vue";
  import { mapMutations, mapState } from "vuex";
  import { Link } from '@inertiajs/vue3';
  import Notificaciones from "@/Components/notificaciones.vue";
  import Report from "@/Components/Report.vue";

  export default {
    name: "navbar",
    data() {
      return {

      };
    },
    props: ["minNav", "color", "title", "user"],
    created() {
      this.minNav;
    },
    methods: {
      ...mapMutations(["navbarMinimize", "toggleConfigurator"]),

      toggleSidebar() {
        this.navbarMinimize();
      },
    },
    components: {
      Breadcrumbs,
      Link,
      Notificaciones,
      Report
    },
    computed: {
      ...mapState(["isRTL", "isAbsolute"]),

      currentRouteName() {
        return this.title;
      },
    },
  };
  </script>
