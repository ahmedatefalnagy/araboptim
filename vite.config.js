import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import react from '@vitejs/plugin-react'

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/js/app.jsx'],
            refresh: true,
        }),
        react(),
    ],

    server: {
        host: '0.0.0.0',   // مهم جدًا
        port: 5173,
        strictPort: true,

        hmr: {
            host: '192.168.8.137', // 👈 ضع IP جهازك الحقيقي
            protocol: 'ws'
        }
    }
})