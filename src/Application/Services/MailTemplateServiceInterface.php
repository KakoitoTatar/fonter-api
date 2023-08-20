<?php
declare(strict_types=1);

namespace App\Application\Services;

interface MailTemplateServiceInterface
{
    public function makeBody(string $templateName, array $data): string;
}