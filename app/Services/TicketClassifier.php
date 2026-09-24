<?php

namespace App\Services;

use App\Models\Ticket;
use OpenAI\Laravel\Facades\OpenAI;

class TicketClassifier
{
    public function classify(Ticket $ticket): array
    {
        if (!config('services.openai.classify_enabled', false)) {
            $categories = [
                'Technical',
                'Billing',
                'Account',
                'General',
            ];

            return [
                'category' => $categories[array_rand($categories)],
                'explanation' => 'Dummy classification because AI classification is disabled.',
                'confidence' => 0.80,
            ];
        }

        $response = OpenAI::chat()->create([
            'model' => config('services.openai.model', 'gpt-4o-mini'),
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'Classify the support ticket into one category: Technical, Billing, Account, or General. Return JSON only with these keys: category, explanation, confidence.',
                ],
                [
                    'role' => 'user',
                    'content' => "Subject: {$ticket->subject}\n\nBody: {$ticket->body}",
                ],
            ],
            'response_format' => [
                'type' => 'json_object',
            ],
        ]);

        $result = json_decode(
            $response->choices[0]->message->content,
            true
        );

        return [
            'category' => $result['category'] ?? 'General',
            'explanation' => $result['explanation'] ?? 'No explanation provided.',
            'confidence' => (float) ($result['confidence'] ?? 0.50),
        ];
    }
}