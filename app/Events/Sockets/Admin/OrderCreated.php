<?php

namespace App\Events\Sockets\Admin;

use Illuminate\Broadcasting\InteractsWithBroadcasting;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Support\Facades\Log;

class OrderCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, InteractsWithBroadcasting;

    public float $total;
    public string $url;

    /**
     * Create a new event instance.
     *
     * @param float $total
     * @param string $url
     */
    public function __construct(float $total, string $url)
    {
        $this->total = $total;
        $this->url = $url;

        // Optionally remove or adjust logging in production
        Log::debug('OrderCreated event dispatched', ['total' => $total, 'url' => $url]);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('admin-channel'),
        ];
    }

    /**
     * Define the name of the event.
     *
     * @return string
     */
    public function broadcastAs(): string
    {
        return 'order.created.notify';
    }
}
