<?php

namespace MisfitPixel\Repository\Abstraction\Oauth;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;
use MisfitPixel\Entity\User;
use MisfitPixel\Repository\Abstraction\BaseRepository;

/**
 * Class UserRepository
 * @package MisfitPixel\Repository\Abstraction\Oauth
 */
abstract class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    /**
     * @param string $username
     * @return User|null
     */
    public function findOneByUsername(string $username): ?User
    {
        return $this->findOneBy([
            'username' => $username
        ]);
    }

    /**
     * @param $username
     * @param $password
     * @param $grantType
     * @param ClientEntityInterface $clientEntity
     * @return User|null
     */
    public function getUserEntityByUserCredentials($username, $password, $grantType, ClientEntityInterface $clientEntity): ?User
    {
        $user = $this->findOneByUsername($username);

        if(
            $user === null ||
            !$user->isPassword($password)
        ) {
            return null;
        }

        return $user;
    }
}
