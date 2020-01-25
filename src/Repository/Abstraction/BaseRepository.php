<?php
/**
 * Created by PhpStorm.
 * User: misfitpixel
 * Date: 1/25/20
 * Time: 9:20 AM
 */

namespace MisfitPixel\Repository\Abstraction;


use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * Class BaseRepository
 * @package MisfitPixel\Repository\Abstraction
 */
abstract class BaseRepository extends ServiceEntityRepository
{

    /** @var int */
    private $offset;

    /** @var int */
    private $limit;

    /**
     * BaseRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        $this->offset = 0;

        parent::__construct($registry, $this->getEntityClassName());
    }

    /**
     * @return string
     */
    public abstract function getEntityClassName(): string;

    /**
     * @return int
     */
    public function getOffset(): int
    {
        return $this->offset;
    }

    /**
     * @param int $offset
     * @return $this
     */
    public function setOffset(int $offset): self
    {
        $this->offset = $offset;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getLimit(): ?int
    {
        return $this->limit;
    }

    /**
     * @param int|null $limit
     * @return $this
     */
    public function setLimit(?int $limit): self
    {
        $this->limit = $limit;

        return $this;
    }
}