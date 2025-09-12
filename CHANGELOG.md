# Changelog

All notable changes to `SyliusTpayPlugin` will be documented in this file.

## 3.0

### Changed
- **BREAKING**: Moved `templates/shop/partial/_policies.html.twig` to `templates/shop/component/policies.html.twig`
- **BREAKING**: Removed `RegulationsUrlContextProvider` class - replaced with `PoliciesComponent` Twig component
- **BREAKING**: Merged translation keys `commerce_weavers_sylius_tpay.shop.pay.regulations` and `commerce_weavers_sylius_tpay.shop.pay.policy` into single `commerce_weavers_sylius_tpay.shop.pay.legal_info` key with `%regulations_url%` and `%policy_url%` parameters
- **BREAKING**: This plugin no longer forces Payum encryption. To enable Payum encryption use the https://github.com/Payum/Payum/blob/master/docs/symfony/encrypt-gateway-configs-stored-in-database.md guide. If you are migrating from SyliusTpayPlugin 2.x you only need to configure the encryption key (https://github.com/Payum/Payum/blob/master/docs/symfony/encrypt-gateway-configs-stored-in-database.md#configure).
- **BREAKING**: Override test services for Tpay notifications:
    - `commerce_weavers_sylius_tpay.tpay.security.notification.verifier.signature` and `commerce_weavers_sylius_tpay.tpay.security.notification.verifier.checksum` now bound to always-valid fakes in the test environment to simplify webhook simulations.

### Added
- New `PoliciesComponent` Twig component for handling legal information display
