import { defineConfig } from 'vite';

export default defineConfig({
  root: '.',
  server: { port: 5173 },
  build: {
    outDir: 'dist',
    emptyOutDir: true,
    assetsDir: 'assets'
  }
});
