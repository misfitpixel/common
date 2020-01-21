<?php
/**
 * Created by PhpStorm.
 * User: misfitpixel
 * Date: 1/19/20
 * Time: 8:52 PM
 */

namespace MisfitPixel\Entity;


/**
 * Interface Status
 * @package MisfitPixel\Entity
 */
interface Status
{
    const ACTIVE = 1;
    const INACTIVE = 2;
    const EXPIRED = 3;
    const DELETED = 4;
    const COMPLETE = 5;

    /**
     * @return int
     */
    public function getId(): int;

    /**
     * @return string
     */
    public function getName(): string;
}