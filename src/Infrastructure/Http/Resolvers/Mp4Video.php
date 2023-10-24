<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Resolvers;

use Symfony\Component\HttpKernel\Attribute\ValueResolver;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

#[\Attribute(\Attribute::TARGET_PARAMETER)]
class Mp4Video extends ValueResolver
{
    public ArgumentMetadata $metadata;

    public function __construct(string $resolver = Mp4VideoResolver::class, bool $disabled = false)
    {
        parent::__construct($resolver, $disabled);
    }
}
