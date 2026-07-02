import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import vue from "@vitejs/plugin-vue";
import viteCompression from "vite-plugin-compression";

export default defineConfig({
    plugins: [
        laravel({
            input: "resources/js/app.js",
            refresh: true,
            build: {
                outputDir: "public/frontend/build",
                manifest: "manifest.json",
                rollupOptions: {
                    output: {
                        // Manual chunks untuk optimasi
                        manualChunks: {
                            vendor: ["jquery", "bootstrap"],
                            ui: ["slick-carousel", "magnific-popup"],
                        },
                    },
                },
            },
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        // Compression plugin untuk gzip/brotli
        viteCompression({
            algorithm: "gzip",
            ext: ".gz",
        }),
    ],
    // Konfigurasi optimasi build
    build: {
        // Minify menggunakan esbuild (lebih cepat)
        minify: "esbuild",
        // Mengaktifkan source maps hanya untuk development
        sourcemap: false,
        // Chunk size warning limit
        chunkSizeWarningLimit: 1000,
        // Rollup options untuk optimasi
        rollupOptions: {
            output: {
                // Nama file dengan hash untuk cache busting
                entryFileNames: "js/[name].[hash].js",
                chunkFileNames: "js/[name].[hash].js",
                assetFileNames: (assetInfo) => {
                    if (assetInfo.name.endsWith(".css")) {
                        return "css/[name].[hash][extname]";
                    }
                    return "assets/[name].[hash][extname]";
                },
            },
        },
    },
    // Optimasi resolve
    resolve: {
        alias: {
            "@": "/resources/js",
        },
    },
});
