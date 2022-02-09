<?php

namespace MisfitPixel\Entity\Oauth;

use Lcobucci\JWT\Token\Plain;
use League\OAuth2\Server\CryptKey;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Entities\Traits\AccessTokenTrait;
use MisfitPixel\Entity\Abstraction\Dated;
use MisfitPixel\Entity\Abstraction\Oauth\Client;
use MisfitPixel\Entity\Abstraction\Persistent;
use MisfitPixel\Entity\Abstraction\Statused;
use MisfitPixel\Entity\User;

/**
 * Class UserToken
 * @package MisfitPixel\Entity\Oauth
 */
class UserToken implements AccessTokenEntityInterface, RefreshTokenEntityInterface, AuthCodeEntityInterface
{
    use Dated, Statused, Persistent, AccessTokenTrait;

    /** @var int|null  */
    protected ?int $id = null;

    /** @var UserToken|null  */
    protected ?UserToken $parent = null;

    /** @var UserTokenType  */
    protected UserTokenType $userTokenType;

    /** @var User|null  */
    protected ?User $user = null;

    /** @var Client|null  */
    protected ?Client $client = null;

    /** @var string  */
    protected string $token;

    /** @var \DateTime|null  */
    protected ?\DateTime $dateExpired;

    /** @var Scope[]  */
    protected array $scopes = [];

    /**
     * @return string
     */
    abstract function getUserClassName(): string;

    /**
     * @param UserTokenType $type
     * @param User $user
     * @param \DateTime $dateExpired
     * @param bool $save
     * @return UserToken
     * @throws \Exception
     */
    public static function generate(UserTokenType $type, User $user, \DateTime $dateExpired, bool $save = true): UserToken
    {
        do {
            $code = bin2hex(random_bytes(16));

            $tokenInUse = (new self())->getManager()->getRepository(self::class)
                ->findOneByToken($code)
            ;

        } while($tokenInUse !== null);

        $token = (new UserToken())
            ->setUserTokenType($type)
            ->setUser($user)
            ->setToken($code)
            ->setDateExpired($dateExpired)
        ;

        $token->setPrivateKey(new CryptKey(sprintf("file://%s", (new self)->getContainer()->getParameter('oauth')['private_key'])));

        if($save) {
            $token->save();
        }

        return $token;
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return UserToken|null
     */
    public function getParent(): ?UserToken
    {
        return $this->parent;
    }

    /**
     * @param UserToken|null $token
     * @return $this
     */
    public function setParent(?UserToken $token): self
    {
        $this->parent = $token;

        return $this;
    }

    /**
     * @return UserTokenType
     */
    public function getUserTokenType(): UserTokenType
    {
        return $this->userTokenType;
    }

    /**
     * @param UserTokenType $type
     * @return $this
     */
    public function setUserTokenType(UserTokenType $type): self
    {
        $this->userTokenType = $type;

        return $this;
    }

    /**
     * @return User|null
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * @param User $user
     * @return $this
     */
    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @param string $token
     * @return $this
     */
    public function setToken(string $token): self
    {
        $this->token = $token;

        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getDateExpired(): ?\DateTime
    {
        return $this->dateExpired;
    }

    /**
     * @param \DateTime|null $date
     * @return $this
     */
    public function setDateExpired(?\DateTime $date): self
    {
        $this->dateExpired = $date;

        return $this;
    }

    /**
     * @return Client
     */
    public function getClient(): ?Client
    {
        return $this->client;
    }

    /**
     * @param ClientEntityInterface|null $client
     * @return $this
     */
    public function setClient(?ClientEntityInterface $client): self
    {
        $this->client = $client;

        return $this;
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->token;
    }

    /**
     * @param $identifier
     * @return $this
     */
    public function setIdentifier($identifier): self
    {
        return $this->setToken($identifier);
    }

    /**
     * @return array|\League\OAuth2\Server\Entities\ScopeEntityInterface[]
     */
    public function getScopes(): array
    {
        /**
         * TODO: get scopes from client?
         * TODO: or get scopes from /token request params?
         */
        return $this->scopes;
    }

    /**
     * @param ScopeEntityInterface $scope
     * @return $this
     */
    public function addScope(ScopeEntityInterface $scope): self
    {
        $this->scopes[] = $scope;

        return $this;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getExpiryDateTime(): \DateTimeImmutable
    {
        return \DateTimeImmutable::createFromMutable($this->getDateExpired());
    }

    /**
     * @param \DateTimeImmutable $dateTime
     * @return $this
     */
    public function setExpiryDateTime(\DateTimeImmutable $dateTime): self
    {
        $this->setDateExpired(\DateTime::createFromImmutable($dateTime));

        return $this;
    }

    /**
     * @return string|null
     */
    public function getUserIdentifier(): ?string
    {
        return ($this->getUser() != null) ? $this->getUser()->getUsername() : null;
    }

    /**
     * @param $identifier
     * @return $this
     */
    public function setUserIdentifier($identifier): self
    {
        $this->setUser($this->getManager()->getRepository($this->getUserClassName())->findOneByUsername($identifier));

        return $this;
    }

    /**
     * @return UserToken
     */
    public function getAccessToken(): UserToken
    {
        return $this->getParent();
    }

    /**
     * @param AccessTokenEntityInterface $accessToken
     * @return $this
     */
    public function setAccessToken(AccessTokenEntityInterface $accessToken): self
    {
        return $this->setParent($accessToken);
    }

    public function getRedirectUri()
    {
        /**
         * TODO: make dynamic based on input from signin form.
         * TODO: not saved to user_token, encoded in auth_code.
         */
        return 'https://alchemy.misfitpixel.io/oauth/redirect';
    }

    public function setRedirectUri($uri)
    {
        // TODO: Implement setRedirectUri() method.
    }

    /**
     * @return Plain
     */
    public function convertToJwt(): Plain
    {
        $this->initJwtConfiguration();

        return $this->jwtConfiguration->builder()
            ->permittedFor(($this->getClient()->getClientId()) ?? 'https://alchemy.misfitpixel.io')
            ->identifiedBy($this->getIdentifier())
            ->issuedAt(new \DateTimeImmutable())
            ->canOnlyBeUsedAfter(new \DateTimeImmutable())
            ->expiresAt($this->getExpiryDateTime())
            ->relatedTo((string) $this->getUserIdentifier())
            /**
             * TODO: update User::getRoles() to return scopes here
             */
            ->withClaim('scopes', $this->getScopes())
            ->getToken($this->jwtConfiguration->signer(), $this->jwtConfiguration->signingKey())
            ;
    }
}
