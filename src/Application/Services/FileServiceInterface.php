<?php

namespace App\Application\Services;

use Psr\Http\Message\UploadedFileInterface;

interface FileServiceInterface
{
    /**
     * @param string $identifier
     * @return bool
     */
    public function delete(string $identifier): bool;

    /**
     * @param string $identifier
     * @param UploadedFileInterface $uploadedFile
     * @return string
     */
    public function put(string $identifier, UploadedFileInterface $uploadedFile): string;

    /**
     * @param string $bucket
     * @param string $identifier
     * @return UploadedFileInterface
     */
    public function get(string $bucket, string $identifier): UploadedFileInterface;

    /**
     * @param string $sourceKey
     * @param string $destinationKey
     * @return string
     */
    public function move(
        string $sourceKey,
        string $destinationKey
    ): string;
}
