/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

import { defineConfig } from 'vite';
import { resolve } from 'path';

export default defineConfig({
  build: {
    lib: {
      entry: resolve(__dirname, 'src/index.ts'),
      name: 'ClearWidget',
      fileName: 'index',
      formats: ['iife']
    },
    outDir: 'dist',
    sourcemap: true,
    minify: false,
    rollupOptions: {
      output: {
        extend: true
      }
    }
  }
});
