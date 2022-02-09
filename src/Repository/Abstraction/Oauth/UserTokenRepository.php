<?php

namespace MisfitPixel\Repository\Abstraction\Oauth;

use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use MisfitPixel\Entity\Oauth\UserToken;
use MisfitPixel\Entity\Oauth\UserTokenType;
use MisfitPixel\Entity\Status;
use MisfitPixel\Entity\User;
use MisfitPixel\Repository\Abstraction\BaseRepository;

/**
 * Class UserTokenRepository
 * @package MisfitPixel\Repository\Abstraction\Oauth
 */
abstract class UserTokenRepository extends BaseRepository
    implements AccessTokenRepositoryInterface, RefreshTokenRepositoryInterface, AuthCodeRepositoryInterface
{
    /**
     * @return string
     */
    abstract function getUserTokenTypeEntityClassName(): string;

    /**
     * @return string
     */
    abstract function getUserEntityClassName(): string;

    /**
     * @param string $token
     * @return UserToken|null
     */
    public function findOneByToken(string $token): ?UserToken
    {
        return $this->findOneBy([
            'token' => $token
        ]);
    }

    /**
     * @param ClientEntityInterface $clientEntity
     * @param array $scopes
     * @param $userIdentifier
     * @return UserToken
     */
    public function getNewToken(ClientEntityInterface $clientEntity, array $scopes, $userIdentifier = null): UserToken
    {
        $className = $this->getUserTokenTypeEntityClassName();
        $token = new $className();
        $token->setUserTokenType($this->getEntityManager()->getRepository($this->getUserTokenTypeEntityClassName())->find(UserTokenType::ACCESS_TOKEN))
            ->setClient($clientEntity)
        ;

        /**
         * add scopes to JWT.
         */
        foreach($scopes as $scope) {
            $token->addScope($scope);
        }

        /**
         * for user authorizations (auth_code, password, etc);
         * set a user on the token.
         */
        if($userIdentifier !== null) {
            $token->setUser($this->getEntityManager()->getRepository($this->getUserEntityClassName())->findOneByUsername($userIdentifier));
        }

        return $token;
    }

    /**
     * @param AccessTokenEntityInterface|UserToken $accessTokenEntity
     * @return void
     */
    public function persistNewAccessToken(AccessTokenEntityInterface $accessTokenEntity)
    {
        $accessTokenEntity->save();
    }

    /**
     * @param $tokenId
     * @return void
     */
    public function revokeAccessToken($tokenId)
    {
        /** @var UserToken $token */
        $token = $this->findOneBy([
            'token' => $tokenId
        ]);

        if(null === $token) {
            return;
        }

        $token->delete(true);
    }

    /**
     * @param $tokenId
     * @return bool
     */
    public function isAccessTokenRevoked($tokenId): bool
    {
        /** @var UserToken $token */
        $token = $this->findOneByToken($tokenId);

        return ($token === null) || $token->getStatusId() !== Status::ACTIVE;
    }

    /**
     * @return UserToken
     */
    public function getNewRefreshToken(): UserToken
    {
        $className = $this->getUserTokenTypeEntityClassName();
        $token = new $className();
        $token->setUserTokenType($this->getEntityManager()->getRepository($this->getUserTokenTypeEntityClassName())->find(UserTokenType::REFRESH_TOKEN));

        return $token;
    }

    /**
     * @param RefreshTokenEntityInterface|UserToken $refreshTokenEntity
     * @return void
     */
    public function persistNewRefreshToken(RefreshTokenEntityInterface $refreshTokenEntity)
    {
        $refreshTokenEntity->setUser($refreshTokenEntity->getParent()->getUser())
            ->setClient($refreshTokenEntity->getParent()->getClient())
            ->save()
        ;
    }

    /**
     * @param $tokenId
     * @return void
     */
    public function revokeRefreshToken($tokenId)
    {
        $this->revokeAccessToken($tokenId);
    }

    /**
     * @param $tokenId
     * @return bool
     */
    public function isRefreshTokenRevoked($tokenId): bool
    {
        return $this->isAccessTokenRevoked($tokenId);
    }

    /**
     * @return UserToken
     */
    public function getNewAuthCode(): UserToken
    {
        $className = $this->getUserTokenTypeEntityClassName();
        $token = new $className();
        $token->setUserTokenType($this->getEntityManager()->getRepository($this->getUserTokenTypeEntityClassName())->find(UserTokenType::AUTHORIZATION_CODE));

        return $token;
    }

    /**
     * @param AuthCodeEntityInterface|UserToken $authCodeEntity
     * @return void
     */
    public function persistNewAuthCode(AuthCodeEntityInterface $authCodeEntity)
    {
        $authCodeEntity->save();
    }

    /**
     * @param $codeId
     * @return void
     */
    public function revokeAuthCode($codeId)
    {
        $this->revokeAccessToken($codeId);
    }

    /**
     * @param $codeId
     * @return bool
     */
    public function isAuthCodeRevoked($codeId): bool
    {
        return $this->isAccessTokenRevoked($codeId);
    }
}
