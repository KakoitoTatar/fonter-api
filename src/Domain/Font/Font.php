<?php

declare (strict_types=1);

namespace App\Domain\Font;

use App\Domain\Media\Media;
use App\Domain\User\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="fonts")
 * @ORM\Entity(repositoryClass=FontRepository::class)
 */
class Font
{
    /**
     * @var int|null
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected ?int $id;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    protected string $name;

    /**
     * @ORM\Column(type="simple_array")
     */
    protected array $tags;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="App\Domain\User\User")
     */
    protected User $author;

    /**
     * @var Media
     * @ORM\OneToOne(targetEntity="App\Domain\Media\Media")
     */
    protected Media $file;

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
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
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
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
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
     * @return User
     */
    public function getAuthor(): User
    {
        return $this->author;
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
     * @return Media
     */
    public function getFile(): Media
    {
        return $this->file;
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
     * @return array
     */
    public function getTags(): array
    {
        return $this->tags;
    }
}
