<?php

declare (strict_types=1);

namespace App\Domain\Logotype;

use App\Domain\Media\Media;
use App\Domain\User\User;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="logotypes")
 * @ORM\Entity(repositoryClass=LogotypeRepository::class)
 */
class Logotype
{
    /**
     * @var int|null
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected ?int $id;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected string $name;

    /**
     * @var Media
     * @ORM\OneToOne(targetEntity="App\Domain\Media\Media")
     */
    protected Media $file;

    /**
     * @var Media
     * @ORM\OneToOne(targetEntity="App\Domain\Media\Media")
     */
    protected Media $cover;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="App\Domain\User\User")
     */
    protected User $author;

    /**
     * @var DateTime
     * @ORM\Column(type="datetime")
     */
    protected DateTime $createdAt;

    /**
     * @ORM\Column(type="simple_array")
     */
    protected array $tags;

    /**
     * @return array
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    /**
     * @param array $tags
     * @return $this
     */
    public function setTags(array $tags): self
    {
        $this->tags = $tags;

        return $this;
    }

    /**
     * @return Media
     */
    public function getFile(): Media
    {
        return $this->file;
    }

    /**
     * @param Media $file
     * @return $this
     */
    public function setFile(Media $file): self
    {
        $this->file = $file;

        return $this;
    }

    /**
     * @return User
     */
    public function getAuthor(): User
    {
        return $this->author;
    }

    /**
     * @param User $author
     * @return $this
     */
    public function setAuthor(User $author): self
    {
        $this->author = $author;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     * @return $this
     */
    public function setId(?int $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param DateTime $createdAt
     * @return $this
     */
    public function setCreatedAt(DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return Media
     */
    public function getCover(): Media
    {
        return $this->cover;
    }

    /**
     * @param Media $cover
     * @return $this
     */
    public function setCover(Media $cover): self
    {
        $this->cover = $cover;

        return $this;
    }
}
