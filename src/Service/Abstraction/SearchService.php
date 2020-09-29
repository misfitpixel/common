<?php


namespace MisfitPixel\Service\Abstraction;


use Doctrine\ORM\EntityManagerInterface;

/**
 * Class SearchService
 * @package MisfitPixel\Service\Abstraction
 */
abstract class SearchService
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
     * @return array
     */
    public abstract function search(): array;

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

            $sql .= sprintf("%s %s", $column, $direction);
        }

        return $sql;
    }
}