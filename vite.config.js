import { defineConfig } from "vite";
import react from "@vitejs/plugin-react";
import { resolve } from "path";

export default defineConfig({
  plugins: [react()],
  build: {
    outDir: "wpscalerpro/assets/js/admin",
    emptyOutDir: false,
    rollupOptions: {
      input: {
        main: resolve(__dirname, "wpscalerpro/assets/js/admin/main.js")
      },
      output: {
        entryFileNames: "main.js"
      }
    }
  }
});
