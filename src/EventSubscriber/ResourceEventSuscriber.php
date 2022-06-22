<?php

namespace App\EventSubscriber;

use App\Event\ResourceEvent;
use App\Handler\PushBuildHandler;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ResourceEventSuscriber implements EventSubscriberInterface
{
    /**
     * @var PushBuildHandler
     */
    private $pushBuildHandler;

    public function __construct(PushBuildHandler $pushBuildHandler) {
        $this->pushBuildHandler = $pushBuildHandler;
    }

    public static function getSubscribedEvents()
    {
        return [
            ResourceEvent::name => 'pushBuild',
        ];
    }

    public function pushBuild(ResourceEvent $resourceEvent)
    {
        $resource = $resourceEvent->getResource();

        $className = get_class($resource);

        $id = null;

        if (method_exists($resource, 'getId')) {
            $id = $resource->getId();
        }

        $this->pushBuildHandler->addBuildNotification(sprintf('%s %s trigger udpate', $className, $id));
    }
}