import { defineConfig } from "vite";
import react from "@vitejs/plugin-react";
import { resolve } from "path";

export default defineConfig({
  plugins: [react()],
  root: ".",
  publicDir: false,
  build: {
    outDir: "wpscalerpro/assets/js/admin",
    emptyOutDir: false,
    rollupOptions: {
      input: {
        main: resolve(__dirname, "index.html")
      },
      output: {
        entryFileNames: "main.js"
      }
    }
  },
  server: {
    open: true,
    fs: {
      allow: ["."],
    },
  }
});
