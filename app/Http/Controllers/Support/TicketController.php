<?php

namespace App\Http\Controllers\Support;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TicketController extends Controller
{
    public function index()
    {
        return Inertia::render('Support/Tickets');
    }

    public function show($ticketId)
    {
        return Inertia::render('Support/TicketDetail', [
            'ticketId' => $ticketId,
        ]);
    }
}
