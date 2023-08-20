<?php

declare (strict_types=1);

namespace App\Infrastructure\Validator\Rules;

use App\Application\Validator\Rules\UploadedFileRuleInterface;
use Psr\Http\Message\UploadedFileInterface;
use Slim\Psr7\UploadedFile;

class UploadedFileRule implements UploadedFileRuleInterface
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'uploadedFile';
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return '{field} must be file';
    }

    /**
     * @param $field
     * @param $value
     * @param array $params
     * @param array $fields
     * @return bool
     */
    public function check($field, $value, array $params, array $fields): bool
    {
        if ($value instanceof UploadedFileInterface) {
            $ext = pathinfo($value->getClientFilename(), PATHINFO_EXTENSION);

            if (empty($params[0])) {
                return true;
            }

            if (in_array($ext, $params[0])) {
                return true;
            }
        }

        return false;
    }
}
