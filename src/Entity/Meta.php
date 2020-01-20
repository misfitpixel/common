<?php
/**
 * Created by PhpStorm.
 * User: misfitpixel
 * Date: 1/19/20
 * Time: 8:57 PM
 */

namespace Entity;


/**
 * Interface Meta
 * @package Entity
 */
interface Meta
{
    /**
     * @return int
     */
    public function getId(): int;

    /**
     * @return string
     */
    public function getField(): string;

    /**
     * @param string $field
     * @return Meta
     */
    public function setField(string $field): self;

    /**
     * @return null|string
     */
    public function getValue1(): ?string;

    /**
     * @param null|string $value
     * @return Meta
     */
    public function setValue1(?string $value): self;

    /**
     * @return null|string
     */
    public function getValue2(): ?string;

    /**
     * @param null|string $value
     * @return Meta
     */
    public function setValue2(?string $value): self;
}