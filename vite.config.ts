import path from 'node:path';
import { defineConfig, loadEnv } from 'vite';
import react from '@vitejs/plugin-react';
import tailwindcss from '@tailwindcss/vite';
import tailwindShadowDom from 'vite-plugin-tailwind-shadowdom';

const webRoot = path.resolve(__dirname, 'src/web');

const parseServerPort = (value: string | undefined, fallback: number): number => {
  const port = Number.parseInt(value || '', 10);

  return Number.isInteger(port) ? port : fallback;
};

export default defineConfig(({ mode }) => {
  const env = loadEnv(mode, __dirname, '');
  const devServerPublicUrl = (env.NAVIGATION_CP_DEV_SERVER_PUBLIC || 'http://localhost:4011/').replace(/\/$/, '');
  const devServerHost = env.NAVIGATION_CP_DEV_SERVER_HOST || 'localhost';
  const devServerPort = parseServerPort(env.NAVIGATION_CP_DEV_SERVER_PORT, 4011);
  const hmrProtocol = env.NAVIGATION_CP_DEV_SERVER_HMR_PROTOCOL || 'ws';

  return {
    root: webRoot,
    // CP bundles publish under Craft cpresources; relative base keeps lazy chunks beside entries.
    base: '',
    plugins: [react(), tailwindcss(), tailwindShadowDom()],
    resolve: {
      dedupe: [
        'react',
        'react-dom',
        '@lit/react',
        '@lit/reactive-element',
        'lit',
        'lit-element',
        'lit-html',
        // One registry module so registerIcon() is visible to <pk-icon>/getIcon().
        '@verbb/plugin-kit-icons',
      ],
    },
    // Optional plugin-local HMR — Craft must set NAVIGATION_USE_VITE_DEV_SERVER=true.
    server: {
      origin: devServerPublicUrl,
      host: devServerHost,
      port: devServerPort,
      strictPort: true,
      cors: true,
      hmr: {
        protocol: hmrProtocol,
      },
    },
    build: {
      outDir: path.resolve(webRoot, 'assets/cp/dist'),
      emptyOutDir: true,
      manifest: 'manifest.json',
      sourcemap: true,
      // Target modern browsers (optional — no longer required for top-level await).
      target: 'es2022',
      rollupOptions: {
        input: {
          builder: path.resolve(webRoot, 'src/main.tsx'),
        },
      },
    },
  };
});
