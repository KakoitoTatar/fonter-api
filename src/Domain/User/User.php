<?php

declare(strict_types=1);

namespace App\Domain\User;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint;

/**
 * @ORM\Entity
 * @ORM\Table(name="users", uniqueConstraints={@UniqueConstraint(name="email_idx", columns={"email"})})
 * @ORM\Entity(repositoryClass=UserRepository::class)
 */
class User
{
    /**
     * @var string
     */
    public const ADMIN = 'ROLE_ADMIN';

    /**
     * @var string
     */
    public const USER = 'ROLE_USER';

    /**
     * @var string
     */
    public const GUEST = 'ROLE_GUEST';

    /**
     * @var string
     */
    public const INACTIVE_USER = 'ROLE_INACTIVE_USER';

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected ?int $id;

    /**
     * @ORM\Column(type="string")
     */
    protected string $email;

    /**
     * @ORM\Column(type="string")
     */
    protected string $password;

    /**
     * @ORM\Column(type="string")
     */
    protected string $role;

    /**
     * @ORM\Column(type="datetime", name="last_login")
     */
    protected \DateTime $lastLogin;

    /**
     * @ORM\Column(type="string", length=64, nullable=true)
     */
    protected ?string $secretToken;

    /**
     * @param string $email
     * @param string $role
     * @param string $secretToken
     * @param string $password
     * @return $this
     */
    public function fillNewUser(string $email, string $role, string $secretToken, string $password): self
    {
        $this->email = $email;
        $this->role = $role;
        $this->lastLogin = new \DateTime();
        $this->secretToken = $secretToken;
        $this->password = password_hash($password, PASSWORD_DEFAULT);

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
     * @param string $email
     */
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword(string $password): void
    {
        $this->password = password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * Should only be used for migrating from the old site
     * @param string $password
     * @return void
     */
    public function setRawPassword(string $password): void
    {
        $this->password = $password;
    }

    /**
     * @return string
     */
    public function getRole(): string
    {
        return $this->role;
    }

    /**
     * @param \DateTime $dateTime
     * @return $this
     */
    public function setLastLogin(\DateTime $dateTime): self
    {
        $this->lastLogin = $dateTime;
        return $this;
    }

    /**
     * @return string
     */
    public function getSecretToken(): ?string
    {
        return $this->secretToken ?? null;
    }

    /**
     * @return \DateTime
     */
    public function getLastLogin(): \DateTime
    {
        return $this->lastLogin;
    }

    /**
     * @param string|null $secretToken
     */
    public function setSecretToken(?string $secretToken): void
    {
        $this->secretToken = $secretToken;
    }

    /**
     * @param string $role
     */
    public function setRole(string $role): void
    {
        $this->role = $role;
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
}
