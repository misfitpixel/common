<?php
/**
 * Created by PhpStorm.
 * User: misfitpixel
 * Date: 1/26/20
 * Time: 9:09 PM
 */

namespace MisfitPixel\Entity;


/**
 * Class OauthTokenType
 * @package MisfitPixel\Entity
 */
class OauthTokenType
{
    const ACCESS_TOKEN = 1;
    const REFRESH_TOKEN = 2;
    const AUTHORIZATION_CODE = 3;

    /** @var int */
    private $id;

    /** @var string */
    private $name;

    /**
     * @return int
     */
    public function getId(): int
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