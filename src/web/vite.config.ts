import path from 'path';
import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
import tailwindcss from '@tailwindcss/vite';
import tailwindShadowDom from 'vite-plugin-tailwind-shadowdom';
import { getPluginKitReactViteDevAliases } from '@verbb/plugin-kit-react/vite-dev';

export default defineConfig(({ command }) => ({
  plugins: [react(), tailwindcss(), tailwindShadowDom()],
  resolve: {
    alias: command === 'serve' ? getPluginKitReactViteDevAliases() : [],
    dedupe: [
      'react',
      'react-dom',
      '@fortawesome/fontawesome-svg-core',
      '@fortawesome/pro-solid-svg-icons',
      '@fortawesome/react-fontawesome',
    ],
    preserveSymlinks: command === 'serve',
  },
  build: {
    outDir: path.resolve(__dirname, 'assets/cp/dist'),
    emptyOutDir: true,
    rollupOptions: {
      input: path.resolve(__dirname, 'src/main.tsx'),
      output: {
        entryFileNames: 'builder.js',
        assetFileNames: 'builder.[ext]',
      },
    },
  },
}));
