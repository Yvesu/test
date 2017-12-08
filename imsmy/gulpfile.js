var elixir = require('laravel-elixir');

/*
 |--------------------------------------------------------------------------
 | Elixir Asset Management
 |--------------------------------------------------------------------------
 |
 | Elixir provides a clean, fluent API for defining some basic Gulp tasks
 | for your Laravel application. By default, we are compiling the Sass
 | file for our application, as well as publishing vendor resources.
 |
 */

elixir(function(mix) {
    /*mix.sass('app.scss');*/
    /*mix.styles('admin/app.css','public/css/admin/app.css');*/

    //后台 颜色文件
    /*mix.styles('themes/amethyst.css','public/css/admin/themes/amethyst.min.css');
    mix.styles('themes/city.css','public/css/admin/themes/city.min.css');
    mix.styles('themes/flat.css','public/css/admin/themes/flat.min.css');
    mix.styles('themes/modern.css','public/css/admin/themes/modern.min.css');
    mix.styles('themes/smooth.css','public/css/admin/themes/smooth.min.css');*/

    mix.version('js/admin/manage_family.js');
});
