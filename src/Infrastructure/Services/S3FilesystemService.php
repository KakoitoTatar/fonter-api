<?php
declare(strict_types=1);

namespace App\Infrastructure\Services;

use App\Application\Services\FileServiceException;
use App\Application\Services\FileServiceInterface;
use App\Application\Services\S3FilesystemServiceInterface;
use Aws\Exception\AwsException;
use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use Psr\Http\Message\UploadedFileInterface;
use Slim\Psr7\UploadedFile;

class S3FilesystemService implements S3FilesystemServiceInterface
{
    /**
     * @var S3Client
     */
    private S3Client $storage;

    private string $bucket;

    /**
     * S3FilesystemService constructor.
     * @param S3Client $s3
     */
    public function __construct(S3Client $s3, string $bucket)
    {
        $this->storage = $s3;
        $this->bucket = $bucket;
    }

    /**
     * @param string $identifier
     * @return bool
     */
    public function delete(string $identifier): bool
    {
        $result = $this->storage->deleteObject([
            'Bucket' => $this->bucket,
            'Key' => $identifier
        ]);

        return $result->get('DeleteMarker');
    }

    /**
     * @param string $identifier
     * @param UploadedFileInterface $uploadedFile
     * @return string
     * @throws FileServiceException
     */
    public function put(string $identifier, UploadedFileInterface $uploadedFile): string
    {
        try {
            $result = $this->storage->putObject([
                'Bucket' => $this->bucket,
                'Key' => $identifier,
                'ContentType' => $uploadedFile->getClientMediaType(),
                'Body' => $uploadedFile->getStream(),
            ]);
        } catch (S3Exception $e) {
            throw new FileServiceException($e->getMessage(), $e->getCode());
        }

        return $result->get('ObjectURL');
    }

    /**
     * @param string $bucket
     * @param string $identifier
     * @return UploadedFileInterface
     */
    public function get(string $bucket, string $identifier): UploadedFileInterface
    {
        $s3file = $this->storage->getObject([
            'Key' => $identifier,
            'Bucket' => $bucket
        ]);

        return new UploadedFile(
            $s3file->get('Body'),
            $identifier,
            $s3file->get('@metadata')['headers']['content-type'],
            $s3file->get('ContentLength')
        );
    }

    /**
     * @param string $sourceKey
     * @param string $destinationKey
     * @return string
     * @throws FileServiceException
     */
    public function move(
        string $sourceKey,
        string $destinationKey
    ): string {
        try {
            $this->storage->copy(
                $this->bucket,
                $sourceKey,
                $this->bucket,
                $destinationKey
            );
        } catch (S3Exception $e) {
            throw new FileServiceException($e->getMessage(), $e->getCode());
        }

        $this->delete($sourceKey);

        return $destinationKey;
    }
}
