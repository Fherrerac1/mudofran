<template>
  <div class="app-container">
    <!-- Sidenav -->
    <sidenav style="z-index: 3;" :custom_class="color" :title="title" :user="user" v-if="showSidenav" class="sidenav" />

    <div class="main-content">
      <!-- Navbar -->
      <navbar :title="title" :user="user" />

      <!-- Page content will be injected here -->
      <main class="content">
        <slot></slot>
      </main>
    </div>

    <!-- Configurator -->
    <!-- <configurator :toggle="toggleConfigurator" :class="[showConfig ? 'show' : '', hideConfigButton ? 'd-none' : '']" /> -->
  </div>
</template>

<script>
import Sidenav from "./examples/Sidenav/index.vue";
import Configurator from "@/examples/Configurator.vue";
import Navbar from "@/examples/Navbars/Navbar.vue";
import { mapMutations, mapState } from "vuex";

export default {
  name: "App",
  components: {
    Sidenav,
    Configurator,
    Navbar,
  },
  props: ['title', 'user'],
  methods: {
    ...mapMutations(["toggleConfigurator", "navbarMinimize"])
  },
  computed: {
    ...mapState([
      "color",
      "isAbsolute",
      "isNavFixed",
      "navbarFixed",
      "absolute",
      "showSidenav",
      "showNavbar",
      "showConfig",
      "hideConfigButton"
    ])
  },
  mounted() {
    this.$store.state.isTransparent = "bg-transparent";

    const sidenav = document.getElementsByClassName("g-sidenav-show")[0];

    if (window.innerWidth > 1200) {
      sidenav.classList.add("g-sidenav-pinned");
    }
  }
};
</script>
