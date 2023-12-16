<?php

declare(strict_types=1);

namespace App\Application;

use App\Application\Api\FileStorageInterface;
use GuzzleHttp\Psr7\Stream;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Uid\Uuid;

class ClientFilesService
{
    private const DOWNLOAD_VIDEO_METHOD = 'api/download-video';

    public function __construct(
        private readonly FileStorageInterface $fileStorage,
        private readonly MailerInterface $mailer,
        private readonly ParameterBagInterface $bag
    ) {
    }

    public function sendFileLinkByEmail(
        string $targetEmail,
        string $subject,
        string $text,
        string $filePath,
        string $contentType
    ): string {
        $videoUuid = Uuid::v4()->toRfc4122();
        $this->fileStorage->upload($videoUuid, $filePath, $contentType);
        $videoLink = $this->generateVideoUrl($videoUuid);
        $this->mailer->send(
            (new TemplatedEmail())
                ->from('smotrim@smotrim.video')
                ->to($targetEmail)
                ->subject($subject)
                ->htmlTemplate('email.html.twig')
            ->context(['video_link' => $videoLink])
        );
        return $videoLink;
    }

    public function downloadFile(string $key): Stream
    {
        return $this->fileStorage->download($key);
    }

    private function generateVideoUrl(string $videoUuid): string
    {
        $mainUrl = $this->bag->get('mainUrl');
        if (!$mainUrl) {
            throw new \RuntimeException('URL не определён');
        }

        $method = self::DOWNLOAD_VIDEO_METHOD;
        return "{$mainUrl}/$method/{$videoUuid}";
    }

}