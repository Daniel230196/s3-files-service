<?php

declare(strict_types=1);

namespace App\Application;

use App\Application\Api\FileStorageInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Uid\Uuid;

class SendFileToClientService
{
    public function __construct(
        private readonly FileStorageInterface $fileStorage,
        private readonly MailerInterface $mailer
    ) {
    }

    public function sendFileLinkByEmail(
        string $targetEmail,
        string $subject,
        string $text,
        string $filePath,
        string $contentType
    ): string {
        $videoLink = $this->fileStorage->upload(Uuid::v4()->toRfc4122(), $filePath, $contentType);
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

}