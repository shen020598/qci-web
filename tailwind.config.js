/** @type {import('tailwindcss').Config} */
export default {
    content: ["./src/**/*.{astro,html,js,jsx,ts,tsx}"],
    theme: {
        extend: {
            colors: {
                brand: {
                    primary: "#7DA79B",
                    secondary: "#F4A261",
                    dark: "#222222",
                    light: "#F5F5F5",
                    accent: "#E76F51",
                },
            },
            maxWidth: {
                '8xl': '88rem',  // 1408px
                '9xl': '96rem',  // 1536px
            }
        },
    },
    plugins: [],
};