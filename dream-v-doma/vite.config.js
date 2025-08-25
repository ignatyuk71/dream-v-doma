import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import laravel from 'laravel-vite-plugin'
import path from 'path'

export default defineConfig({
  resolve: {
    alias: {
      vue: 'vue/dist/vue.esm-bundler.js',
      '@': path.resolve(__dirname, 'resources/js'),
    },
  },
  plugins: [
    laravel({
      input: [
        'resources/js/app.js',               // фронт
        'resources/js/admin/index.js',       // ✅ адмінка js
        'resources/js/admin/index.css',       // ✅ адмінка css (в одному input)
        'resources/css/app-index.css',
        'resources/css/app.css',
      ],
      refresh: true,
    }),
    vue(),
  ],
})
