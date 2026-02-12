// @ts-check
import { defineConfig } from 'astro/config';
import tailwindcss from '@tailwindcss/vite';
import astroI18next from "astro-i18next";

export default defineConfig({
    vite: {
        plugins: [tailwindcss()]
    },
    integrations: [astroI18next()],
    i18n: {
        defaultLocale: "en",
        locales: ["en", "zh", "th"],
        routing: {
            prefixDefaultLocale: false
        }
    }
});
