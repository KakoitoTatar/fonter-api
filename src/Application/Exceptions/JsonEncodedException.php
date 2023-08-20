<?php
declare(strict_types=1);

namespace App\Application\Exceptions;

use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpException;
use Throwable;

class JsonEncodedException extends HttpException
{
    public function __construct(ServerRequestInterface $request, array $message = [], int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($request, json_encode($message, JSON_THROW_ON_ERROR), $code, $previous);
    }

    /**
     * Returns the json decoded message.
     *
     * @param bool $assoc
     *
     * @return mixed
     */
    public function getDecodedMessage($assoc = false)
    {
        return json_decode($this->getMessage(), $assoc, 512, JSON_THROW_ON_ERROR);
    }
}