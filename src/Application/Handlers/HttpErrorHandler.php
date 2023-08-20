<?php
declare(strict_types=1);

namespace App\Application\Handlers;

use App\Application\Actions\ActionError;
use App\Application\Actions\ActionPayload;
use App\Application\Actions\RequestValidationException;
use Exception;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpException;
use Slim\Exception\HttpForbiddenException;
use Slim\Exception\HttpMethodNotAllowedException;
use Slim\Exception\HttpNotFoundException;
use Slim\Exception\HttpNotImplementedException;
use Slim\Exception\HttpUnauthorizedException;
use Slim\Handlers\ErrorHandler as SlimErrorHandler;
use Slim\Interfaces\CallableResolverInterface;
use Symfony\Component\Serializer\Serializer;
use Throwable;

class HttpErrorHandler extends SlimErrorHandler
{
    /**
     * @var Serializer
     */
    private Serializer $serializer;

    /**
     * HttpErrorHandler constructor.
     * @param Serializer $serializer
     * @param CallableResolverInterface $callableResolver
     * @param ResponseFactoryInterface $responseFactory
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        Serializer $serializer,
        CallableResolverInterface $callableResolver,
        ResponseFactoryInterface $responseFactory,
        ?LoggerInterface $logger = null
    ) {
        parent::__construct($callableResolver, $responseFactory, $logger);
        $this->serializer = $serializer;
    }

    /**
     * @inheritdoc
     */
    protected function respond(): Response
    {
        $exception = $this->exception;
        $statusCode = 500;
        $error = new ActionError(
            ActionError::SERVER_ERROR,
            'An internal error has occurred while processing your request.'
        );

        if ($exception instanceof HttpException) {
            $statusCode = $exception->getCode();
            $error->setDescription($exception->getDescription());

            if ($exception instanceof HttpNotFoundException) {
                $error->setType($exception->getMessage());
            } elseif ($exception instanceof HttpMethodNotAllowedException) {
                $error->setType($exception->getMessage());
            } elseif ($exception instanceof HttpUnauthorizedException) {
                $error->setType($exception->getMessage());
            } elseif ($exception instanceof HttpForbiddenException) {
                $error->setType($exception->getMessage());
            } elseif ($exception instanceof HttpBadRequestException) {
                $error->setType($exception->getMessage());
            } elseif ($exception instanceof HttpNotImplementedException) {
                $error->setType($exception->getMessage());
            } elseif ($exception instanceof RequestValidationException) {
                $error->setDescription($exception->getDecodedMessage(true));
                $error->setType(ActionError::VALIDATION_ERROR);
            }
        }

        if (
            !($exception instanceof HttpException)
            && $exception instanceof Throwable
            && $this->displayErrorDetails
        ) {
            $error->setDescription($exception->getMessage());
            $error->setTrace(explode(PHP_EOL, $exception->getTraceAsString()));
        }

        $payload = new ActionPayload($this->statusCode,null, $error);

        $encodedPayload = $this->serializer->serialize($payload->getError(), 'json');

        $response = $this->responseFactory->createResponse($statusCode);

        $response->getBody()->write($encodedPayload);

        return $response->withHeader('Content-Type', 'application/json');
    }
}
