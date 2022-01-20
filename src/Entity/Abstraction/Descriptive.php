<?php
/**
 * Created by PhpStorm.
 * User: misfitpixel
 * Date: 1/19/20
 * Time: 8:55 PM
 */

namespace MisfitPixel\Entity\Abstraction;


use Symfony\Component\HttpKernel\Kernel;

/**
 * Trait Descriptive
 * @package MisfitPixel\Entity\Abstraction
 */
trait Descriptive
{
    /** @var array */
    private array $metaTree;

    /**
     * @return int|null
     */
    public abstract function getId(): ?int;

    /**
     * @param string $field
     * @param bool $return
     * @return Meta|null|string
     */
    public function getMeta(string $field, bool $return = false)
    {
        /** @var Kernel $kernel */
        global $kernel;

        $metaEntity = sprintf('%sMeta', ucfirst(self::class));

        /** @var Meta $meta */
        $meta = $kernel->getContainer()->get('doctrine')->getManager()->getRepository($metaEntity)
            ->findOneBy([
                strtolower(str_replace('App\Entity\\', '', self::class)) => $this->getId(),
                'field' => $field
            ])
        ;

        return (!$return) ? $meta : (($meta != null) ? $meta->getValue1() : null);
    }

    /**
     * @param string $field
     * @param string $value1
     * @param string|null $value2
     * @param bool $override
     * @return $this
     */
    public function setMeta(string $field, string $value1, string $value2 = null, bool $override = true): self
    {
        /** @var Kernel $kernel */
        global $kernel;

        $className = $this->getMetaClassName();
        $methodName = sprintf('set%s', ucfirst($this->getRootEntityName()));

        /** @var Meta $meta */
        $meta = new $className;

        if($override) {
            $meta = $kernel->getContainer()->get('doctrine')->getManager()->getRepository($className)
                ->findOneBy([
                    $this->getRootEntityName() => $this->getId(),
                    'field' => $field
                ])
            ;

            if($meta === null) {
                $meta = new $className;
            }
        }

        $meta->$methodName($this)
            ->setField($field)
            ->setValue1($value1)
            ->setValue2($value2)
            ->save()
        ;

        return $this;
    }

    /**
     * @param string $field
     * @return bool
     */
    public function deleteMeta(string $field): bool
    {
        /** @var Kernel $kernel */
        global $kernel;

        $className = $this->getMetaClassName();

        $meta = $kernel->getContainer()->get('doctrine')->getManager()->getRepository($className)
            ->findOneBy([
                $this->getRootEntityName() => $this->getId(),
                'field' => $field
            ])
        ;

        return $meta->delete();
    }

    /**
     * @param bool $force
     * @return array
     */
    public function getMetaTree(bool $force = false): array
    {
        /** @var Kernel $kernel */
        global $kernel;

        if($this->metaTree !== null && !$force) {
            return $this->metaTree;
        }

        $metaEntity = sprintf('%sMeta', ucfirst(self::class));
        $metaTree = [];

        /**
         * build the entire metadata tree for this resource.
         */
        $meta = $kernel->getContainer()->get('doctrine')->getManager()->getRepository($metaEntity)
            ->findBy([
                strtolower(str_replace('App\Entity\\', '', self::class)) => $this->getId(),
            ])
        ;

        /** @var Meta $item */
        foreach($meta as $item) {
            $keys = explode('.', $item->getField());

            $chain = "";

            for($i=0; $i<sizeof($keys); $i++) {
                $chain .= sprintf("['%s']", $keys[$i]);
            }

            eval(sprintf('$metaTree%s = $item;',
                $chain
            ));
        }

        $this->metaTree = $metaTree;

        return $this->metaTree;
    }

    /**
     * @return string
     */
    public function getRootEntityName(): string
    {
        return strtolower(str_replace('App\Entity\\', '', self::class));
    }

    /**
     * @return string
     */
    public function getMetaClassName(): string
    {
        return sprintf('%sMeta', ucfirst(self::class));
    }
}
