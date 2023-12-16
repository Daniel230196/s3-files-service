<?php

declare(strict_types=1);

namespace App\Infrastructure\S3;

use App\Application\Api\FileStorageInterface;
use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use GuzzleHttp\Psr7\Stream;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

class S3StorageService implements FileStorageInterface
{
    public const ACL_PRIVATE = 'private';
    public const ACL_PUBLIC_READ = 'public-read';
    public const ACL_PUBLIC_READ_WRITE = 'public-read-write';
    public const ACL_AUTH_READ = 'authenticated-read';
    public const ACL_BUCKET_OWN_READ = 'bucket-owner-read';
    public const ACL_BUCKET_OWN_FULL_CONTROL = 'bucket-owner-full-control';

    private S3Client $s3Client;

    private string $bucket;

    public function __construct(
        ContainerBagInterface $bag
    )
    {
        $s3Parameters = (object)$bag->get('s3');

        $this->s3Client = new S3Client([
            'version' => 'latest',
            'region' => $s3Parameters->region,
            'credentials' => [
                'key' => $s3Parameters->key,
                'secret' => $s3Parameters->secret,
            ],
            'endpoint' => "$s3Parameters->endpoint"
        ]);
        $this->bucket = $s3Parameters->bucket;
    }

    public function upload(string $key, string $filepath, string $contentType, string $access = self::ACL_PUBLIC_READ): string
    {
        try {
            if ($this->s3Client->doesObjectExist($this->bucket, $key)) {
                throw new \Exception('Файл уже существует', 400);
            }

            $this->s3Client->putObject([
                'Bucket' => $this->bucket,
                'Key' => $key,
                'ContentType' => $contentType,
                'SourceFile' => $filepath,
                'ACL' => $access
            ]);
            return $this->s3Client->getObjectUrl($this->bucket, $key);
        } catch (S3Exception $e) {
            throw new \Exception( $e->getMessage() . DIRECTORY_SEPARATOR . $e->getAwsErrorMessage() . DIRECTORY_SEPARATOR . $e->getAwsErrorCode(), 401);
        }
    }

    public function download(string $key = '0dab6916-b303-4125-a616-0add108a6a17')
    {
        if (!$this->s3Client->doesObjectExist($this->bucket, $key)) {
            throw new \Exception('Файл не существует', 400);
        }


        $result = $this->s3Client->getObject([
        'Bucket' => $this->bucket,
            'Key' => $key,
        ]);

        return $result->get('Body');
    }
}
