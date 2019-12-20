const mix = require('laravel-mix');

mix.setPublicPath('./dist/');

mix.js( __dirname + '/resources/assets/js/app.js', 'dist/js/announcements.js')
    .sass( __dirname + '/resources/assets/sass/app.scss', 'dist/css/announcements.css');

if (mix.inProduction()) {
    mix.version();
}
