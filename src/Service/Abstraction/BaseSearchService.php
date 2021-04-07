<?php


namespace MisfitPixel\Service\Abstraction;


use Doctrine\ORM\EntityManagerInterface;
use MisfitPixel\Exception\BadRequestException;

/**
 * Class BaseSearchService
 * @package MisfitPixel\Service\Abstraction
 */
abstract class BaseSearchService
{
    /** @var EntityManagerInterface  */
    private $manager;

    /**
     * ExpansionSearchService constructor.
     * @param EntityManagerInterface $manager
     */
    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @param string $query
     * @param int $offset
     * @param int $limit
     * @param array $order
     * @return array
     */
    public abstract function search(string $query, int $offset, int $limit, array $order = []): array;

    /**
     * @param array $criteria
     * @return string
     */
    protected abstract function evaluateCriteriaAsSql(array $criteria): string;

    /**
     * @param array $order
     * @return string
     */
    protected function evaluateOrder(array $order): string
    {
        $sql = 'ORDER BY ';

        if($order == null) {
            return '';
        }

        foreach($order as $column => $direction) {
            if(
                $column == null ||
                $direction == null
            ) {
                continue;
            }

            /**
             * confirm that provided column is acceptable for this resource.
             */
            if(!in_array($column, $this->getSortColumns())) {
                throw new BadRequestException(sprintf("'%s' is not a valid field for ordering", $column));
            }

            $sql .= sprintf("q.%s %s", $column, $direction);
        }

        return $sql;
    }

    /**
     * @return array
     */
    abstract protected function getSortColumns(): array;

    /**
     * @return EntityManagerInterface
     */
    protected function getManager(): EntityManagerInterface
    {
        return $this->manager;
    }
}