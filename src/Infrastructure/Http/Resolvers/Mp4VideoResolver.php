<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Resolvers;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class Mp4VideoResolver implements ValueResolverInterface
{

    public function __construct(
        private readonly ValidatorInterface $validator
    )
    {
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $video = $request->files->get($argument->getName());
        $this->validator->validate(
            $video,
            $this->getConstraints($argument)
        );

        return [$video];
    }

    private function getConstraints(ArgumentMetadata $argument): array
    {
        $constraints = [
            new Assert\File(
                maxSize: '100M',
                extensions: ['mp4'],
                extensionsMessage: 'Ожидается видео в формате mp4'),
        ];

        if (!$argument->isNullable()) {
            $constraints[] = new Assert\NotBlank();
        }

        return $constraints;
    }
}