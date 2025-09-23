<template>
  <div class="fixed-plugin">
    <a class="px-3 py-2 fixed-plugin-button text-dark position-fixed" @click="toggle">
      <i class="material-icons py-2">settings</i>
    </a>
    <div class="shadow-lg card">
      <div class="pt-3 pb-0 bg-transparent card-header">
        <div class="float-start">
          <h5 class="mt-3 mb-0">Material UI Configurator</h5>
          <p>See our dashboard options.</p>
        </div>
        <div class="mt-4 float-end" @click="toggle">
          <button class="p-0 btn btn-link text-dark fixed-plugin-close-button">
            <i class="material-icons">clear</i>
          </button>
        </div>
        <!-- End Toggle Button -->
      </div>
      <hr class="my-1 horizontal dark" />
      <div class="pt-0 card-body pt-sm-3">
        <!-- Sidebar Backgrounds -->
        <div>
          <h6 class="mb-0">Sidebar Colors</h6>
        </div>
        <a href="#" class="switch-trigger background-color">
          <div class="my-2 badge-colors" :class="isRTL ? 'text-end' : ' text-start'">
            <span class="badge filter bg-gradient-primary" data-color="primary" @click="sidebarColor('primary')"></span>
            <span class="badge filter bg-gradient-dark" data-color="dark" @click="sidebarColor('dark')"></span>
            <span class="badge filter bg-gradient-info" data-color="info" @click="sidebarColor('info')"></span>
            <span class="badge filter bg-gradient-success" data-color="success" @click="sidebarColor('success')"></span>
            <span class="badge filter style_color forms" data-color="warning" @click="sidebarColor('warning')"></span>
            <span class="badge filter bg-gradient-danger" data-color="danger" @click="sidebarColor('danger')"></span>
          </div>
        </a>
        <!-- Sidenav Type -->
        <div class="mt-3">
          <h6 class="mb-0">Sidenav Type</h6>
          <p class="text-sm">Choose between 2 different sidenav types.</p>
        </div>
        <div class="d-flex">
          <button id="btn-dark" class="px-3 mb-2 btn bg-gradient-dark"
            :class="sidebarType === 'bg-gradient-dark' ? 'active' : ''" @click="sidebar('bg-gradient-dark')">
            Dark
          </button>
          <button id="btn-transparent" class="px-3 mb-2 btn bg-gradient-dark ms-2"
            :class="sidebarType === 'bg-transparent' ? 'active' : ''" @click="sidebar('bg-transparent')">
            Transparent
          </button>
          <button id="btn-white" class="px-3 mb-2 btn bg-gradient-dark ms-2"
            :class="sidebarType === 'bg-white' ? 'active' : ''" @click="sidebar('bg-white')">
            White
          </button>
        </div>
        <p class="text-sm d-xl-none d-block mt-2">
          You can change the sidenav type just on desktop view.
        </p>

        <!-- Navbar Fixed -->
        <hr class="horizontal dark my-3" />
        <div class="mt-2 d-flex">
          <h6 class="mb-0">Light / Dark</h6>
          <div class="form-check form-switch ps-0 ms-auto my-auto">
            <input class="form-check-input mt-1 ms-auto" type="checkbox" :checked="this.$store.state.isDarkMode"
              @click="darkMode" />
          </div>
        </div>
        <hr class="horizontal dark my-sm-4" />
      </div>
    </div>
  </div>
</template>

<script>
import { mapMutations, mapState, mapActions } from "vuex";
import { activateDarkMode, deactivateDarkMode } from "@/assets/js/dark-mode";

export default {
  name: "configurator",
  props: ["toggle"],
  methods: {
    ...mapMutations(["navbarMinimize", "navbarFixed"]),
    ...mapActions(["setColor"]),

    // Save sidebar color and persist it in localStorage
    sidebarColor(color = "success") {
      this.setColor(color);
      localStorage.setItem("sidebarColor", color);  // Save color in localStorage
    },

    // Save sidebar type and persist it in localStorage
    sidebar(type) {
      this.$store.state.sidebarType = type;
      localStorage.setItem("sidebarType", type);  // Save sidebar type in localStorage
    },

    // Toggle dark mode and save the setting in localStorage
    darkMode() {
      if (this.$store.state.isDarkMode) {
        this.$store.state.isDarkMode = false;
        deactivateDarkMode();
        localStorage.setItem("isDarkMode", "false");  // Save dark mode setting
      } else {
        this.$store.state.isDarkMode = true;
        activateDarkMode();
        localStorage.setItem("isDarkMode", "true");  // Save dark mode setting
      }
    },

    // Adjust sidebar button availability on window resize
    sidenavTypeOnResize() {
      let transparent = document.querySelector("#btn-transparent");
      let white = document.querySelector("#btn-white");
      if (window.innerWidth < 1200) {
        transparent.classList.add("disabled");
        white.classList.add("disabled");
      } else {
        transparent.classList.remove("disabled");
        white.classList.remove("disabled");
      }
    },

    // Load saved configuration from localStorage or set default dark mode
    loadConfigFromLocalStorage() {
      const savedColor = localStorage.getItem("sidebarColor");
      const savedType = localStorage.getItem("sidebarType");
      const savedDarkMode = localStorage.getItem("isDarkMode");

      if (savedColor) {
        this.sidebarColor(savedColor);
      }

      if (savedType) {
        this.sidebar(savedType);
      }

      // Default to dark mode if no setting is saved
      if (savedDarkMode === "true" || savedDarkMode === null) {
        this.$store.state.isDarkMode = true;
        activateDarkMode();
      } else {
        this.$store.state.isDarkMode = false;
        deactivateDarkMode();
      }
    },
  },
  computed: {
    ...mapState(["isRTL", "sidebarType"]),
    sidenavResponsive() {
      return this.sidenavTypeOnResize;
    },
  },
  beforeMount() {
    this.$store.state.isTransparent = "bg-transparent";
    window.addEventListener("resize", this.sidenavTypeOnResize);
    window.addEventListener("load", this.sidenavTypeOnResize);

    // Load saved config when the component is mounted
    this.loadConfigFromLocalStorage();
  },
};
</script>
