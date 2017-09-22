const { mix } = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.js('resources/assets/js/app.js', 'public/js')
   .sass('resources/assets/sass/app.scss', 'public/css')
   .sass('resources/assets/theme-coco/sass/style-coco.scss', 'public/css')
   .sass('resources/assets/theme-coco/sass/style-responsive-coco.scss', 'public/css')
   .sass('resources/assets/sass/theme-customization.scss', 'public/css')
   ;

mix.scripts([
    'resources/assets/theme-coco/js/libs/fastclick.js',
    'resources/assets/theme-coco/js/init.js'
], 'public/js/theme.js');
