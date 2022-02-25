<?php

namespace MisfitPixel\Entity\Abstraction\Oauth;

use Doctrine\Persistence\Event\LifecycleEventArgs;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use MisfitPixel\Entity\Abstraction\Dated;
use MisfitPixel\Entity\Abstraction\Persistent;
use MisfitPixel\Entity\Abstraction\Respondent;
use MisfitPixel\Entity\Abstraction\Statused;

/**
 * Class Client
 * @package MisfitPixel\Entity\Abstraction\Oauth
 */
abstract class Client implements ClientEntityInterface
{
    use Dated, Persistent, Respondent, Statused;

    /** @var int|null  */
    protected ?int $id = null;

    /** @var string  */
    protected string $clientId;

    /** @var string  */
    protected string $secret;

    /** @var string  */
    protected string $name;

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
    public function getClientId(): string
    {
        return $this->clientId;
    }

    /**
     * @param LifecycleEventArgs $event
     * @return $this
     * @throws \Exception
     */
    public function generateClientId(LifecycleEventArgs $event): self
    {
        do {
            $clientId = bin2hex(random_bytes(16));

            $client = $event->getObjectManager()->getRepository($this->getContainer()->getParameter('oauth')['client_entity'])
                ->findOneBy([
                    'clientId' => $clientId
                ])
            ;

        } while($client !== null);

        $this->clientId = $clientId;

        return $this;
    }

    /**
     * @return string
     */
    public function getSecret(): string
    {
        return $this->secret;
    }

    /**
     * @param LifecycleEventArgs $event
     * @return $this
     * @throws \Exception
     */
    public function generateSecret(LifecycleEventArgs $event): self
    {
        $this->secret = bin2hex(random_bytes(64));

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
     * @return bool
     */
    public function isConfidential(): bool
    {
        return true;
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->getClientId();
    }
}
