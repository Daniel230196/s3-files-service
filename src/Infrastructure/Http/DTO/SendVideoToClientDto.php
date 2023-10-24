<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class SendVideoToClientDto
{
    #[Assert\NotBlank()]
    #[Assert\Email()]
    public string $target;

    #[Assert\NotBlank()]
    #[Assert\Length(min: 5, max: 255)]
    public string $subject;

    #[Assert\NotBlank()]
    #[Assert\Length(min: 5, max: 15000)]
    public string $text;
}