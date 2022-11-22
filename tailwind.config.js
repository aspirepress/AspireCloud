/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ["./templates/**/*.php"],
  theme: {
    extend: {},
  },
  plugins: [
    require('@tailwindcss/forms'),
  ],
}
