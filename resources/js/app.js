import "../css/app.css";
import "../scss/main.scss";
import "./bootstrap";

import VueGoogleMaps from "@fawmi/vue-google-maps";
import { createInertiaApp } from "@inertiajs/vue3";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import { createApp, h } from "vue";
import { ZiggyVue } from "../../vendor/tightenco/ziggy";

const appName = import.meta.env.VITE_APP_NAME || "Laravel";

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob("./Pages/**/*.vue"),
        ),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .use(VueGoogleMaps, {
                load: {
                    key: import.meta.env.VITE_GOOGLE_MAPS_API_KEY,
                    language: "es",
                    region: "ES",
                },
            })
            .mount(el);
    },
    progress: {
        color: "#4B5563",
    },
});
