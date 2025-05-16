<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class SupportController extends Controller
{
    public function index()
    {
        return Inertia::render('Support/Dashboard');
    }

    public function tickets()
    {
        return Inertia::render('Support/Tickets');
    }

    public function showTicket($id)
    {
        return Inertia::render('Support/TicketDetail', [
            'ticketId' => $id,
        ]);
    }
}
