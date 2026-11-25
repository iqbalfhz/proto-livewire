import {
    defineConfig
} from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny, local } from 'laravel-vite-plugin/fonts';
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
            fonts: [
                // Body & UI
                bunny('Instrument Sans', {
                    weights: [400, 500, 600],
                }),
                // Display: self-hosted so the optical-size axis survives (Bunny only serves fixed instances)
                local('Newsreader', {
                    variants: [
                        { src: 'resources/fonts/Newsreader-Opsz.woff2', weight: 400, style: 'normal' },
                        { src: 'resources/fonts/Newsreader-Italic-Opsz.woff2', weight: 400, style: 'italic' },
                    ],
                    preload: [{ weight: 400, style: 'normal' }],
                }),
                // Labels, metadata, indices — the letterpress "type case"
                bunny('JetBrains Mono', {
                    weights: [400, 500],
                }),
            ],
        }),
        tailwindcss(),
    ],
    server: {
        cors: true,
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
