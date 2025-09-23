import "./bootstrap";
import "../css/app.css";
import "bootstrap/dist/css/bootstrap.min.css";
import "bootstrap/dist/js/bootstrap.bundle.min.js";
import "@fortawesome/fontawesome-free/css/all.css";
import "@fortawesome/fontawesome-free/js/all.js";
import "@/assets/scss/material-dashboard.scss";
import "@/assets/css/nucleo-icons.css";
import "@/assets/css/nucleo-svg.css";
import "material-design-icons-iconfont/dist/material-design-icons.css";
import "material-icons/iconfont/material-icons.css";

import "@/assets/scss/app.scss";

import { createApp, h } from "vue";
import { createInertiaApp } from "@inertiajs/vue3";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import { ZiggyVue } from "../../vendor/tightenco/ziggy";

// Import the store
import store from "./store";
import materialDashboard from "./material-dashboard";

const appName = import.meta.env.VITE_APP_NAME || "Laravel";

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob("./Pages/**/*.vue")
        ),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(store)
            .use(ZiggyVue)
            .use(materialDashboard)
            .mount(el);
    },
    progress: {
        color: "#4B5563",
    },
});
