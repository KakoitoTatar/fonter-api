<?php

namespace App\Application\Validator\Rules;

interface RuleInterface
{
    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return string
     */
    public function getMessage(): string;

    /**
     * @param $field
     * @param $value
     * @param array $params
     * @param array $fields
     * @return bool
     */
    public function check($field, $value, array $params, array $fields): bool;
}
