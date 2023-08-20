<?php

use App\Application\Validator\Rules\EntityExistsRuleInterface;
use App\Application\Validator\Rules\UploadedFileRuleInterface;
use App\Infrastructure\Validator\Rules\EntityExistsRule;
use App\Infrastructure\Validator\Rules\UploadedFileRule;
use DI\ContainerBuilder;
use App\Application\Validator\ValidatorInterface;
use App\Infrastructure\Validator\Validator;
use App\Infrastructure\Validator\Rules\UniqueRule;
use App\Application\Validator\Rules\UniqueRuleInterface;
use Psr\Container\ContainerInterface;
use Doctrine\ORM\EntityManagerInterface;

return function (ContainerBuilder $containerBuilder) {
    return $containerBuilder->addDefinitions([
        ValidatorInterface::class => function (ContainerInterface $c) {
            return new Validator($c);
        },
        UniqueRuleInterface::class => function (ContainerInterface $c) {
            return new UniqueRule($c->get(EntityManagerInterface::class));
        },
        UploadedFileRuleInterface::class => function (ContainerInterface $c) {
            return new UploadedFileRule();
        },
        EntityExistsRuleInterface::class => function (ContainerInterface $c) {
            return new EntityExistsRule($c->get(EntityManagerInterface::class));
        },
        'customValidatorRules' => function (ContainerInterface $c) {
            return [
                $c->get(UniqueRuleInterface::class),
                $c->get(UploadedFileRuleInterface::class),
                $c->get(EntityExistsRuleInterface::class)
            ];
        }
    ]);
};