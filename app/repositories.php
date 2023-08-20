<?php

declare(strict_types=1);

use App\Domain\Font\Font;
use App\Domain\Font\FontRepositoryInterface;
use App\Domain\Logotype\Logotype;
use App\Domain\Logotype\LogotypeRepositoryInterface;
use App\Domain\Mail\Mail;
use App\Domain\Mail\MailRepositoryInterface;
use App\Domain\Media\Media;
use App\Domain\Media\MediaRepositoryInterface;
use App\Domain\User\User;
use App\Domain\User\UserRepository;
use App\Infrastructure\Doctrine\Font\FontRepository;
use App\Infrastructure\Doctrine\Logotype\LogotypeRepository;
use App\Infrastructure\Doctrine\Media\MediaRepository;
use DI\ContainerBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        UserRepository::class => function (ContainerInterface $c) {
            $em = $c->get(EntityManagerInterface::class);
            $cm = $em->getMetadataFactory()->getMetadataFor(User::class);
            return new \App\Infrastructure\Doctrine\User\UserRepository($em, $cm);
        },

        MailRepositoryInterface::class => function (ContainerInterface $c) {
            $em = $c->get(EntityManagerInterface::class);
            $cm = $em->getMetadataFactory()->getMetadataFor(Mail::class);
            return new \App\Infrastructure\Doctrine\Mail\MailRepository($em, $cm);
        },
        MediaRepositoryInterface::class => function (ContainerInterface $c) {
            $em = $c->get(EntityManagerInterface::class);
            $cm = $em->getMetadataFactory()->getMetadataFor(Media::class);
            return new MediaRepository($em, $cm);
        },
        FontRepositoryInterface::class => function (ContainerInterface $c) {
            $em = $c->get(EntityManagerInterface::class);
            $cm = $em->getMetadataFactory()->getMetadataFor(Font::class);
            return new FontRepository($em, $cm);
        },
        LogotypeRepositoryInterface::class => function (ContainerInterface $c) {
            $em = $c->get(EntityManagerInterface::class);
            $cm = $em->getMetadataFactory()->getMetadataFor(Logotype::class);
            return new LogotypeRepository($em, $cm);
        },
    ]);
};
