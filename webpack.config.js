const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');

module.exports = [
  {
    name: 'admin',
    entry: {
      'payment-method': './assets/admin/payment_method_entrypoint.js',
    },
    output: {
      path: path.resolve(__dirname, 'public/admin/scripts'),
      filename: '[name].js',
      publicPath: '/admin/scripts/',
    },
    module: {
      rules: [
        {
          test: /\.js$/,
          exclude: /node_modules/,
          use: {
            loader: 'babel-loader',
            options: { presets: ['@babel/preset-env'] },
          },
        },
        {
          test: /\.scss$/,
          use: [
            MiniCssExtractPlugin.loader,
            'css-loader',
            {
              loader: 'sass-loader',
              options: { implementation: require('sass') },
            },
          ],
        },
        {
          test: /\.css$/,
          use: [MiniCssExtractPlugin.loader, 'css-loader'],
        },
        {
          test: /\.(png|jpg|jpeg|gif|svg)$/,
          type: 'asset/resource',
          generator: { filename: '../images/[name][ext]' },
        },
      ],
    },
    plugins: [
      new MiniCssExtractPlugin({ filename: '../styles/[name].css' }),
    ],
    mode: process.env.NODE_ENV === 'production' ? 'production' : 'development',
  },
  {
    name: 'shop',
    entry: {
      'checkout-complete': './assets/shop/checkout_complete_entrypoint.js',
      'order-show': './assets/shop/order_show_entrypoint.js',
    },
    output: {
      path: path.resolve(__dirname, 'public/shop/scripts'),
      filename: '[name].js',
      publicPath: '/shop/scripts/',
    },
    module: {
      rules: [
        {
          test: /\.js$/,
          exclude: /node_modules/,
          use: {
            loader: 'babel-loader',
            options: { presets: ['@babel/preset-env'] },
          },
        },
        {
          test: /\.(css|scss)$/,
          use: [
            MiniCssExtractPlugin.loader,
            'css-loader',
            'sass-loader',
          ],
        },
        {
          test: /\.(png|jpg|jpeg|gif|svg|webp)$/,
          type: 'asset/resource',
          generator: { filename: '../images/[name][ext]' },
        },
      ],
    },
    plugins: [
      new MiniCssExtractPlugin({ filename: '../styles/[name].css' }),
    ],
    mode: process.env.NODE_ENV === 'production' ? 'production' : 'development',
  },
];
