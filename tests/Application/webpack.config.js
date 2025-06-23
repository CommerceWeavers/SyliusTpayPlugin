const path = require('path');
const Encore = require('@symfony/webpack-encore');

const CommerceWeaversSyliusTpay = require('../../index');

const SyliusAdmin = require('@sylius-ui/admin');
const SyliusShop = require('@sylius-ui/shop');

const adminConfig = SyliusAdmin.getWebpackConfig(path.resolve(__dirname));
const shopConfig = SyliusShop.getWebpackConfig(path.resolve(__dirname));
const cwTpayShop = CommerceWeaversSyliusTpay.getWebpackShopConfig(path.resolve(__dirname));
const cwTpayAdmin = CommerceWeaversSyliusTpay.getWebpackAdminConfig(path.resolve(__dirname));

module.exports = [shopConfig, adminConfig, cwTpayShop, cwTpayAdmin];
