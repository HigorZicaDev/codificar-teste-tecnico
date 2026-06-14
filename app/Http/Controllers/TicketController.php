<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTicketRequest;
use App\Http\Requests\UpdateTicketRequest;
use App\Models\Owner;
use App\Models\Ticket;
use App\Services\TicketService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TicketController extends Controller
{
    public function __construct(private readonly TicketService $ticketService) {}

    public function index(Request $request): Response
    {
        $tickets = Ticket::query()
            ->with('owner')
            ->when($request->string('status')->isNotEmpty(), fn ($q) => $q->where('status', $request->status))
            ->when($request->string('priority')->isNotEmpty(), fn ($q) => $q->where('priority', $request->priority))
            ->when($request->filled('owner_id'), fn ($q) => $q->where('owner_id', $request->owner_id))
            ->when($request->string('search')->isNotEmpty(), fn ($q) => $q->where(
                fn ($q) => $q->where('title', 'like', "%{$request->search}%")
                    ->orWhere('description', 'like', "%{$request->search}%")
            ))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Tickets/Index', [
            'tickets' => $tickets,
            'filters' => $request->only(['status', 'priority', 'owner_id', 'search']),
            'owners' => Owner::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Tickets/Create', [
            'owners' => Owner::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(StoreTicketRequest $request): RedirectResponse
    {
        $ticket = $this->ticketService->create($request->validated());

        return redirect()->route('tickets.show', $ticket)->with('success', 'Chamado criado com sucesso.');
    }

    public function show(Ticket $ticket): Response
    {
        return Inertia::render('Tickets/Show', [
            'ticket' => $ticket->load('owner'),
        ]);
    }

    public function edit(Ticket $ticket): Response
    {
        return Inertia::render('Tickets/Edit', [
            'ticket' => $ticket->load('owner'),
            'owners' => Owner::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(UpdateTicketRequest $request, Ticket $ticket): RedirectResponse
    {
        $this->ticketService->update($ticket, $request->validated());

        return redirect()->route('tickets.show', $ticket)->with('success', 'Chamado atualizado com sucesso.');
    }

    public function assignOwner(Request $request, Ticket $ticket): RedirectResponse
    {
        $request->validate(['owner_id' => ['required', 'exists:owners,id']]);

        $this->ticketService->assignOwner($ticket, (int) $request->owner_id);

        return redirect()->route('tickets.show', $ticket)->with('success', 'Responsável atribuído com sucesso.');
    }

    public function autoAssignOwner(Ticket $ticket): RedirectResponse
    {
        try {
            $this->ticketService->autoAssignOwner($ticket);
        } catch (DomainException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->route('tickets.show', $ticket)->with('success', 'Responsável atribuído automaticamente.');
    }
}
