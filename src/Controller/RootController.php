<?php
/**
 * Created by PhpStorm.
 * User: misfitpixel
 * Date: 1/21/20
 * Time: 3:05 PM
 */

namespace MisfitPixel\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class RootController
 * @package MisfitPixel\Controller
 */
class RootController extends AbstractController
{
    /**
     * @return JsonResponse
     */
    public function index(): Response
    {
        $version = shell_exec('git describe --tags `git rev-list --tags --max-count=1`');
        $documentationUrl = null;

        try {
            $documentationUrl = $this->getParameter('misfitpixel.common.documentation_url');

        } catch(InvalidArgumentException $e) {
            // do nothing.
        }

        return new JsonResponse([
            'version' => ($version != null) ? $version : 'Development',
            'documentation_url' => $documentationUrl,
            'message' => 'Welcome to mtgbracket! Interested in working with our API? Take a look at the developer resources and get started!'
        ]);
    }
}
