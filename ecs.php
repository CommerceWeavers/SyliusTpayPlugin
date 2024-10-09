<?php

declare(strict_types=1);

use SlevomatCodingStandard\Sniffs\Commenting\InlineDocCommentDeclarationSniff;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return static function (ECSConfig $ecsConfig): void {
    $ecsConfig->paths([
        __DIR__ . '/src',
        __DIR__ . '/ecs.php',
    ]);

    $ecsConfig->skip([
        InlineDocCommentDeclarationSniff::class . '.MissingVariable',
    ]);

    $ecsConfig->import('vendor/sylius-labs/coding-standard/ecs.php');
};
