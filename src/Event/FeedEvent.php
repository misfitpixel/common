<?php


namespace MisfitPixel\Event;


use Google\Cloud\PubSub\PubSubClient;
use MisfitPixel\Service\ValidatorService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Class FeedEvent
 * @package MisfitPixel\Event
 */
class FeedEvent
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
     * @throws \Exception
     */
    public function execute(GenericEvent $event)
    {
        $data = $event->getSubject();

        if(!is_array($data)) {
            return;
        }

        $data['date_created'] = (new \DateTime('now', new \DateTimeZone('UTC')))->getTimestamp();

        /**
         * cancel event if insufficient arguments.
         */
        try {
            $valid = $this->validatorService->validate(
                $data,
                sprintf('%s/vendor/misfitpixel/common/config/schema_validator/feed_event.yml',
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
        $client->topic('feed-events')->publish([
            'data' => json_encode($data)
        ]);
    }
}