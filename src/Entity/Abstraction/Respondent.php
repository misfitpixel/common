<?php
/**
 * Created by PhpStorm.
 * User: misfitpixel
 * Date: 1/19/20
 * Time: 9:06 PM
 */

namespace Entity\Abstraction;


/**
 * Trait Respondent
 * @package Entity\Abstraction
 */
trait Respondent
{
    /**
     * @return int
     */
    public abstract function getId(): int;

    /**
     * @return array
     * @throws \ReflectionException
     */
    public function getResponse(): array
    {
        $response = [];

        $reflection = new \ReflectionClass($this);

        /**
         * if class is a proxy, re-reflect against the actual entity.
         */
        if($this instanceof \Doctrine\ORM\Proxy\Proxy) {
            $reflection = $reflection->getParentClass();
        }

        /** @var \ReflectionProperty $property */
        foreach($reflection->getProperties() as $property) {
            $methodName = sprintf('get%s', ucfirst($property->getName()));

            /**
             * skip autogeneration of metadata.
             */
            if($methodName === 'getMetaTree') {
                continue;
            }

            /**
             * grab all of the properties and create a response array.
             */
            if($reflection->getMethod($methodName)) {
                if(
                !is_object($this->$methodName())
                ) {
                    $value = $this->$methodName();

                } elseif(method_exists($this->$methodName(), 'getResponse')) {
                    /**
                     * recursively build the response of child entities.
                     */
                    $value = $this->$methodName()->getResponse();

                } elseif($this->$methodName() instanceof \DateTimeInterface) {
                    /**
                     * properly format timestamps.
                     */
                    $value = $this->$methodName()->getTimeStamp();

                } else {
                    $value = null;
                }

                $response[strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $property->getName()))] = $value;
            }
        }

        return $response;
    }

    /**
     * @return string
     */
    public function toString(): string
    {
        return sprintf('%s with ID: %d', self::class, $this->getId());
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->toString();
    }
}