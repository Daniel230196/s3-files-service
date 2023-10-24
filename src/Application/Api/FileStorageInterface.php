<?php

declare(strict_types=1);

namespace App\Application\Api;

interface FileStorageInterface
{
    public function upload(string $key, string $filepath, string $contentType, string $access): string;
}