<?php
/**
 * Created by PhpStorm.
 * User: misfitpixel
 * Date: 1/21/20
 * Time: 3:08 PM
 */

namespace MisfitPixel\Controller\Abstraction;


use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class BaseController
 * @package Controller\Abstraction
 */
abstract class BaseController
{
    /** @var ContainerInterface  */
    protected $container;

    /**
     * BaseController constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
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
}