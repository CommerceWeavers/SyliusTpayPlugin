const path = require('path');
const Encore = require('@symfony/webpack-encore');

const SyliusAdmin = require('@sylius-ui/admin');
const SyliusShop = require('@sylius-ui/shop');

const adminConfig = SyliusAdmin.getWebpackConfig(path.resolve(__dirname));
const shopConfig = SyliusShop.getWebpackConfig(path.resolve(__dirname));

Encore.reset();

// App admin config
Encore
  .setOutputPath('public/build/app/admin')
  .setPublicPath('/build/app/admin')
  .addEntry('app-admin-entry', './assets/admin/entrypoint.js')
  .disableSingleRuntimeChunk()
  .cleanupOutputBeforeBuild()
  .enableSourceMaps(!Encore.isProduction())
  .enableVersioning(Encore.isProduction())
  .enableSassLoader();

const appAdminConfig = Encore.getWebpackConfig();

appAdminConfig.externals = Object.assign({}, appAdminConfig.externals, { window: 'window', document: 'document' });
appAdminConfig.name = 'app.admin';

module.exports = [appAdminConfig, shopConfig, adminConfig];
