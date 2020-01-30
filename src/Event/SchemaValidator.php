<?php
/**
 * Created by PhpStorm.
 * User: misfitpixel
 * Date: 3/8/19
 * Time: 9:58 AM
 */

namespace MisfitPixel\Event;


use MisfitPixel\Service\Validator\ValidatorService;;
use Symfony\Component\HttpKernel\Event\RequestEvent;

/**
 * Class SchemaValidator
 * @package App\Event
 */
class SchemaValidator
{
    /**
     * @var ValidatorService
     */
    private $validator;

    /**
     * SchemaValidator constructor.
     * @param ValidatorService $validator
     */
    public function __construct(ValidatorService $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @param RequestEvent $event
     * @throws \Exception
     */
    public function execute(RequestEvent $event)
    {
        $this->validator->validate($event->getRequest());
    }
}