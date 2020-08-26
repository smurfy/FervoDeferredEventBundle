<?php

namespace Fervo\DeferredEventBundle\Listener;

use Fervo\DeferredEventBundle\EventDispatcher\DummyEventDispatcher;
use Fervo\DeferredEventBundle\Service\MessageService;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Fervo\DeferredEventBundle\Event\DeferEvent;
use Fervo\DeferredEventBundle\Event\Queue\MessageQueueInterface;

class DeferEventListener
{
    /**
     * @var \Fervo\DeferredEventBundle\Event\Queue\MessageQueueInterface queue
     */
    protected $queue;

    /**
     * @var \Fervo\DeferredEventBundle\Service\MessageService messageService
     */
    protected $messageService;

    /**
     * @param MessageQueueInterface $queue
     * @param MessageService $messageService
     */
    public function __construct(MessageQueueInterface $queue, MessageService $messageService)
    {
        $this->queue = $queue;
        $this->messageService = $messageService;
    }

    /**
     * When we defer a DeferEvent. (The publisher decides that this should be a defer event)
     *
     * @param DeferEvent $event
     * @param string $name
     */
    public function onDeferEvent(DeferEvent $event, $name = null)
    {
        $message = $this->messageService->createMessage($event->getDeferredEvent());
        if (!is_null($name)) {
            $message->addHeader('event_name', $name);
        } else if (method_exists($event, 'getName')) {
            $message->addHeader('event_name', $event->getName());
        }
        $this->queue->addMessage($message, 0);
    }

    /**
     * When we defer a normal Event. (The listener decides that this should be a defer event)
     *
     * @param Event $event
     * @param string $name
     */
    public function onNonDeferEvent(Event $event, $name = null)
    {
        $message = $this->messageService->createMessage($event);
        if (!is_null($name)) {
            $message->addHeader('event_name', $name);
        } else if (method_exists($event, 'getName')) {
            $message->addHeader('event_name', $event->getName());
        }
        $this->queue->addMessage($message);
    }
}
