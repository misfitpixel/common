<?php
/**
 * Created by PhpStorm.
 * User: misfitpixel
 * Date: 1/21/20
 * Time: 3:09 PM
 */

namespace MisfitPixel\Response\Abstraction;


use Symfony\Component\HttpFoundation\Request;

/**
 * Class JsonResponse
 * @package MisfitPixel\Response\Abstraction
 */
class JsonResponse extends \Symfony\Component\HttpFoundation\JsonResponse
{
    /** @var mixed */
    private $entity;

    /**
     * JsonResponse constructor.
     * @param null $data
     * @param int $status
     * @param Request $request
     */
    public function __construct($data = null, int $status = 200, Request $request)
    {
        /**
         * add paging.
         */
        if(isset($data['items'])) {
            $data['paging'] = $this->addPaging($data, $request);
        }

        parent::__construct($data, $status, [], false);
    }

    /**
     * @return mixed
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * @param $entity
     * @return JsonResponse
     */
    public function setEntity($entity): self
    {
        $this->entity = $entity;

        return $this;
    }

    /**
     * @param array $data
     * @param Request $request
     * @return array
     */
    public function addPaging(array $data, Request $request): array
    {
        $query = [];

        /**
         * set query.
         */
        $page = ($request->query->has('page')) ? $request->query->get('page') : 1;
        $limit = ($request->query->has('limit')) ? $request->query->get('limit') : 10;

        $request->query->remove('page');
        $request->query->remove('limit');

        foreach($request->query as $key => $value) {
            $query[$key] = $value;
        }

        /**
         * build URLs.
         */
        $endpoint = explode('?', $request->getUri())[0];

        $prev = sprintf('%s?%s', $endpoint, http_build_query(array_merge($query, [
            'page' => $page - 1,
            'limit' => $limit
        ])));

        $next = sprintf('%s?%s', $endpoint, http_build_query(array_merge($query, [
            'page' => $page + 1,
            'limit' => $limit
        ])));

        return [
            'prev' => ($page > 1) ? $prev : null,
            'next' => (!$request->query->has('limit') !== null && sizeof($data['items']) >= $limit) ? $next : null
        ];
    }
}