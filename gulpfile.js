var elixir = require('laravel-elixir');

require('laravel-elixir-vueify-2.0');
// require('laravel-elixir-webpack');

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

elixir(function (mix) {
    // mix.webpack(['app.js'], 'public/js/appTest.js')
        mix.browserify(['app.js'], 'public/js/app.js')
        .browserify(['./node_modules/icheck/icheck.js'], 'public/js/icheck.js')
        .copy('node_modules/icheck/skins', 'public/css/icheck/skins')
        .sass('app.scss')
        .version(['public/css/app.css', 'public/js/app.js', 'public/js/icheck.js', 'public/css/icheck/skins/square/yellow.css']);
});

