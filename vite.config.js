import { defineConfig } from "vite";
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
  plugins: [tailwindcss()],
  publicDir: false,
  server: {
    host: "0.0.0.0",
    port: 5173,
    strictPort: true,
    cors: {
      origin: [
        "http://localhost:8080",
        "http://bonehacker-ci4.test",
        "http://localhost",
      ],
      credentials: true,
    },
    hmr: {
      host: "localhost",
    },
  },
  build: {
    outDir: "public/build",
    rollupOptions: {
      input: "resources/js/app.js",
      output: {
        entryFileNames: "assets/[name].js",
        assetFileNames: "assets/[name][extname]",
      },
    },
  },
});
