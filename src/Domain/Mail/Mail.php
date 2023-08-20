<?php

declare(strict_types=1);

namespace App\Domain\Mail;

use Doctrine\ORM\Mapping as ORM;
use DateTime;

/**
 * @ORM\Table(name="mails")
 * @ORM\Entity(repositoryClass=MailRepository::class)
 */
class Mail
{
    /**
     * @var string
     */
    public const READY = 'ready';

    /**
     * @var string
     */
    public const SUCCESS = 'success';

    /**
     * @var string
     */
    public const ERROR = 'error';

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    private ?int $id;

    /**
     * @ORM\Column(type="string")
     */
    private string $author;

    /**
     * @ORM\Column(type="string")
     */
    private string $receiver;

    /**
     * @ORM\Column(type="string")
     */
    private string $subject;

    /**
     * @ORM\Column(type="string")
     */
    private string $template;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private array $data;

    /**
     * @ORM\Column(type="datetime")
     */
    private DateTime $sendAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private ?DateTime $sendedAt;

    /**
     * @ORM\Column(type="string")
     */
    private string $status;

    public function __construct(
        string $from,
        string $to,
        string $subject,
        string $template,
        array $data,
        \DateTime $sendAt = null,
        int $id = null
    ) {
        $this->id = $id;
        $this->author = $from;
        $this->receiver = $to;
        $this->subject = $subject;
        $this->template = $template;
        $this->data = $data;
        $this->status = self::READY;
        $this->sendAt = $sendAt ?? new \DateTime();
        $this->sendedAt = null;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getAuthor(): string
    {
        return $this->author;
    }

    /**
     * @return string
     */
    public function getSubject(): string
    {
        return $this->subject;
    }

    /**
     * @return string
     */
    public function getTemplate(): string
    {
        return $this->template;
    }

    /**
     * @return string
     */
    public function getReceiver(): string
    {
        return $this->receiver;
    }

    /**
     * @param \DateTime $sendedAt
     */
    public function setSendedAt(\DateTime $sendedAt): void
    {
        $this->sendedAt = $sendedAt;
    }

    /**
     * @param string $status
     */
    public function setStatus(string $status): void
    {
        $this->status = $status;
    }
}
