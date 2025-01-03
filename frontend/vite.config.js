import { defineConfig } from "vite";
import path from "path";

export default defineConfig({
  base: "./",
  resolve: {
    alias: {
      "@global": path.resolve(__dirname, "src/global.scss"),
      "@assets": path.resolve(__dirname, "src/assets"),
      "@pages": path.resolve(__dirname, "src/pages"),
    },
  },

  server: {
    port: 3000, // Porta per lo sviluppo
    open: true,
  },

  build: {
    rollupOptions: {
      input: {
        index: path.resolve(__dirname, "index.html"),
        home: path.resolve(__dirname, "src/pages/home/index.html"),
        login: path.resolve(__dirname, "src/pages/login/index.html"),
        register: path.resolve(__dirname, "src/pages/register/index.html"),
        toolkit: path.resolve(__dirname, "src/pages/toolkit/index.html"),
      },
    },
    outDir: "dist", // Cartella per i file di produzione
    assetsDir: "assets",
  },
  plugins: [
    {
      name: "html-alias-resolver",
      transformIndexHtml(html) {
        // Mappa gli alias e i percorsi da sostituire
        const aliasMap = {
          "@assets": "/src/assets",
          "@pages": "/src/pages",
        };

        // Sostituisci gli alias nel contenuto HTML
        return Object.entries(aliasMap).reduce(
          (updatedHtml, [alias, path]) =>
            updatedHtml.replace(new RegExp(alias, "g"), path),
          html
        );
      },
    },
  ],
});
