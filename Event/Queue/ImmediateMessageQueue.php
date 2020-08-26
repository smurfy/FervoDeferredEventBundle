<?php

namespace Fervo\DeferredEventBundle\Event\Queue;

use Fervo\DeferredEventBundle\Model\QueueMessage;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Serializer\SerializerInterface;

class ImmediateMessageQueue implements MessageQueueInterface
{
    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface eventDispatcher
     *
     */
    protected $eventDispatcher;

    /**
     * @var \Symfony\Component\Serializer\SerializerInterface eventSerializer
     *
     */
    protected $eventSerializer;

    /**
     * @var  serializerFormat
     *
     */
    protected $serializerFormat;

    /**
     * @param EventDispatcher $eventDispatcher
     * @param SerializerInterface $eventSerializer
     * @param $serializerFormat
     */
    public function __construct(EventDispatcherInterface $eventDispatcher, SerializerInterface $eventSerializer, $serializerFormat)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->serializerFormat = $serializerFormat;
        $this->eventSerializer = $eventSerializer;
    }

    public function addMessage(QueueMessage $message, $delay = 0)
    {
        /*
         * We do want to serialize and deserialize. This is a great way to find bugs on your local machine where you
         * don't have the message queue set up.
         */
        $event = $this->eventSerializer->deserialize($message->getData(), null, $this->serializerFormat);
        $eventName =  $message->getHeader('event_name');
        // Sf < 3.0 work around
        if (is_null($eventName) && method_exists($event, 'getName')) {
            $eventName = $event->getName();
        }
        $this->eventDispatcher->dispatch($eventName, $event);
    }
}
