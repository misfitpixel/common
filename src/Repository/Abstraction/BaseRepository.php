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
use MisfitPixel\Exception\BadRequestException;

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

    /** @var array */
    private $order;

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

    /**
     * @return array
     */
    public function getOrder(): array
    {
        return $this->order;
    }

    /**
     * @param array|null $order
     * @return $this
     */
    public function setOrder(?array $order): self
    {
        if($order === null) {
            $this->order = [];
        }

        foreach($order as $field => $direction) {
            if(!in_array(strtolower($direction), ['asc', 'desc'])) {
                throw new BadRequestException('Sort order direction must be one-of: \'asc\' or \'desc\' ');
            }
        }

        $this->order = $order;

        return $this;
    }

    /**
     * @return array
     */
    public function findAll()
    {
        return parent::findBy([], null, $this->getLimit(), $this->getOffset());
    }
}