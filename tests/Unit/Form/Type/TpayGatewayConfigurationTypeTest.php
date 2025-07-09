<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Unit\Form\Type;

use CommerceWeavers\SyliusTpayPlugin\Form\Type\TpayGatewayConfigurationType;
use CommerceWeavers\SyliusTpayPlugin\Tpay\PaymentType;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validation;

final class TpayGatewayConfigurationTypeTest extends TypeTestCase
{
    use ProphecyTrait;

    protected function setUp(): void
    {

        parent::setUp();
    }

    protected function getExtensions(): array
    {
        $formType = new TpayGatewayConfigurationType(
        );

        $validator = Validation::createValidator();

        return [
            new PreloadedExtension([$formType], []),
            new ValidatorExtension($validator),
        ];
    }

    public function test_build_form(): void
    {
        $form = $this->factory->create(TpayGatewayConfigurationType::class);

        $this->assertTrue($form->has('client_id'));
        $this->assertTrue($form->has('client_secret'));
        $this->assertTrue($form->has('cards_api'));
        $this->assertTrue($form->has('type'));
        $this->assertTrue($form->has('merchant_id'));
        $this->assertTrue($form->has('notification_security_code'));
        $this->assertTrue($form->has('google_merchant_id'));
        $this->assertTrue($form->has('apple_pay_merchant_id'));
        $this->assertTrue($form->has('production_mode'));

        $clientIdField = $form->get('client_id');
        $this->assertSame(TextType::class, $clientIdField->getConfig()->getType()->getInnerType()::class);
        $this->assertEquals([new NotBlank(['allowNull' => false, 'groups' => ['sylius']])], $clientIdField->getConfig()->getOption('constraints'));

        $clientIdField = $form->get('client_secret');
        $this->assertSame(TextType::class, $clientIdField->getConfig()->getType()->getInnerType()::class);
        $this->assertEquals([new NotBlank(['allowNull' => false, 'groups' => ['sylius']])], $clientIdField->getConfig()->getOption('constraints'));

        $typeField = $form->get('type');
        $this->assertSame(ChoiceType::class, $typeField->getConfig()->getType()->getInnerType()::class);
        $this->assertSame([
            'commerce_weavers_sylius_tpay.admin.gateway_configuration.type.redirect' => PaymentType::REDIRECT,
            'commerce_weavers_sylius_tpay.admin.gateway_configuration.type.card' => PaymentType::CARD,
            'commerce_weavers_sylius_tpay.admin.gateway_configuration.type.blik' => PaymentType::BLIK,
            'commerce_weavers_sylius_tpay.admin.gateway_configuration.type.pay_by_link' => PaymentType::PAY_BY_LINK,
            'commerce_weavers_sylius_tpay.admin.gateway_configuration.type.google_pay' => PaymentType::GOOGLE_PAY,
            'commerce_weavers_sylius_tpay.admin.gateway_configuration.type.apple_pay' => PaymentType::APPLE_PAY,
            'commerce_weavers_sylius_tpay.admin.gateway_configuration.type.visa_mobile' => PaymentType::VISA_MOBILE,
        ], $typeField->getConfig()->getOption('choices'));

        $productionModeField = $form->get('production_mode');
        $this->assertSame(ChoiceType::class, $productionModeField->getConfig()->getType()->getInnerType()::class);
        $this->assertSame([
            'sylius.ui.yes_label' => true,
            'sylius.ui.no_label' => false,
        ], $productionModeField->getConfig()->getOption('choices'));
    }

    public function test_configure_options(): void
    {
        $resolver = new OptionsResolver();
        $formType = new TpayGatewayConfigurationType();
        $formType->configureOptions($resolver);

        $options = $resolver->resolve();
        $this->assertSame(['sylius'], $options['validation_groups']);
    }

}
