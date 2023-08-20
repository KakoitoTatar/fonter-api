<?php

namespace App\Application\Auth;

use InvalidArgumentException;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\Signer\Key\InMemory;
use UnexpectedValueException;

/**
 * JwtAuth.
 */
final class JwtAuth
{
    /**
     * @var string The issuer name
     */
    private $issuer;

    /**
     * @var int Max lifetime in seconds
     */
    private $lifetime;

    /**
     * @var string The private key
     */
    private $privateKey;

    /**
     * @var string The public key
     */
    private $publicKey;

    /**
     * @var Sha256 The signer
     */
    private $signer;

    /**
     * @var Configuration
     */
    private Configuration $configuration;

    /**
     * The constructor.
     *
     * @param string $issuer The issuer name
     * @param int $lifetime The max lifetime
     * @param string $privateKey The private key as string
     * @param string $publicKey The public key as string
     */
    public function __construct(
        string $issuer,
        int $lifetime,
        string $privateKey,
        string $publicKey
    ) {
        $this->issuer = $issuer;
        $this->lifetime = 'PT' . $lifetime . 'S';
        $this->privateKey = InMemory::file($privateKey);
        $this->publicKey = InMemory::file($publicKey);
        $this->signer = new Sha256();

        $this->configuration = Configuration::forAsymmetricSigner(
            $this->signer,
            $this->privateKey,
            $this->publicKey
        );
    }

    /**
     * Get JWT max lifetime.
     *
     * @return int The lifetime in seconds
     */
    public function getLifetime(): int
    {
        return $this->lifetime;
    }

    /**
     * Create JSON web token.
     *
     * @param array $claims The claims
     *
     * @throws UnexpectedValueException
     *
     * @return string The JWT
     */
    public function createJwt(array $claims): string
    {
        $issuedAt = new \DateTimeImmutable();

        $builder = $this->configuration->builder();

        $builder = $builder->issuedBy($this->issuer)
            ->identifiedBy(uuid_create())
            ->issuedAt($issuedAt)
            ->canOnlyBeUsedAfter($issuedAt)
            ->expiresAt($issuedAt->add(new \DateInterval($this->lifetime)));

        foreach ($claims as $name => $value) {
            $builder = $builder->withClaim($name, $value);
        }

        return $builder->getToken($this->signer, $this->privateKey)->toString();
    }

    /**
     * Parse token.
     *
     * @param string $token The JWT
     *
     * @throws InvalidArgumentException
     *
     * @return Token The parsed token
     */
    public function createParsedToken(string $token): Token
    {
        return $this->configuration->parser()->parse($token);
    }

    /**
     * Validate the access token.
     *
     * @param string $accessToken The JWT
     *
     * @return bool The status
     */
    public function validateToken(string $accessToken): bool
    {
        $token = $this->createParsedToken($accessToken);

        if (!$token->hasBeenIssuedBy($this->issuer)) {
            // Token signature is not valid
            return false;
        }

        // Check whether the token has not expired
        return !$token->isExpired(new \DateTimeImmutable());
    }
}

