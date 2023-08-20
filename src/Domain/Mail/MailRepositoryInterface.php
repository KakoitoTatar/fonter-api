<?php

declare(strict_types=1);

namespace App\Domain\Mail;

interface MailRepositoryInterface
{
    public function find(int $id);

    public function getAllNewNotSendedMails(): array;

    public function save(Mail $mail): Mail;
}
