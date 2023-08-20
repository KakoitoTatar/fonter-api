<?php
declare(strict_types=1);

namespace App\Domain\Media;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="media", uniqueConstraints={@ORM\UniqueConstraint(name="url_idx", columns={"bucket", "url"})})
 * @ORM\Entity(repositoryClass=MediaRepository::class)
 */
class Media
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected ?int $id;

    /**
     * @ORM\Column(type="string")
     */
    protected string $bucket;

    /**
     * @ORM\Column(type="string")
     */
    protected string $url;

    /**
     * @ORM\Column(type="boolean")
     */
    protected bool $temporal;

    /**
     * Media constructor.
     */
    public function __construct()
    {
        $this->bucket = 'fonter';
    }

    /**
     * @param int|null $id
     */
    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param string $url
     * @return $this
     */
    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getBucket(): string
    {
        return $this->bucket;
    }

    /**
     * @param string $bucket
     * @return $this
     */
    public function setBucket(string $bucket): self
    {
        $this->bucket = $bucket;

        return $this;
    }

    /**
     * @param bool $isTemporal
     * @return $this
     */
    public function setTemporal(bool $isTemporal): self
    {
        $this->temporal = $isTemporal;

        return $this;
    }

    /**
     * @return bool
     */
    public function isTemporal(): bool
    {
        return $this->temporal;
    }
}
