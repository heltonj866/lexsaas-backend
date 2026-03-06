/** @type {import('tailwindcss').Config} */
export default {
  darkMode: 'class', // <-- ISTO É O QUE FORÇA A OBEDECER AOS BOTÕES!
  content: [
    './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
    './storage/framework/views/*.php',
    './resources/views/**/*.blade.php',
    './resources/js/**/*.{js,ts,jsx,tsx}',
  ],
  theme: {
    extend: {},
  },
  plugins: [],
}