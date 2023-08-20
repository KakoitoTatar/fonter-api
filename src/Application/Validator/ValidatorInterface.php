<?php
declare(strict_types=1);

namespace App\Application\Validator;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;

interface ValidatorInterface
{
    /**
     * @param ServerRequestInterface $request
     * @param array $rules
     */
    public function validate(ServerRequestInterface $request, array $rules): void;

    /**
     * @return bool
     */
    public function isValid(): bool;

    /**
     * @return array|null
     */
    public function getErrors(): ?array;

    /**
     * @return array|null
     */
    public function getValidData(): ?array;
}
