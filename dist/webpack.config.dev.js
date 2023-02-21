"use strict";

var _module$exports;

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

require("@babel/polyfill");

var path = require('path');

var VueLoaderPlugin = require('vue-loader/lib/plugin');

module.exports = (_module$exports = {
  mode: 'development',
  entry: ["@babel/polyfill", './resources/assets/js/app.js'],
  output: {
    path: path.resolve(__dirname, 'public/dist'),
    filename: 'app.bundle.js'
  }
}, _defineProperty(_module$exports, "mode", "development"), _defineProperty(_module$exports, "module", {
  rules: [{
    test: /\.styl$/,
    loader: 'stylus-loader' // compiles Styl to CSS

  }, {
    test: /\.less$/,
    loader: 'less-loader' // compiles Less to CSS

  }, {
    test: /\.m?js$/,
    exclude: /(node_modules|bower_components)/,
    use: {
      loader: 'babel-loader',
      options: {
        presets: ['@babel/preset-env']
      }
    }
  }, {
    test: /\.vue$/,
    loader: 'vue-loader'
  }, {
    test: /\.css$/i,
    use: ['style-loader', 'css-loader']
  }, {
    test: /\.s[ac]ss$/i,
    use: [// Creates `style` nodes from JS strings
    'style-loader', // Translates CSS into CommonJS
    'css-loader', // Compiles Sass to CSS
    'sass-loader']
  }, {
    test: /\.(png|jpe?g|gif)$/i,
    loader: 'file-loader',
    options: {
      name: 'img/[name].[ext]'
    }
  }]
}), _defineProperty(_module$exports, "plugins", [// make sure to include the plugin!
new VueLoaderPlugin()]), _defineProperty(_module$exports, "resolve", {
  alias: {
    vue: 'vue/dist/vue.js',
    '@': path.resolve(__dirname, 'resources/')
  }
}), _module$exports);