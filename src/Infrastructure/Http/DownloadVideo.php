<?php

declare(strict_types=1);

namespace App\Infrastructure\Http;

use App\Application\ClientFilesService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;

class DownloadVideo extends AbstractController
{
    public function __construct(
        private readonly ClientFilesService $clientService
    ) {
    }

    #[Route(path: '/api/download-video/{key}', name: 'download_video', methods: 'GET')]
    public function downloadFile(string $key)
    {
        $file = $this->clientService->downloadFile($key);
        return new StreamedResponse(function () use ($file) {
            $outputStream = fopen('php://output', 'wb');
            stream_copy_to_stream($file->detach(), $outputStream);
        }, Response::HTTP_OK, [
            'Content-type' => 'application/force-download',
            'Content-Disposition' => 'attachment; filename=VGTRK_VIDEO.mp4'
        ]);
    }
}