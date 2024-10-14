const path = require('path');
const Encore = require('@symfony/webpack-encore');

class CommerceWeaversSyliusTpay {
  static getWebpackShopConfig(rootDir) {
    Encore
      .setOutputPath('public/build/commerce-weavers/tpay/shop')
      .setPublicPath('/build/commerce-weavers/tpay/shop')
      .addEntry('commerce-weavers-tpay-sylius-shop-checkout-complete-entry', path.resolve(__dirname, 'assets/shop/checkout_complete_entrypoint.js'))
      .addEntry('commerce-weavers-tpay-sylius-shop-order-show-entry', path.resolve(__dirname, 'assets/shop/order_show_entrypoint.js'))
      .disableSingleRuntimeChunk()
      .cleanupOutputBeforeBuild()
      .enableSourceMaps(!Encore.isProduction())
      .enableVersioning(Encore.isProduction())
      .enableSassLoader((options) => {
        // eslint-disable-next-line no-param-reassign
        options.additionalData = `$rootDir: ${rootDir};`;
      })

    const shopConfig = Encore.getWebpackConfig();

    shopConfig.name = 'commerce_weavers_sylius_tpay_shop';

    Encore.reset();

    return shopConfig;
  }
}

module.exports = CommerceWeaversSyliusTpay;
