<?php
/**
 * Created by PhpStorm.
 * User: misfitpixel
 * Date: 1/21/20
 * Time: 3:05 PM
 */

namespace MisfitPixel\Controller;


use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class RootController
 * @package Controller
 */
class RootController
{
    /**
     * @return JsonResponse
     */
    public function root()
    {
        $version = shell_exec('git describe --tags `git rev-list --tags --max-count=1`');

        return new JsonResponse([
            'version' => ($version != null) ? $version : 'Development',
            'documentation_url' => 'https://www.mtgbracket.com/developers',
            'message' => 'Welcome to mtgbracket! Interested in working with our API? Take a look at the developer resources and get started!'
        ]);
    }
}