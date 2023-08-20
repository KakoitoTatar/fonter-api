<?php
declare(strict_types=1);

namespace App\Infrastructure\Services;

use App\Application\Services\MailTemplateServiceInterface;

class MailTemplateService implements MailTemplateServiceInterface
{
    /**
     * @param string $templateName
     * @param array $data
     * @return string
     */
    public function makeBody(string $templateName, array $data): string
    {
        $rawBody = require __DIR__ . '/../../../templates/mail/' . $templateName . '.php';
        $keys = array_map(
            static function ($value) {
                return '#' . mb_strtoupper($value) . '#';
            },
            array_keys($data)
        );

        $values = array_values($data);

        return str_replace($keys, $values, $rawBody);
    }
}