<?php


namespace MisfitPixel\Event\Middleware;


use MisfitPixel\Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpFoundation\Response;
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

    /**
     * OauthValidator constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
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
        $data = $this->getTokenDetails($event->getRequest()->headers->get('Authorization'));
        $isScoped = true;

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
     * @param string $token
     * @return array
     */
    private function getTokenDetails(string $token): array
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            sprintf('Authorization: %s', $token)
        ]);

        curl_setopt($ch, CURLOPT_URL, 'http://accounts.mtgbracket.com/oauth/validate');

        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FAILONERROR, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);

        $result = (curl_exec($ch));
        $errorCode = curl_errno($ch);
        $info = curl_getinfo($ch);

        curl_close($ch);

        switch($errorCode) {
            case CURLE_OK:
                break;

            case CURLE_OPERATION_TIMEOUTED:
                throw new Exception\TimeoutException('Could not connect to accounts service');

                break;

            default:
                throw new Exception\UnknownErrorException('Error encountered during api request');

                break;
        }

        switch($info['http_code']){
            case Response::HTTP_OK:
            case Response::HTTP_ACCEPTED:
            case Response::HTTP_NO_CONTENT:
                break;

            default:
                throw new Exception\UnknownErrorException();
        }

        return ($result != null) ? json_decode($result, true) : null;
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