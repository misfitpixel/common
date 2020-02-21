<?php
/**
 * Created by PhpStorm.
 * User: misfitpixel
 * Date: 3/14/19
 * Time: 3:16 PM
 */

namespace MisfitPixel\Exception\Controller;


use MisfitPixel\Exception\Abstraction\BaseException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Twig\Environment;

/**
 * Class ExceptionController
 * @package MisfitPixel\Exception\Controller
 */
class ExceptionController
{
    /** @var bool  */
    protected $debug;

    /** @var Environment  */
    protected $twig;

    /**
     * ExceptionController constructor.
     * @param bool $debug
     * @param Environment $twig
     */
    public function __construct(bool $debug, Environment $twig)
    {
        $this->debug = $debug;
        $this->twig = $twig;
    }

    /**
     * @param ExceptionEvent $event
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function onKernelException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();

        if(!($exception instanceof BaseException)) {
            return;
        }

        if($exception->getTemplate() === null) {
            $event->setResponse(new JsonResponse([
                'error' => [
                    'status_code' => $exception->getStatusCode(),
                    'message' => $exception->getMessage(),
                    'data' => $exception->getData(),
                    'debug' => ($this->debug) ? [
                        'file' => $exception->getFile(),
                        'line' => $exception->getLine(),
                        'previous' => ($exception->getPrevious() !== null) ? [
                            'file' => $exception->getPrevious()->getFile(),
                            'line' => $exception->getPrevious()->getLine()
                        ] : null
                    ] : null
                ]
            ], $exception->getStatusCode(), $exception->getHeaders()));

        } else {
            if(!$this->twig->getLoader()->exists($exception->getTemplate())) {
                return;
            }

            $event->setResponse(new Response($this->twig->render($exception->getTemplate(), [
                'status_code' => $exception->getStatusCode(),
                'message' => $exception->getMessage(),
                'data' => $exception->getData()
            ]), $exception->getStatusCode(), $exception->getHeaders()));
        }
    }
}