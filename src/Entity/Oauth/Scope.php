<?php

namespace MisfitPixel\Entity\Oauth;

use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Entities\Traits\ScopeTrait;
use MisfitPixel\Entity\Abstraction\Respondent;

/**
 * Class Scope
 * @package MisfitPixel\Entity\Abstraction\Oauth
 */
class Scope implements ScopeEntityInterface
{
    use Respondent, ScopeTrait;

    /** @var int|null  */
    private ?int $id;

    /** @var string  */
    private string $name;

    /** @var string  */
    private string $identifier;

    /** @var string|null  */
    private ?string $description;

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
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * @return string|null
     */
    public function getDescription():? string
    {
        return $this->description;
    }
}
