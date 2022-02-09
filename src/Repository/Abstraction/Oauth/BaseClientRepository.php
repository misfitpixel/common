<?php

namespace MisfitPixel\Repository\Abstraction\Oauth;

use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use MisfitPixel\Entity\Abstraction\Oauth\Client;
use MisfitPixel\Entity\Status;
use MisfitPixel\Repository\Abstraction\BaseRepository;

/**
 * Class BaseClientRepository
 * @package MisfitPixel\Repository\Abstraction\Oauth
 */
abstract class BaseClientRepository extends BaseRepository implements ClientRepositoryInterface
{
    /**
     * @param $clientIdentifier
     * @return Client|null
     */
    public function getClientEntity($clientIdentifier): ?Client
    {
        return $this->findOneBy([
            'clientId' => $clientIdentifier,
            'statusId' => Status::ACTIVE
        ]);
    }

    /**
     * @param $clientIdentifier
     * @param $clientSecret
     * @param $grantType
     * @return bool
     */
    public function validateClient($clientIdentifier, $clientSecret, $grantType): bool
    {
        $client = $this->getClientEntity($clientIdentifier);

        if($client === null) {
            return false;
        }

        return $client->getSecret() === $clientSecret;
    }
}
