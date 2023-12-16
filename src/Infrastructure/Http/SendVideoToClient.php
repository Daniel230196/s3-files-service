<?php

declare(strict_types=1);

namespace App\Infrastructure\Http;

use App\Application\ClientFilesService;
use App\Infrastructure\Http\DTO\SendVideoToClientDto;
use App\Infrastructure\Http\Resolvers\Mp4Video;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class SendVideoToClient extends AbstractController
{
    public function __construct(
        private readonly ClientFilesService $clientService,
        private readonly ContainerBagInterface $bag
    ) {
    }

    #[Route(path: '/api/send-video', name: 'send_video', methods: 'POST')]
    public function execute(
        #[MapRequestPayload] SendVideoToClientDto $dto,
        #[Mp4Video] File $file,
        Request $request
    ): Response {
        $this->ensureHasAccess($request);

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

    private function ensureHasAccess(Request $request): void {
        $token = $request->headers->get('X-Secure-Token');
        if ($token !== null && $token === $this->bag->get('security')['token']) {
            return;
        }

        throw new AccessDeniedException('Доступ запрещен');
    }
}