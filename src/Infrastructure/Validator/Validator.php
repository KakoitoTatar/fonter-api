<?php
declare(strict_types=1);

namespace App\Infrastructure\Validator;

use App\Application\Validator\Rules\RuleInterface;
use App\Application\Validator\ValidatorInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Psr7\Request;
use Valitron\Validator as Valitron;

class Validator implements ValidatorInterface
{
    /**
     * @var mixed|Valitron
     */
    protected Valitron $validator;

    /**
     * @var RequestInterface
     */
    protected RequestInterface $request;

    /**
     * @var bool
     */
    private bool $isValid = false;

    /**
     * @var array
     */
    private array $errors = [];

    /**
     * @var ContainerInterface
     */
    private ContainerInterface $container;

    /**
     * Validator constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @inheritDoc
     */
    public function isValid(): bool
    {
        return $this->isValid;
    }

    /**
     * @inheritDoc
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @inheritDoc
     */
    protected function registerCustomValidators(Valitron $validator, array $customValidators): void
    {
        /**
         * @var RuleInterface $rule
         */
        foreach ($customValidators as $rule) {
            $validator->addInstanceRule(
                $rule->getName(),
                function ($field, $value, array $params, array $fields) use ($rule) {
                    return $rule->check($field, $value, $params, $fields);
                },
                $rule->getMessage()
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function validate(ServerRequestInterface $request, array $rules): void
    {
        $data = array_merge(
            $request->getQueryParams(),
            $request->getParsedBody() ?? [],
            $request->getUploadedFiles(),
            $request->getAttribute('__routingResults__')->getRouteArguments() ?? [],
        );

        $this->validator = new \Valitron\Validator($data);

        $this->registerCustomValidators(
            $this->validator,
            $this->container->get('customValidatorRules')
        );

        $this->validator->mapFieldsRules($rules);

        $this->isValid = $this->validator->validate();

        $this->errors = $this->validator->errors();
    }

    /**
     * @inheritDoc
     */
    public function getValidData(): ?array
    {
        if ($this->isValid()) {
            return $this->validator->data();
        }

        return [];
    }
}
