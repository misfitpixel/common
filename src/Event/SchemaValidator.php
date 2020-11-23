<?php
/**
 * Created by PhpStorm.
 * User: misfitpixel
 * Date: 3/8/19
 * Time: 9:58 AM
 */

namespace MisfitPixel\Event;


use MisfitPixel\Service\ValidatorService;;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;

/**
 * Class SchemaValidator
 * @package App\Event
 */
class SchemaValidator
{
    /** @var ContainerInterface  */
    private $container;

    /**
     * @var ValidatorService
     */
    private $validator;

    /**
     * SchemaValidator constructor.
     * @param ContainerInterface $container
     * @param ValidatorService $validator
     */
    public function __construct(ContainerInterface $container, ValidatorService $validator)
    {
        $this->container = $container;
        $this->validator = $validator;
    }

    /**
     * @param RequestEvent $event
     * @throws \Exception
     */
    public function execute(RequestEvent $event)
    {
        $this->validator->validate(
            json_decode($event->getRequest()->getContent(), true),
            sprintf('%s/config/schema_validator/%s.yml',
                $this->container->get('kernel')->getProjectDir(),
                $event->getRequest()->get('_route')
            )
        );
    }
}