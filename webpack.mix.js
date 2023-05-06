let mix = require('laravel-mix');

// Add JS mixes here.
//mix.js("resources/js/app.js", "public/js")

mix.postCss("./assets/source/style.css", "./assets/output/style.css", [
        require("tailwindcss"),
    ]);