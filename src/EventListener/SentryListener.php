<?php

namespace Baldinof\RoadRunnerBundle\EventListener;

use Baldinof\RoadRunnerBundle\Event\WorkerExceptionEvent;
use Baldinof\RoadRunnerBundle\Event\WorkerStopEvent;
use Sentry\ClientInterface;
use Sentry\State\HubInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class SentryListener implements EventSubscriberInterface
{
    private $hub;

    public function __construct(HubInterface $hub)
    {
        $this->hub = $hub;
    }

    public function onWorkerStop(WorkerStopEvent $event): void
    {
        $client = $this->hub->getClient();

        if ($client instanceof ClientInterface) {
            $client->flush()->wait();
        }
    }

    public function onWorkerException(WorkerExceptionEvent $event): void
    {
        $this->hub->captureException($event->getException());
    }

    public static function getSubscribedEvents()
    {
        return [
            WorkerStopEvent::class => 'onWorkerStop',
            WorkerExceptionEvent::class => 'onWorkerException',
        ];
    }
}
