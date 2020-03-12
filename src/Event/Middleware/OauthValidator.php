<?php


namespace MisfitPixel\Event\Middleware;


use MisfitPixel\Exception;
use MisfitPixel\Service\OauthService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Routing\Route;
use Symfony\Component\Yaml\Exception\ParseException;

/**
 * Class OauthValidator
 * @package MisfitPixel\Event\Middleware
 */
class OauthValidator
{
    /** @var ContainerInterface  */
    private $container;

    /** @var OauthService  */
    private $oauthService;

    /**
     * OauthValidator constructor.
     * @param ContainerInterface $container
     * @param OauthService $oauthService
     */
    public function __construct(ContainerInterface $container, OauthService $oauthService)
    {
        $this->container = $container;
        $this->oauthService = $oauthService;
    }

    /**
     * @param RequestEvent $event
     * @throws \Exception
     */
    public function execute(RequestEvent $event)
    {
        /**
         * load the route config.
         */
        if(($routeIdentifier = $event->getRequest()->get('_route')) == null ) {
            return;
        }

        $route = $this->getRouteConfig($routeIdentifier);

        /**
         * skip if no route found.
         */
        if($route == null) {
            return;
        }

        /**
         * skip check for routes with empty or missing oauth_scopes.
         */
        if($route->getOption('oauth_scopes') == null) {
            return;
        }

        /**
         * get the token details from the accounts service.
         */
        $data = $this->oauthService->getTokenDetails(trim(str_replace('Bearer', '', $event->getRequest()->headers->get('Authorization'))));
        $isScoped = true;

        /**
         * attach the token data to the request.
         */
        $event->getRequest()->attributes->set('oauth_token', $data);

        /**
         * if root client, skip this process.
         */
        if(in_array('ROOT', $this->getScopes($data['scopes']))) {
            $isScoped = true;

        } else {
            /**
             * verify all the required scopes for the route are attached to the token details.
             */
            foreach ($route->getOption('oauth_scopes') as $scope) {
                if (!in_array($scope, $this->getScopes($data['scopes']))) {
                    $isScoped = false;

                    break;
                }
            }
        }

        if(!$isScoped) {
            throw new Exception\ForbiddenException();
        }
    }

    /**
     * @param array $permissions
     * @return array
     */
    private function getScopes(array $permissions): array
    {
        $scopes = [];

        foreach($permissions as $permission) {
            if($permission['oauth_permission']['resource'] === 'root') {
                $scopes[] = 'ROOT';

                break;
            }

            if($permission['has_read']) {
                $scopes[] = strtoupper(sprintf('%s_READ', $permission['oauth_permission']['resource']));
            }

            if($permission['has_write']) {
                $scopes[] = strtoupper(sprintf('%s_WRITE', $permission['oauth_permission']['resource']));
            }
        }

        return $scopes;
    }

    /**
     * @param string $routeIdentifier
     * @return Route|null
     */
    private function getRouteConfig(string $routeIdentifier): ?Route
    {
        try {
            $route = (new YamlFileLoader(
                new FileLocator(
                    sprintf('%s/config', $this->container->getParameter('kernel.project_dir'))
                )
            ))->load('routes.yaml')->get($routeIdentifier);

        } catch(ParseException $e) {
            $route = null;
        }

        return $route;
    }
}