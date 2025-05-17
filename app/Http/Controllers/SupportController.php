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

    public function show($id)
    {
        return Inertia::render('Support/TicketDetail', [
            'ticketId' => $id,
        ]);
    }

    public function create()
    {
        return Inertia::render('Support/TicketCreate');
    }

    public function reply($id)
    {
        return Inertia::render('Support/TicketReply', [
            'ticketId' => $id,
        ]);
    }

    public function faq()
    {
        return Inertia::render('Support/Faq');
    }
}
