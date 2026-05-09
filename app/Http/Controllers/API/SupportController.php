<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SupportController extends Controller
{
    public function index(Request $request)
    {
        // Mock response for now
        return response()->json([
            'data' => [
                [
                    'id' => 1,
                    'subject' => 'Delayed Delivery',
                    'status' => 'open',
                    'created_at' => now()->subHours(2)->toDateTimeString()
                ]
            ]
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'category' => 'sometimes|string'
        ]);

        return response()->json([
            'message' => 'Ticket created successfully',
            'data' => [
                'id' => rand(100, 999),
                'subject' => $request->subject,
                'status' => 'open',
                'created_at' => now()->toDateTimeString()
            ]
        ], 201);
    }

    public function show($id)
    {
        return response()->json([
            'data' => [
                'id' => $id,
                'subject' => 'Delayed Delivery',
                'status' => 'open',
                'messages' => [
                    [
                        'id' => 1,
                        'user_name' => 'John Doe',
                        'message' => 'My order is late.',
                        'created_at' => now()->subHours(2)->toDateTimeString()
                    ]
                ]
            ]
        ]);
    }

    public function reply(Request $request, $id)
    {
        $request->validate([
            'message' => 'required|string'
        ]);

        return response()->json([
            'message' => 'Reply added successfully',
            'data' => [
                'id' => rand(1000, 9999),
                'message' => $request->message,
                'created_at' => now()->toDateTimeString()
            ]
        ], 201);
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
