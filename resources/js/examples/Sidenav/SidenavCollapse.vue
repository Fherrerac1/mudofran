<template>
  <a v-bind="$attrs" href="#" :data-bs-toggle="collapse ? 'collapse' : ''" :aria-controls="collapseRef"
    :aria-expanded="isExpanded" class="nav-link"
    :class="getRoute() === collapseRef ? `active bg-gradient-${color}` : ''" @click.prevent="toggleCollapse">
    <div class="text-center d-flex align-items-center justify-content-center" :class="isRTL ? ' ms-2' : 'me-2'">
      <slot name="icon"></slot>
    </div>
    <span class="nav-link-text" :class="isRTL ? ' me-1' : 'ms-1'">{{ navText }}</span>
  </a>
  <div :class="isExpanded ? 'collapse show' : 'collapse'">
    <slot name="list"></slot>
  </div>
</template>

<script>
import { mapState } from "vuex";
import { Inertia } from "@inertiajs/inertia";

export default {
  name: "SidenavCollapse",
  inheritAttrs: false, // Disable automatic attribute inheritance
  props: {
    collapseRef: {
      type: String,
      required: true
    },
    navText: {
      type: String,
      required: true
    },
    collapse: {
      type: Boolean,
      default: true
    }
  },
  data() {
    return {
      isExpanded: false
    };
  },
  methods: {
    toggleCollapse() {
      this.isExpanded = !this.isExpanded; // Toggle collapse state
      Inertia.visit(`/${this.collapseRef}`); // Navigate using Inertia
    },
    getRoute() {
      // Get the current route from Inertia's page context
      const currentPath = this.$page.url.split("/");
      return currentPath.length > 1 ? currentPath[1] : '';
    }
  },
  computed: {
    ...mapState(["isRTL", "color"])
  }
};
</script>
