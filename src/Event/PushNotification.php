<?php


namespace MisfitPixel\Event;


use Google\Cloud\PubSub\PubSubClient;
use MisfitPixel\Service\ValidatorService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Class PushNotification
 * @package MisfitPixel\Event
 */
class PushNotification
{
    /** @var ContainerInterface  */
    private $container;

    /** @var ValidatorService  */
    private $validatorService;

    /**
     * FeedEvent constructor.
     * @param ContainerInterface $container
     * @param ValidatorService $validatorService
     */
    public function __construct(ContainerInterface $container, ValidatorService $validatorService)
    {
        $this->container = $container;
        $this->validatorService = $validatorService;
    }

    /**
     * @param GenericEvent $event
     */
    public function execute(GenericEvent $event)
    {
        $data = $event->getSubject();
        $endpoint = $event->getArgument('endpoint');

        if(
            !is_array($data) ||
            $endpoint == null
        ) {
            return;
        }

        /**
         * cancel event if insufficient arguments.
         */
        try {
            $valid = $this->validatorService->validate(
                $data,
                sprintf('%s/vendor/misfitpixel/common/config/schema_validator/push_notification.yml',
                    $this->container->get('kernel')->getProjectDir()
                )
            );

            if(!$valid) {
                throw new \Exception();
            }

        } catch(\Exception $e) {
            return;
        }

        $client = new PubSubClient([
            'projectId' => $_ENV['GOOGLE_PROJECT_ID'],
            'keyFilePath' => $_ENV['GOOGLE_APPLICATION_CREDENTIALS']
        ]);

        /**
         * publish message.
         */
        $client->topic('push-notifications')->publish([
            'data' => json_encode($data),
            'attributes' => [
                'endpoint' => $endpoint
            ]
        ]);
    }
}