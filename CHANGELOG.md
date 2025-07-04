# Changelog

All notable changes to `SyliusTpayPlugin` will be documented in this file.

## 3.0

### Changed
- **BREAKING**: Moved `templates/shop/partial/_policies.html.twig` to `templates/shop/component/policies.html.twig`
- **BREAKING**: Removed `RegulationsUrlContextProvider` class - replaced with `PoliciesComponent` Twig component
- **BREAKING**: Merged translation keys `commerce_weavers_sylius_tpay.shop.pay.regulations` and `commerce_weavers_sylius_tpay.shop.pay.policy` into single `commerce_weavers_sylius_tpay.shop.pay.legal_info` key with `%regulations_url%` and `%policy_url%` parameters

### Added
- New `PoliciesComponent` Twig component for handling legal information display
