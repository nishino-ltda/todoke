<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SupportTicket;
use App\Models\SupportTicketReply;

class SupportController extends Controller
{
    public function index(Request $request)
    {
        $tickets = $request->user()->supportTickets()->latest()->get();

        return response()->json([
            'data' => $tickets
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'category' => 'required|string',
            'priority' => 'sometimes|string|in:low,medium,high',
        ]);

        $ticket = $request->user()->supportTickets()->create([
            'subject' => $validated['subject'],
            'category' => $validated['category'],
            'priority' => $validated['priority'] ?? 'medium',
            'status' => 'open',
        ]);

        $ticket->replies()->create([
            'user_id' => $request->user()->id,
            'message' => $validated['message'],
        ]);

        return response()->json([
            'message' => 'Ticket created successfully',
            'data' => $ticket
        ], 201);
    }

    public function show($id)
    {
        $ticket = SupportTicket::with(['replies.user'])->findOrFail($id);

        // Security check: only the owner or an admin can see the ticket
        if ($ticket->user_id !== auth()->id() && auth()->user()->type !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'data' => [
                'id' => $ticket->id,
                'subject' => $ticket->subject,
                'status' => $ticket->status,
                'category' => $ticket->category,
                'priority' => $ticket->priority,
                'created_at' => $ticket->created_at,
                'messages' => $ticket->replies->map(function ($reply) {
                    return [
                        'id' => $reply->id,
                        'user_name' => $reply->user->name,
                        'body' => $reply->message, // Using 'body' to match frontend expected field
                        'created_at' => $reply->created_at,
                    ];
                })
            ]
        ]);
    }

    public function reply(Request $request, $id)
    {
        $ticket = SupportTicket::findOrFail($id);

        if ($ticket->user_id !== auth()->id() && auth()->user()->type !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'message' => 'required|string'
        ]);

        $reply = $ticket->replies()->create([
            'user_id' => auth()->id(),
            'message' => $request->message,
        ]);

        // If it was closed, maybe reopen it? Or just keep status.
        // Usually, a user reply reopens or keeps it open.

        return response()->json([
            'message' => 'Reply added successfully',
            'data' => [
                'id' => $reply->id,
                'body' => $reply->message,
                'created_at' => $reply->created_at
            ]
        ], 201);
    }

    public function close($id)
    {
        $ticket = SupportTicket::findOrFail($id);

        if ($ticket->user_id !== auth()->id() && auth()->user()->type !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $ticket->update(['status' => 'closed']);

        return response()->json([
            'message' => 'Ticket closed successfully'
        ]);
    }

    public function faq()
    {
        return response()->json([
            'data' => [
                [
                    'id' => 1,
                    'question' => 'How to track my order?',
                    'answer' => 'Go to the dashboard and click on the delivery.'
                ],
                [
                    'id' => 2,
                    'question' => 'What are the delivery fees?',
                    'answer' => 'Fees are determined by the community and distance.'
                ]
            ]
        ]);
    }
}
