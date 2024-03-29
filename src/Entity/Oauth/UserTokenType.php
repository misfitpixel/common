<?php
/**
 * Created by PhpStorm.
 * User: misfitpixel
 * Date: 1/26/20
 * Time: 9:09 PM
 */

namespace MisfitPixel\Entity\Oauth;

use MisfitPixel\Entity\Abstraction\Respondent;

/**
 * Class UserTokenType
 * @package MisfitPixel\Entity\Oauth
 */
class UserTokenType
{
    const ACCESS_TOKEN = 1;
    const REFRESH_TOKEN = 2;
    const AUTHORIZATION_CODE = 3;
    const PASSWORD_RESET = 4;

    use Respondent;

    /** @var int|null  */
    protected ?int $id;

    /** @var string */
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
    public function getName(): string
    {
        return $this->name;
    }
}
