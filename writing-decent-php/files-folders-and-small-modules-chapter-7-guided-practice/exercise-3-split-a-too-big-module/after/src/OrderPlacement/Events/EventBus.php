<?php
declare(strict_types=1);

namespace App\OrderPlacement\Events;

/**
 * The tiniest possible event bus. In a real app this would be Symfony's
 * EventDispatcher, Laravel's Event facade, RabbitMQ, etc. The point is
 * that the publisher (Placement) speaks only to this interface and never
 * to a concrete listener.
 */
interface EventBus
{
    public function publish(object $event): void;
}
