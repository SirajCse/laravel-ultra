import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import { resolve } from 'path';

export default defineConfig({
    plugins: [vue()],
    resolve: {
        alias: {
            '@': resolve('./resources/js'),
        },
    },
    build: {
        rollupOptions: {
            input: {
                ultra: resolve(__dirname, 'resources/js/ultra.js')
            },
            output: {
                entryFileNames: 'ultra-[name].js',
                chunkFileNames: 'ultra-[name].js',
                assetFileNames: 'ultra-[name].[ext]'
            }
        }
    }
});