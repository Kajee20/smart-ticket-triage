<?php

namespace App\Http\Controllers;

use App\Jobs\ClassifyTicket;
use App\Models\Ticket;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    // POST /tickets
    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        $ticket = Ticket::create([
            'subject' => $validated['subject'],
            'body' => $validated['body'],
            'status' => 'open',
        ]);

        return response()->json([
            'message' => 'Ticket created successfully',
            'ticket' => $ticket,
        ], 201);
    }

    // GET /tickets
    public function index(Request $request)
    {
        $query = Ticket::query();

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                    ->orWhere('body', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $tickets = $query
            ->latest()
            ->paginate(10);

        return response()->json($tickets);
    }

    // GET /tickets/{id}
    public function show(string $id)
    {
        $ticket = Ticket::findOrFail($id);

        return response()->json([
            'ticket' => $ticket,
        ]);
    }

    // PATCH /tickets/{id}
    public function update(Request $request, string $id)
    {
        $ticket = Ticket::findOrFail($id);

        $validated = $request->validate([
            'status' => 'sometimes|in:open,in_progress,resolved,closed',
            'category' => 'sometimes|nullable|string|max:100',
            'internal_note' => 'sometimes|nullable|string',
        ]);

        // If category is manually changed, mark it as manual
        if (array_key_exists('category', $validated)) {
            $validated['category_source'] =
                !empty($validated['category'])
                    ? 'manual'
                    : null;
        }

        $ticket->update($validated);

        return response()->json([
            'message' => 'Ticket updated successfully',
            'ticket' => $ticket->fresh(),
        ]);
    }

    // POST /tickets/{id}/classify
    public function classify(string $id)
    {
        $ticket = Ticket::findOrFail($id);

        ClassifyTicket::dispatch($ticket->id);

        return response()->json([
            'message' => 'Classification queued successfully',
            'ticket_id' => $ticket->id,
        ], 202);
    }

    // GET /stats
    public function stats()
    {
        $statusCounts = Ticket::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $categoryCounts = Ticket::whereNotNull('category')
            ->selectRaw('category, COUNT(*) as total')
            ->groupBy('category')
            ->pluck('total', 'category');

        return response()->json([
            'total' => Ticket::count(),
            'by_status' => $statusCounts,
            'by_category' => $categoryCounts,
        ]);
    }
}