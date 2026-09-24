<?php

namespace App\Jobs;

use App\Models\Ticket;
use App\Services\TicketClassifier;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ClassifyTicket implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $ticketId
    ) {
    }

    public function handle(TicketClassifier $classifier): void
    {
        $ticket = Ticket::find($this->ticketId);

        if (!$ticket) {
            return;
        }

        $result = $classifier->classify($ticket);

        // Manual category must not be overwritten by AI
        if ($ticket->category_source !== 'manual') {
            $ticket->category = $result['category'];
            $ticket->category_source = 'ai';
        }

        // Always update AI explanation and confidence
        $ticket->explanation = $result['explanation'];
        $ticket->confidence = $result['confidence'];

        $ticket->save();
    }
}