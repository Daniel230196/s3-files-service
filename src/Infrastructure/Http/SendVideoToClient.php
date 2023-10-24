<?php

declare(strict_types=1);

namespace App\Infrastructure\Http;

use App\Application\SendFileToClientService;
use App\Infrastructure\Http\DTO\SendVideoToClientDto;
use App\Infrastructure\Http\Resolvers\Mp4Video;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;

class SendVideoToClient extends AbstractController
{
    public function __construct(
        private readonly SendFileToClientService $clientService
    ) {
    }

    #[Route(path: '/api/send-video', name: 'send_video')]
    public function execute(#[MapRequestPayload] SendVideoToClientDto $dto, #[Mp4Video] File $file): Response
    {
        try {
            $videoLink = $this->clientService->sendFileLinkByEmail(
                $dto->target,
                $dto->subject,
                $dto->text,
                $file->getPathname(),
                $file->getMimeType()
            );
            return new JsonResponse(['video_url' => $videoLink]);
        } catch (\Throwable $t) {
            throw new HttpException(400, $t->getMessage());
        }
    }
}