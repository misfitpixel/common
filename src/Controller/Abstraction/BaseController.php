<?php
/**
 * Created by PhpStorm.
 * User: misfitpixel
 * Date: 1/21/20
 * Time: 3:08 PM
 */

namespace MisfitPixel\Controller\Abstraction;


use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class BaseController
 * @package Controller\Abstraction
 */
abstract class BaseController extends AbstractController
{
    /** @var ManagerRegistry  */
    private ManagerRegistry $manager;

    /**
     * @param ManagerRegistry $manager
     */
    public function __construct(ManagerRegistry $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @return ManagerRegistry
     */
    public function getManager(): ManagerRegistry
    {
        return $this->manager;
    }

    /**
     * @param Request $request
     * @return int
     */
    public function getPage(Request $request): int
    {
        return ($request->query->has('page')) ? $request->query->get('page') : 1;
    }

    /**
     * @param Request $request
     * @param int $limit
     * @return int
     */
    public function getLimit(Request $request, int $limit = 50): int
    {
        return ($request->query->has('limit')) ? $request->query->get('limit') : $limit;
    }

    /**
     * @param Request $request
     * @param array $order
     * @return array
     */
    public function getOrder(Request $request, array $order = []): array
    {
        if($request->query->has('order')) {
            $parts = explode(':', $request->query->get('order'));

            if(sizeof($parts) === 2) {
                $order = [$parts[0] => $parts[1]];
            }
        }

        return $order;
    }

    /**
     * @param Request $request
     * @return array
     */
    public function getContent(Request $request): array
    {
        return json_decode(($request->getContent()), true);
    }
}
