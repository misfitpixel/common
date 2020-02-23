<?php


namespace MisfitPixel\Event;


use Google\Cloud\PubSub\PubSubClient;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Class Webhook
 * @package MisfitPixel\Event
 */
class Webhook
{
    /**
     * @param GenericEvent $event
     */
    public function execute(GenericEvent $event)
    {
        /**
         * cancel event if insufficient arguments.
         */
        if(($data = json_encode($event->getSubject()) == null)) {
            return;
        }

        if(!$event->hasArgument('endpoint')) {
            return;
        }

        if(!$event->hasArgument('event_name')) {
            return;
        }

        $client = new PubSubClient([
            'projectId' => $_ENV['GOOGLE_PROJECT_ID'],
            'keyFilePath' => $_ENV['GOOGLE_APPLICATION_CREDENTIALS']
        ]);

        /**
         * publish message.
         */
        $client->topic('webhooks')->publish([
            'data' => json_encode($event->getSubject()),
            'attributes' => [
                'endpoint' => $event->getArgument('endpoint'),
                'event_name' => $event->getArgument('event_name')
            ]
        ]);
    }
}