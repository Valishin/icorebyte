import vue from "@vitejs/plugin-vue";
import laravel from "laravel-vite-plugin";
import path from "path";
import { defineConfig } from "vite";

export default defineConfig({
    plugins: [
        laravel({
            input: "resources/js/app.js",
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    resolve: {
        alias: {
            "@scss": path.resolve(__dirname, "resources/scss"),
        },
    },
    css: {
        preprocessorOptions: {
            scss: {
                includePaths: ["resources/scss"],
                additionalData: `
@use "tokens/colors" as *;
@use "tokens/spacing" as *;
@use "tokens/grid" as *;
@use "tokens/breakpoints" as *;
@use "tokens/tipography" as *;
@use "mixins/mixins" as *;
`,
            },
        },
    },
});
