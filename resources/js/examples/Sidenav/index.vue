<template>
  <aside id="sidenav-main"
    class="sidenav style_color navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 ms-3"
    :class="`fixed-start ms-3 ${sidebarType}`">

    <!-- Make inner container flex column instead of aside -->
    <div class="d-flex flex-column h-100">

      <div class="sidenav-header">
        <i class="top-0 p-3 cursor-pointer fas fa-times text-secondary opacity-5 position-absolute end-0 d-none d-xl-none"
          aria-hidden="true" id="iconSidenav"></i>

        <div class="p-3 d-flex justify-content-center">
          <img
            :src="sidebarType === 'bg-white' ||
              (sidebarType === 'bg-transparent' && !isDarkMode)
              ? '/images/App_Logo.png'
              : '/images/logo_white.png'"
            class="w-100"
            alt="main_logo"
          />
        </div>

        <div class="d-xl-none ps-3 d-flex align-items-center">
          <a href="#" @click="toggleSidebar" class="p-0 nav-link text-body lh-1 mt-3" id="iconNavbarSidenav">
            <div class="sidenav-toggler-inner">
              <i class="sidenav-toggler-line"></i>
              <i class="sidenav-toggler-line"></i>
              <i class="sidenav-toggler-line"></i>
            </div>
          </a>
        </div>
      </div>

      <hr class="horizontal light mt-0 mb-2" />
      <sidenav-list :user="user" />

      <!-- Footer pushed to bottom -->
      <div class="sidenav-footer text-center w-100 mt-auto">
        <hr class="horizontal light mt-0 mb-2">
        <div class="mx-3">
          <BlackCatLogo />
        </div>
      </div>
    </div>
  </aside>
</template>


<script>
import SidenavList from "./SidenavList.vue";
import logo from "@/assets//img/logo-ct.png";
import logoDark from "@/assets//img/logo-ct-dark.png";
import { mapMutations, mapState } from "vuex";
import BlackCatLogo from "@/Components/BlackCatLogo.vue";

export default {
  name: "index",
  components: {
    SidenavList,
    BlackCatLogo
  },
  props: {
    user: Object
  },
  data() {
    return {
      logo,
      logoDark,
    };
  },
  computed: {
    ...mapState(["isRTL", "sidebarType", "isDarkMode"]),
  },
  methods: {
    ...mapMutations(["navbarMinimize"]),

    toggleSidebar() {
      this.navbarMinimize();
    },
  },
};
</script>
