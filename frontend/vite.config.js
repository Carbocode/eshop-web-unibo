import { defineConfig } from 'vite';

export default defineConfig({
  server: {
    port: 3000, // Porta per lo sviluppo
  },
  build: {
    outDir: 'dist', // Cartella per i file di produzione
  },
});
