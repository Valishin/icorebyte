import "../css/app.css";
import "../scss/main.scss";
import "./bootstrap";

// Chat widget — lazy, after page load, not on admin panel, only when enabled
const CHAT_ENABLED = import.meta.env.VITE_CHAT_ENABLED !== 'false'
if (CHAT_ENABLED && !window.location.pathname.startsWith('/admin')) {
  const idle = window.requestIdleCallback ?? ((cb) => setTimeout(cb, 2000))
  idle(() => {
    Promise.all([
      import('./chat/ChatFab.vue'),
      import('vue'),
    ]).then(([{ default: ChatFab }, { createApp }]) => {
      const el = document.createElement('div')
      el.id = 'chat-widget-root'
      document.body.appendChild(el)
      createApp(ChatFab).mount('#chat-widget-root')
    }).catch(() => { /* noop — widget is non-critical */ })
  })
}

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
