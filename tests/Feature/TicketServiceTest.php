<?php

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\Owner;
use App\Models\Ticket;
use App\Services\OwnerAssignmentService;
use App\Services\TicketService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeService(): TicketService
{
    return new TicketService(new OwnerAssignmentService);
}

function ticketData(array $overrides = []): array
{
    return array_merge([
        'title' => 'Test ticket',
        'description' => 'Description',
        'priority' => TicketPriority::Medium,
    ], $overrides);
}

// --- create ---

it('auto-assigns owner when owner_id is absent', function () {
    $owner = Owner::factory()->create();

    $ticket = makeService()->create(ticketData());

    expect($ticket->owner_id)->toBe($owner->id);
});

it('sets opened_at when creating with open status', function () {
    Owner::factory()->create();

    $ticket = makeService()->create(ticketData(['status' => TicketStatus::Open]));

    expect($ticket->opened_at)->not->toBeNull();
});

it('sets opened_at when creating with in_progress status', function () {
    Owner::factory()->create();

    $ticket = makeService()->create(ticketData(['status' => TicketStatus::InProgress]));

    expect($ticket->opened_at)->not->toBeNull();
});

it('does not set opened_at when creating with closed status', function () {
    Owner::factory()->create();

    $ticket = makeService()->create(ticketData(['status' => TicketStatus::Closed]));

    expect($ticket->opened_at)->toBeNull();
});

it('respects explicit owner_id on create', function () {
    $ownerA = Owner::factory()->create();
    $ownerB = Owner::factory()->create();

    $ticket = makeService()->create(ticketData(['owner_id' => $ownerB->id]));

    expect($ticket->owner_id)->toBe($ownerB->id);
});

// --- auto-assign ---

it('auto-assigns owner with fewest open tickets', function () {
    $busy = Owner::factory()->create();
    $free = Owner::factory()->create();

    Ticket::factory(3)->create(['owner_id' => $busy->id, 'status' => TicketStatus::Open]);
    Ticket::factory(1)->create(['owner_id' => $free->id, 'status' => TicketStatus::Open]);

    $ticket = Ticket::factory()->create(['owner_id' => $busy->id]);
    makeService()->autoAssignOwner($ticket);

    expect($ticket->fresh()->owner_id)->toBe($free->id);
});

it('uses lowest id as tiebreaker for auto-assign', function () {
    $first = Owner::factory()->create();
    $second = Owner::factory()->create();

    $ticket = Ticket::factory()->create(['owner_id' => $second->id]);
    makeService()->autoAssignOwner($ticket);

    expect($ticket->fresh()->owner_id)->toBe($first->id);
});

it('only counts open and in_progress tickets for auto-assign load', function () {
    $ownerA = Owner::factory()->create();
    $ownerB = Owner::factory()->create();

    // ownerA has many resolved/closed — should not count
    Ticket::factory(5)->create(['owner_id' => $ownerA->id, 'status' => TicketStatus::Resolved]);
    Ticket::factory(1)->create(['owner_id' => $ownerB->id, 'status' => TicketStatus::Open]);

    $ticket = Ticket::factory()->create(['owner_id' => $ownerB->id]);
    makeService()->autoAssignOwner($ticket);

    // ownerA has 0 open tickets, ownerB has 1 — ownerA wins
    expect($ticket->fresh()->owner_id)->toBe($ownerA->id);
});

it('throws DomainException when no owners exist', function () {
    expect(fn () => makeService()->create(ticketData()))
        ->toThrow(DomainException::class, 'No owners available for assignment.');
});

// --- closed_at on create ---

it('sets closed_at when creating with resolved status', function () {
    Owner::factory()->create();

    $ticket = makeService()->create(ticketData(['status' => TicketStatus::Resolved]));

    expect($ticket->closed_at)->not->toBeNull();
});

it('sets closed_at when creating with closed status', function () {
    Owner::factory()->create();

    $ticket = makeService()->create(ticketData(['status' => TicketStatus::Closed]));

    expect($ticket->closed_at)->not->toBeNull();
});

it('does not set closed_at when creating with open status', function () {
    Owner::factory()->create();

    $ticket = makeService()->create(ticketData(['status' => TicketStatus::Open]));

    expect($ticket->closed_at)->toBeNull();
});

// --- update ---

it('sets opened_at on update when transitioning to open state', function () {
    $owner = Owner::factory()->create();
    $ticket = Ticket::factory()->create([
        'owner_id' => $owner->id,
        'status' => TicketStatus::Closed,
        'opened_at' => null,
    ]);

    makeService()->update($ticket, ['status' => TicketStatus::Open]);

    expect($ticket->fresh()->opened_at)->not->toBeNull();
});

it('does not overwrite opened_at on update if already set', function () {
    $owner = Owner::factory()->create();
    $originalDate = now()->subDays(5);
    $ticket = Ticket::factory()->create([
        'owner_id' => $owner->id,
        'status' => TicketStatus::Open,
        'opened_at' => $originalDate,
    ]);

    makeService()->update($ticket, ['status' => TicketStatus::InProgress]);

    expect($ticket->fresh()->opened_at->toDateString())->toBe($originalDate->toDateString());
});

it('sets closed_at on update when transitioning to closed state', function () {
    $owner = Owner::factory()->create();
    $ticket = Ticket::factory()->create([
        'owner_id' => $owner->id,
        'status' => TicketStatus::Open,
        'closed_at' => null,
    ]);

    makeService()->update($ticket, ['status' => TicketStatus::Resolved]);

    expect($ticket->fresh()->closed_at)->not->toBeNull();
});

it('does not overwrite closed_at on update if already set', function () {
    $owner = Owner::factory()->create();
    $originalDate = now()->subDays(3);
    $ticket = Ticket::factory()->create([
        'owner_id' => $owner->id,
        'status' => TicketStatus::Resolved,
        'closed_at' => $originalDate,
    ]);

    makeService()->update($ticket, ['status' => TicketStatus::Closed]);

    expect($ticket->fresh()->closed_at->toDateString())->toBe($originalDate->toDateString());
});

// --- manual assign ---

it('manually assigns a specific owner', function () {
    $ownerA = Owner::factory()->create();
    $ownerB = Owner::factory()->create();
    $ticket = Ticket::factory()->create(['owner_id' => $ownerA->id]);

    makeService()->assignOwner($ticket, $ownerB->id);

    expect($ticket->fresh()->owner_id)->toBe($ownerB->id);
});
