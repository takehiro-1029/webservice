const mix = require('laravel-mix');

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

mix.js('resources/js/app.js', 'public/js')
  .sass('resources/sass/style.scss', '../resources/assets/build/css/')
  // .sass('resources/sass/Foundation/base.scss', '../resources/assets/build/css/')
  // .sass('resources/sass/Foundation/reset.scss', '../resources/assets/build/css/')
  // .sass('resources/sass/Layout/l-header.scss', '../resources/assets/build/css/')
  // .sass('resources/sass/Layout/l-footer.scss', '../resources/assets/build/css/')
  // .sass('resources/sass/Layout/l-main.scss', '../resources/assets/build/css/')
  // .sass('resources/sass/Layout/l-nav.scss', '../resources/assets/build/css/')
  // .sass('resources/sass/Layout/l-wrapper.scss', '../resources/assets/build/css/')
  // .sass('resources/sass/Object/Project/p-error.scss', '../resources/assets/build/css/')
  // .sass('resources/sass/Object/Project/p-header.scss', '../resources/assets/build/css/')
  // .sass('resources/sass/Object/Project/p-login.scss', '../resources/assets/build/css/')
  // .sass('resources/sass/Object/Project/p-nav.scss', '../resources/assets/build/css/')
  // .sass('resources/sass/Object/Project/p-news.scss', '../resources/assets/build/css/')
  // .sass('resources/sass/Object/Project/p-rank.scss', '../resources/assets/build/css/')
  // .sass('resources/sass/Object/Project/p-twitter.scss', '../resources/assets/build/css/')
  // .sass('resources/sass/Object/Project/p-wrapper.scss', '../resources/assets/build/css/')
  // .sass('resources/sass/Object/Component/c-form.scss', '../resources/assets/build/css/')
  // .sass('resources/sass/Object/Component/c-btn.scss', '../resources/assets/build/css/')
  // .sass('resources/sass/Object/Component/c-error.scss', '../resources/assets/build/css/')
  // .sass('resources/sass/Object/Component/c-autofollow.scss', '../resources/assets/build/css/')
  // .sass('resources/sass/Object/Component/c-ball.scss', '../resources/assets/build/css/')
  // buildディレクトリに出力したcssファイルを、app.cssというファイルに１つにまとめてpublicディレクトリへ出力する
  .styles(
    [
      'resources/assets/build/css/style.css',
      // 'resources/assets/build/css/base.css',
      // 'resources/assets/build/css/reset.css',
      // 'resources/assets/build/css/l-header.css',
      // 'resources/assets/build/css/l-footer.css',
      // 'resources/assets/build/css/l-main.css',
      // 'resources/assets/build/css/l-nav.css',
      // 'resources/assets/build/css/l-wrapper.css',
      // 'resources/assets/build/css/p-error.css',
      // 'resources/assets/build/css/p-header.css',
      // 'resources/assets/build/css/p-login.css',
      // 'resources/assets/build/css/p-nav.css',
      // 'resources/assets/build/css/p-news.css',
      // 'resources/assets/build/css/p-rank.css',
      // 'resources/assets/build/css/p-twitter.css',
      // 'resources/assets/build/css/p-wrapper.css',
      // 'resources/assets/build/css/c-form.css',
      // 'resources/assets/build/css/c-btn.css',
      // 'resources/assets/build/css/c-error.css',
      // 'resources/assets/build/css/c-autofollow.css',
      // 'resources/assets/build/css/c-ball.css',
    ],
    'public/css/app.css'
  );

//    .sass('resources/sass/app.scss', 'public/css');
