<?php
declare(strict_types=1);

namespace App\Application\Actions;

use JsonSerializable;

class ActionError implements JsonSerializable
{
    public const BAD_REQUEST = 'BAD_REQUEST';
    public const INSUFFICIENT_PRIVILEGES = 'INSUFFICIENT_PRIVILEGES';
    public const NOT_ALLOWED = 'NOT_ALLOWED';
    public const NOT_IMPLEMENTED = 'NOT_IMPLEMENTED';
    public const RESOURCE_NOT_FOUND = 'RESOURCE_NOT_FOUND';
    public const SERVER_ERROR = 'SERVER_ERROR';
    public const UNAUTHENTICATED = 'UNAUTHENTICATED';
    public const VALIDATION_ERROR = 'Ошибка валидации запроса';
    public const VERIFICATION_ERROR = 'VERIFICATION_ERROR';

    /**
     * @var string
     */
    private string $type;

    /**
     * @var string|null
     */
    private $description;

    /**
     * @var array|null
     */
    private ?array $trace;

    /**
     * ActionError constructor.
     * @param string $type
     * @param string|null $description
     * @param array|null $trace
     */
    public function __construct(string $type, ?string $description, ?array $trace = null)
    {
        $this->type = $type;
        $this->description = $description;
        $this->trace = $trace;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return self
     */
    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     * @return self
     */
    public function setDescription($description = null): self
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @param array $trace
     * @return $this
     */
    public function setTrace(?array $trace): self
    {
        $this->trace = $trace;

        return $this;
    }

    /**
     * @return array
     */
    public function getTrace(): ?array
    {
        return $this->trace;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'type' => $this->type,
            'description' => $this->description,
            'trace' => $this->trace
        ];
    }
}
